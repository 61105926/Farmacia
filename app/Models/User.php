<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'document_number',
        'branch_id',
        'status',
        'last_login_at',
        'last_login_ip',
        'failed_login_attempts',
        'blocked_at',
        'theme',
        'language',
        'notification_settings',
        'preferences',
        // Compatibilidad con campos antiguos
        'telefono',
        'cedula',
        'direccion',
        'estado',
        'ultimo_acceso',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'blocked_at' => 'datetime',
            'failed_login_attempts' => 'integer',
            'notification_settings' => 'array',
            'preferences' => 'array',
            // Compatibilidad con campos antiguos
            'ultimo_acceso' => 'datetime',
        ];
    }

    /**
     * Relación con clientes asignados como vendedor
     */
    public function assignedClients(): HasMany
    {
        return $this->hasMany(Client::class, 'salesperson_id');
    }

    /**
     * Relación con clientes asignados como cobrador
     */
    public function clientsAsCollector(): HasMany
    {
        return $this->hasMany(Client::class, 'collector_id');
    }

    /**
     * Relación con notificaciones
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Obtener notificaciones no leídas
     */
    public function unreadNotifications(): HasMany
    {
        return $this->notifications()->where('read', false);
    }

    /**
     * Verificar si el usuario tiene un permiso específico
     * Compatible con Spatie Permission (usa hasPermissionTo)
     */
    public function hasPermission(string $permission): bool
    {
        return $this->hasPermissionTo($permission);
    }

    /**
     * Verificar si el usuario está activo
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Verificar si el usuario está bloqueado
     */
    public function isBlocked(): bool
    {
        return $this->status === 'blocked';
    }

    /**
     * Scope para filtrar solo usuarios activos
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope para búsqueda por nombre o email
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }
}
