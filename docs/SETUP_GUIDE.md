# GUÍA DE CONFIGURACIÓN - SISTEMA FARMACIA PANDO

## Archivos Creados ✅

### Migraciones Creadas (9)
1. `2024_01_01_000001_create_roles_table.php`
2. `2024_01_01_000002_create_permissions_table.php`
3. `2024_01_01_000003_update_users_table.php`
4. `2024_01_01_000004_create_role_user_table.php`
5. `2024_01_01_000005_create_permission_role_table.php`
6. `2024_01_01_000006_create_sessions_table.php`
7. `2024_01_02_000001_create_clients_table.php`
8. `2024_01_02_000002_create_client_contacts_table.php`
9. `2024_01_02_000003_create_client_addresses_table.php`

### Modelos Eloquent Creados (6)
1. `app/Models/Role.php` - Con métodos para gestión de roles
2. `app/Models/Permission.php` - Con scopes por módulo
3. `app/Models/Client.php` - Con todas las relaciones
4. `app/Models/ClientContact.php` - Contactos de clientes
5. `app/Models/ClientAddress.php` - Direcciones de entrega
6. User.php (actualizar el existente con relaciones)

### Seeders Creados (3)
1. `database/seeders/RoleSeeder.php` - 8 roles predefinidos
2. `database/seeders/PermissionSeeder.php` - 80+ permisos
3. `database/seeders/DatabaseSeeder.php` - Usuarios demo

---

## PASOS DE INSTALACIÓN

### 1. Crear Migraciones Restantes

Antes de ejecutar las migraciones, necesitas crear las tablas faltantes:

```bash
# MÓDULO PRODUCTOS
php artisan make:migration create_product_categories_table
php artisan make:migration create_price_lists_table
php artisan make:migration create_payment_terms_table
php artisan make:migration create_products_table
php artisan make:migration create_product_images_table
php artisan make:migration create_product_prices_table

# MÓDULO INVENTARIO (Crear ANTES de Ventas)
php artisan make:migration create_branches_table
php artisan make:migration create_warehouses_table
php artisan make:migration create_inventory_table
php artisan make:migration create_inventory_lots_table
php artisan make:migration create_inventory_movements_table
php artisan make:migration create_stock_transfers_table
php artisan make:migration create_stock_transfer_items_table

# MÓDULO PREVENTAS Y VENTAS
php artisan make:migration create_presales_table
php artisan make:migration create_presale_items_table
php artisan make:migration create_sales_table
php artisan make:migration create_sale_items_table
php artisan make:migration create_credit_notes_table
php artisan make:migration create_credit_note_items_table

# MÓDULO CUENTAS POR COBRAR
php artisan make:migration create_receivables_table
php artisan make:migration create_payments_table
php artisan make:migration create_payment_applications_table

# MÓDULO AUDITORÍA
php artisan make:migration create_activity_log_table
php artisan make:migration create_system_settings_table
```

### 2. Ejecutar Migraciones

```bash
# Ejecutar todas las migraciones
php artisan migrate

# O si quieres empezar desde cero (¡CUIDADO! Borra todo)
php artisan migrate:fresh
```

### 3. Ejecutar Seeders

```bash
# Ejecutar todos los seeders
php artisan db:seed

# O ejecutar seeders específicos
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=PermissionSeeder
php artisan db:seed --class=DatabaseSeeder
```

### 4. Asignar Permisos a Roles

Puedes crear un seeder adicional para asignar permisos a roles:

```php
// database/seeders/RolePermissionSeeder.php
<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin - todos los permisos
        $superAdmin = Role::where('slug', 'super-admin')->first();
        $superAdmin->permissions()->attach(Permission::all());

        // Administrador - casi todos excepto configuración crítica
        $admin = Role::where('slug', 'administrador')->first();
        $adminPermissions = Permission::whereNotIn('slug', [
            'users.delete',
            'roles.delete',
            'settings.update',
        ])->get();
        $admin->permissions()->attach($adminPermissions);

        // Vendedor Preventas
        $preventista = Role::where('slug', 'vendedor-preventas')->first();
        $preventaPermissions = Permission::whereIn('module', [
            'clients',
            'products',
            'presales',
        ])->whereIn('slug', [
            'clients.index',
            'clients.show',
            'clients.create',
            'products.index',
            'products.show',
            'presales.index',
            'presales.create',
            'presales.update',
        ])->get();
        $preventista->permissions()->attach($preventaPermissions);

        // Vendedor Ventas
        $vendedor = Role::where('slug', 'vendedor-ventas')->first();
        $ventaPermissions = Permission::whereIn('module', [
            'clients',
            'products',
            'sales',
            'invoices',
        ])->get();
        $vendedor->permissions()->attach($ventaPermissions);

        // Cobrador
        $cobrador = Role::where('slug', 'cobrador')->first();
        $cobranzaPermissions = Permission::whereIn('module', [
            'clients',
            'receivables',
            'payments',
        ])->whereIn('slug', [
            'clients.index',
            'clients.show',
            'receivables.index',
            'receivables.show',
            'payments.index',
            'payments.create',
            'payments.register',
        ])->get();
        $cobrador->permissions()->attach($cobranzaPermissions);

        // Bodeguero
        $bodeguero = Role::where('slug', 'bodeguero')->first();
        $inventoryPermissions = Permission::where('module', 'inventory')->get();
        $bodeguero->permissions()->attach($inventoryPermissions);

        // Contabilidad
        $contabilidad = Role::where('slug', 'contabilidad')->first();
        $contaPermissions = Permission::whereIn('module', [
            'sales',
            'invoices',
            'receivables',
            'payments',
            'reports',
        ])->whereNotIn('slug', [
            'sales.delete',
            'invoices.delete',
            'payments.delete',
        ])->get();
        $contabilidad->permissions()->attach($contaPermissions);

        // Auditor - Solo lectura
        $auditor = Role::where('slug', 'auditor')->first();
        $auditPermissions = Permission::whereIn('slug', [
            'users.index',
            'users.show',
            'clients.index',
            'clients.show',
            'products.index',
            'products.show',
            'sales.index',
            'sales.show',
            'reports.index',
            'reports.view-all',
        ])->get();
        $auditor->permissions()->attach($auditPermissions);

        $this->command->info('✅ Permisos asignados a roles correctamente');
    }
}
```

Luego ejecutar:

```bash
php artisan make:seeder RolePermissionSeeder
# Copiar el código de arriba
php artisan db:seed --class=RolePermissionSeeder
```

---

## CREDENCIALES DE ACCESO

Después de ejecutar los seeders:

**Super Administrador:**
- Email: admin@farmacia.com
- Password: admin123

**Vendedor (Demo):**
- Email: vendedor@farmacia.com
- Password: vendedor123

---

## VERIFICACIÓN

### Verificar Migraciones

```bash
php artisan migrate:status
```

### Verificar Datos

```bash
php artisan tinker
```

Luego en tinker:

```php
// Verificar roles
\App\Models\Role::all();

// Verificar permisos
\App\Models\Permission::count();

// Verificar usuario admin
$admin = \App\Models\User::where('email', 'admin@farmacia.com')->first();
$admin->roles; // Debe mostrar "super-admin"

// Verificar permisos del admin
$admin->getAllPermissions();

// Verificar si admin tiene permiso
$admin->hasPermission('users.create'); // Debe retornar true
```

---

## PRÓXIMOS PASOS

1. ✅ Crear las migraciones restantes (productos, inventario, ventas, etc.)
2. ✅ Crear los modelos Eloquent para cada tabla
3. ✅ Crear factories para testing
4. ✅ Crear los controladores Inertia
5. ✅ Crear las vistas Vue.js
6. ✅ Implementar middleware de permisos
7. ✅ Crear composables para usar permisos en Vue

---

## ESTRUCTURA RECOMENDADA DE CONTROLLERS (Inertia)

```php
// app/Http/Controllers/ClientController.php
<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $clients = Client::query()
            ->with(['salesperson', 'collector'])
            ->when($request->search, function ($query, $search) {
                $query->search($search);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Clients/Index', [
            'clients' => $clients,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Clients/Create', [
            'salespeople' => \App\Models\User::withRole('vendedor-ventas')->get(),
            'collectors' => \App\Models\User::withRole('cobrador')->get(),
            'priceLists' => \App\Models\PriceList::active()->get(),
        ]);
    }

    // ... etc
}
```

---

## NOTAS IMPORTANTES

- ⚠️ El modelo `User.php` existente usa Spatie Permission. Puedes continuar usándolo o migrar al sistema personalizado creado.
- ⚠️ Asegúrate de tener `branches` y `payment_terms` antes de ejecutar la migración de `clients`.
- ⚠️ Los seeders crean usuarios con contraseñas demo. CAMBIARLAS en producción.
- ⚠️ Configurar `.env` correctamente para la base de datos.

---

**Documento creado por:** Gabriel
**Fecha:** 20 de Octubre, 2025
