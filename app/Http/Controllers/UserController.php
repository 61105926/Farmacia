<?php

namespace App\Http\Controllers;

use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Helpers\InertiaHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request): Response
    {
        $users = User::query()
            ->with(['roles:id,name'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('document_number', 'like', "%{$search}%");
                });
            })
            ->when($request->status, function ($query, $status) {
                if ($status === 'active') {
                    $query->whereNull('blocked_at');
                } elseif ($status === 'blocked') {
                    $query->whereNotNull('blocked_at');
                }
            })
            ->when($request->role, function ($query, $role) {
                $query->whereHas('roles', function($q) use ($role) {
                    $q->where('name', $role);
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Users/Index', [
            'users' => InertiaHelper::sanitizeData($users),
            'filters' => InertiaHelper::sanitizeFilters($request->only(['search', 'status', 'role'])),
            'roles' => InertiaHelper::sanitizeRoles(Role::all(['id', 'name'])),
        ]);
    }

    /**
     * Show the form for creating a new user
     */
    public function create(): Response
    {
        $this->ensureBasicRoles();
        
        return Inertia::render('Users/Create', [
            'roles' => Role::all(['id', 'name'])->map(function($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name ?? 'Sin nombre'
                ];
            }),
        ]);
    }

    /**
     * Asegurar que existan roles básicos
     */
    private function ensureBasicRoles(): void
    {
        $basicRoles = [
            'super-admin' => 'Super Administrador',
            'admin' => 'Administrador',
            'vendedor-ventas' => 'Vendedor de Ventas',
            'vendedor-preventas' => 'Vendedor de Preventas',
            'cobrador' => 'Cobrador',
            'almacen' => 'Almacén',
            'contador' => 'Contador',
        ];

        foreach ($basicRoles as $name => $description) {
            Role::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['name' => $name, 'guard_name' => 'web']
            );
        }
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:50',
            'document_number' => 'nullable|string|max:50',
            'role_ids' => 'required|array',
            'role_ids.*' => 'exists:roles,id',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        // Asignar roles
        $user->roles()->attach($request->role_ids);

        return redirect()->route('users.index')
            ->with('success', 'Usuario creado exitosamente.');
    }

    /**
     * Display the specified user
     */
    public function show(User $user): Response
    {
        $user->load(['roles', 'sessions' => function($q) {
            $q->latest()->limit(10);
        }]);

        return Inertia::render('Users/Show', [
            'user' => $user,
            'permissions' => $user->getAllPermissions(),
        ]);
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user): Response
    {
        $this->ensureBasicRoles();
        
        $user->load('roles');

        return Inertia::render('Users/Edit', [
            'user' => $user,
            'roles' => Role::all(['id', 'name'])->map(function($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name ?? 'Sin nombre'
                ];
            }),
        ]);
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:50',
            'document_number' => 'nullable|string|max:50',
            'role_ids' => 'required|array',
            'role_ids.*' => 'exists:roles,id',
        ]);

        // Si se envía contraseña, actualizarla
        if ($request->filled('password')) {
            $request->validate([
                'password' => 'required|string|min:8|confirmed',
            ]);
            $validated['password'] = Hash::make($request->password);
        }

        $user->update($validated);

        // Sincronizar roles
        $user->roles()->sync($request->role_ids);

        return redirect()->route('users.show', $user)
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user): RedirectResponse
    {
        // No permitir eliminar el propio usuario
        if ($user->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        // No permitir eliminar si es el único super-admin
        if ($user->hasRole('super-admin')) {
            $superAdminCount = User::whereHas('roles', fn($q) => $q->where('name', 'super-admin'))->count();
            if ($superAdminCount <= 1) {
                return back()->with('error', 'No se puede eliminar el único Super Administrador del sistema.');
            }
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Usuario eliminado exitosamente.');
    }

    /**
     * Reset user password
     */
    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->update([
            'password' => Hash::make($request->password),
            'password_changed_at' => now(),
        ]);

        return back()->with('success', 'Contraseña actualizada exitosamente.');
    }

    /**
     * Block/Unblock user
     */
    public function toggleBlock(User $user): RedirectResponse
    {
        try {
            $isBlocked = $user->blocked_at !== null;
            
            if ($isBlocked) {
                $user->update([
                    'blocked_at' => null,
                    'failed_login_attempts' => 0,
                ]);
                $message = 'Usuario desbloqueado exitosamente.';
            } else {
                $user->update([
                    'blocked_at' => now(),
                ]);
                $message = 'Usuario bloqueado exitosamente.';
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            \Log::error('UserController toggleBlock error: ' . $e->getMessage());
            
            return back()->with('error', 'Error al cambiar el estado del usuario: ' . $e->getMessage());
        }
    }

    /**
     * Disable user (soft delete)
     */
    public function disable(User $user): RedirectResponse
    {
        // No permitir deshabilitar el propio usuario
        if ($user->id === auth()->id()) {
            return back()->with('error', 'No puedes deshabilitar tu propia cuenta.');
        }

        // No permitir deshabilitar si es el único super-admin
        if ($user->hasRole('super-admin')) {
            $superAdminCount = User::whereHas('roles', fn($q) => $q->where('name', 'super-admin'))->count();
            if ($superAdminCount <= 1) {
                return back()->with('error', 'No se puede deshabilitar el único Super Administrador del sistema.');
            }
        }

        $user->update([
            'blocked_at' => now(),
            'status' => 'blocked',
        ]);

        return back()->with('success', 'Usuario deshabilitado exitosamente.');
    }

    /**
     * Activate user
     */
    public function activate(User $user): RedirectResponse
    {
        try {
            $user->update([
                'blocked_at' => null,
                'status' => 'active',
                'failed_login_attempts' => 0,
            ]);

            return back()->with('success', 'Usuario activado exitosamente.');

        } catch (\Exception $e) {
            \Log::error('UserController activate error: ' . $e->getMessage());
            
            return back()->with('error', 'Error al activar el usuario: ' . $e->getMessage());
        }
    }

    /**
     * Assign clients to user
     */
    public function assignClients(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'client_ids' => 'required|array',
            'client_ids.*' => 'exists:clients,id',
        ]);

        // Actualizar clientes asignados
        \DB::table('clients')
            ->whereIn('id', $request->client_ids)
            ->update(['salesperson_id' => $user->id]);

        return back()->with('success', 'Clientes asignados exitosamente.');
    }

    /**
     * Get user statistics
     */
    public function statistics(User $user): Response
    {
        try {
            $stats = [
                'total_clients' => \DB::table('clients')->where('salesperson_id', $user->id)->count(),
                'active_clients' => \DB::table('clients')->where('salesperson_id', $user->id)->where('status', 'active')->count(),
                'total_sales' => \DB::table('sales')->where('salesperson_id', $user->id)->count(),
                'total_presales' => \DB::table('presales')->where('salesperson_id', $user->id)->count(),
                'total_revenue' => \DB::table('sales')->where('salesperson_id', $user->id)->sum('total'),
                'monthly_sales' => \DB::table('sales')
                    ->where('salesperson_id', $user->id)
                    ->whereMonth('created_at', now()->month)
                    ->sum('total'),
            ];

            return Inertia::render('Users/Statistics', [
                'user' => $user,
                'stats' => $stats,
            ]);

        } catch (\Exception $e) {
            \Log::error('UserController statistics error: ' . $e->getMessage());
            
            return redirect()->route('users.index')
                ->with('error', 'Error al cargar estadísticas: ' . $e->getMessage());
        }
    }

    /**
     * Export users to CSV
     */
    public function export(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="usuarios.csv"',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            
            // Headers
            fputcsv($handle, [
                'ID',
                'Nombre',
                'Email',
                'Teléfono',
                'Documento',
                'Roles',
                'Estado',
                'Último Acceso',
                'Creado'
            ]);

            // Data
            User::with('roles')->chunk(100, function ($users) use ($handle) {
                foreach ($users as $user) {
                    fputcsv($handle, [
                        $user->id,
                        $user->name,
                        $user->email,
                        $user->phone,
                        $user->document_number,
                        $user->roles->pluck('name')->join(', '),
                        $user->blocked_at ? 'Bloqueado' : 'Activo',
                        $user->last_login_at ? $user->last_login_at->format('Y-m-d H:i:s') : 'Nunca',
                        $user->created_at->format('Y-m-d H:i:s'),
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }
}