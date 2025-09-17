<?php

namespace App\Http\Controllers;

use App\Models\Pharmacy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PharmacyController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Pharmacy::query();

        // Filtros
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('nombre_comercial', 'like', "%{$search}%")
                  ->orWhere('razon_social', 'like', "%{$search}%")
                  ->orWhere('codigo_cliente', 'like', "%{$search}%")
                  ->orWhere('numero_documento', 'like', "%{$search}%");
            });
        }

        if ($request->filled('tipo_cliente')) {
            $query->where('tipo_cliente', $request->get('tipo_cliente'));
        }

        if ($request->filled('activo')) {
            $query->where('activo', $request->boolean('activo'));
        }

        if ($request->filled('zona_reparto')) {
            $query->where('zona_reparto', $request->get('zona_reparto'));
        }

        // Ordenamiento
        $sortField = $request->get('sort', 'nombre_comercial');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        $pharmacies = $query->paginate(15)->withQueryString();

        // Estadísticas
        $stats = [
            'total' => Pharmacy::count(),
            'activos' => Pharmacy::where('activo', true)->count(),
            'inactivos' => Pharmacy::where('activo', false)->count(),
            'con_credito' => Pharmacy::where('limite_credito', '>', 0)->count(),
        ];

        return Inertia::render('Pharmacies/Index', [
            'pharmacies' => $pharmacies,
            'filters' => $request->only(['search', 'tipo_cliente', 'activo', 'zona_reparto']),
            'stats' => $stats,
            'sort' => ['field' => $sortField, 'direction' => $sortDirection]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Pharmacies/Create', [
            'codigoCliente' => Pharmacy::generateCodigoCliente()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo_cliente' => 'nullable|string|unique:pharmacies,codigo_cliente|max:20',
            'nombre_comercial' => 'required|string|max:255',
            'razon_social' => 'required|string|max:255',
            'tipo_documento' => 'required|in:RUC,CI,PASAPORTE',
            'numero_documento' => 'required|string|unique:pharmacies,numero_documento|max:20',
            'direccion' => 'required|string|max:500',
            'ciudad' => 'required|string|max:100',
            'provincia' => 'required|string|max:100',
            'codigo_postal' => 'nullable|string|max:10',
            'telefono_principal' => 'required|string|max:20',
            'telefono_secundario' => 'nullable|string|max:20',
            'email_principal' => 'required|email|max:255',
            'email_secundario' => 'nullable|email|max:255',
            'contacto_nombre' => 'required|string|max:255',
            'contacto_cargo' => 'nullable|string|max:100',
            'contacto_telefono' => 'nullable|string|max:20',
            'limite_credito' => 'nullable|numeric|min:0|max:999999.99',
            'dias_credito' => 'nullable|integer|min:0|max:365',
            'tipo_cliente' => 'required|in:regular,mayorista,preferencial',
            'descuento_general' => 'nullable|numeric|min:0|max:100',
            'horario_atencion' => 'nullable|string|max:255',
            'zona_reparto' => 'nullable|string|max:100',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        // Auto-generar código de cliente si no se proporciona
        if (empty($validated['codigo_cliente'])) {
            $validated['codigo_cliente'] = Pharmacy::generateCodigoCliente();
        }

        $validated['created_by'] = Auth::id();

        $pharmacy = Pharmacy::create($validated);

        return redirect()->route('pharmacies.index')
            ->with('message', 'Farmacia creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Pharmacy $pharmacy)
    {
        return Inertia::render('Pharmacies/Show', [
            'pharmacy' => $pharmacy
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pharmacy $pharmacy)
    {
        return Inertia::render('Pharmacies/Edit', [
            'pharmacy' => $pharmacy
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Pharmacy $pharmacy)
    {
        $validated = $request->validate([
            'codigo_cliente' => 'required|string|max:20|unique:pharmacies,codigo_cliente,' . $pharmacy->id,
            'nombre_comercial' => 'required|string|max:255',
            'razon_social' => 'required|string|max:255',
            'tipo_documento' => 'required|in:RUC,CI,PASAPORTE',
            'numero_documento' => 'required|string|max:20|unique:pharmacies,numero_documento,' . $pharmacy->id,
            'direccion' => 'required|string|max:500',
            'ciudad' => 'required|string|max:100',
            'provincia' => 'required|string|max:100',
            'codigo_postal' => 'nullable|string|max:10',
            'telefono_principal' => 'required|string|max:20',
            'telefono_secundario' => 'nullable|string|max:20',
            'email_principal' => 'required|email|max:255',
            'email_secundario' => 'nullable|email|max:255',
            'contacto_nombre' => 'required|string|max:255',
            'contacto_cargo' => 'nullable|string|max:100',
            'contacto_telefono' => 'nullable|string|max:20',
            'limite_credito' => 'nullable|numeric|min:0|max:999999.99',
            'dias_credito' => 'nullable|integer|min:0|max:365',
            'tipo_cliente' => 'required|in:regular,mayorista,preferencial',
            'descuento_general' => 'nullable|numeric|min:0|max:100',
            'activo' => 'boolean',
            'horario_atencion' => 'nullable|string|max:255',
            'zona_reparto' => 'nullable|string|max:100',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        $validated['updated_by'] = Auth::id();

        $pharmacy->update($validated);

        return redirect()->route('pharmacies.index')
            ->with('message', 'Farmacia actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pharmacy $pharmacy)
    {
        // Soft delete logic - disable instead of delete
        $pharmacy->update(['activo' => false, 'updated_by' => Auth::id()]);

        return redirect()->route('pharmacies.index')
            ->with('message', 'Farmacia desactivada exitosamente.');
    }

    /**
     * Toggle pharmacy status
     */
    public function toggleStatus(Pharmacy $pharmacy)
    {
        $pharmacy->update([
            'activo' => !$pharmacy->activo,
            'updated_by' => Auth::id()
        ]);

        $status = $pharmacy->activo ? 'activada' : 'desactivada';

        return back()->with('message', "Farmacia {$status} exitosamente.");
    }

    /**
     * Export pharmacies data
     */
    public function export(Request $request)
    {
        $query = Pharmacy::query();

        // Aplicar los mismos filtros que en index
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('nombre_comercial', 'like', "%{$search}%")
                  ->orWhere('razon_social', 'like', "%{$search}%")
                  ->orWhere('codigo_cliente', 'like', "%{$search}%")
                  ->orWhere('numero_documento', 'like', "%{$search}%");
            });
        }

        if ($request->filled('tipo_cliente')) {
            $query->where('tipo_cliente', $request->get('tipo_cliente'));
        }

        if ($request->filled('activo')) {
            $query->where('activo', $request->boolean('activo'));
        }

        $pharmacies = $query->orderBy('nombre_comercial', 'asc')->get();

        $filename = 'farmacias_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->stream(function () use ($pharmacies) {
            $handle = fopen('php://output', 'w');

            // Encabezados CSV
            fputcsv($handle, [
                'Código Cliente',
                'Nombre Comercial',
                'Razón Social',
                'Tipo Documento',
                'Número Documento',
                'Dirección',
                'Ciudad',
                'Provincia',
                'Teléfono',
                'Email',
                'Contacto',
                'Tipo Cliente',
                'Límite Crédito',
                'Días Crédito',
                'Estado',
                'Zona Reparto'
            ]);

            // Datos
            foreach ($pharmacies as $pharmacy) {
                fputcsv($handle, [
                    $pharmacy->codigo_cliente,
                    $pharmacy->nombre_comercial,
                    $pharmacy->razon_social,
                    $pharmacy->tipo_documento,
                    $pharmacy->numero_documento,
                    $pharmacy->direccion,
                    $pharmacy->ciudad,
                    $pharmacy->provincia,
                    $pharmacy->telefono_principal,
                    $pharmacy->email_principal,
                    $pharmacy->contacto_nombre,
                    ucfirst($pharmacy->tipo_cliente),
                    number_format($pharmacy->limite_credito, 2),
                    $pharmacy->dias_credito,
                    $pharmacy->activo ? 'Activo' : 'Inactivo',
                    $pharmacy->zona_reparto
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
