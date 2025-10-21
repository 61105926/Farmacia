# GUÍA DE IMPLEMENTACIÓN - CONTROLADORES Y VISTAS

## ✅ Archivos Creados

### Controladores Inertia (2)
1. `app/Http/Controllers/ClientController.php` - CRUD completo de clientes
2. `app/Http/Controllers/UserController.php` - CRUD completo de usuarios

### Vistas Vue.js (8)
1. `resources/js/Pages/Clients/Index.vue` - Listado de clientes con filtros
2. `resources/js/Pages/Clients/Create.vue` - Formulario de creación
3. `resources/js/Pages/Clients/Edit.vue` - Formulario de edición
4. `resources/js/Pages/Clients/Show.vue` - Vista detalle con tabs
5. `resources/js/Pages/Users/Index.vue` - Listado de usuarios con filtros
6. `resources/js/Pages/Users/Create.vue` - Formulario de creación
7. `resources/js/Pages/Users/Edit.vue` - Formulario de edición
8. `resources/js/Pages/Users/Show.vue` - Vista detalle con tabs

### Composables (2)
1. `resources/js/Composables/usePermissions.js` - Gestión de permisos
2. `resources/js/Composables/useDebouncedRef.js` - Debounce para búsquedas

### Componentes (1)
1. `resources/js/Components/Pagination.vue` - Paginación reutilizable

### Rutas (1)
1. `routes/web_clients_users.php` - Rutas de clientes y usuarios

---

## 📋 Pasos para Integrar

### 1. Incluir Rutas en `routes/web.php`

Agregar al final del archivo `routes/web.php`:

```php
require __DIR__.'/web_clients_users.php';
```

### 2. Actualizar Middleware de Permisos (Opcional)

Crear middleware para verificar permisos:

```bash
php artisan make:middleware CheckPermission
```

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        if (!$request->user()->hasPermission($permission)) {
            abort(403, 'No tienes permiso para realizar esta acción.');
        }

        return $next($request);
    }
}
```

Registrar en `app/Http/Kernel.php`:

```php
protected $middlewareAliases = [
    // ...
    'permission' => \App\Http\Middleware\CheckPermission::class,
];
```

### 3. Compartir Datos Globales con Inertia

En `app/Http/Middleware/HandleInertiaRequests.php`:

```php
public function share(Request $request): array
{
    return [
        ...parent::share($request),
        'auth' => [
            'user' => $request->user() ? [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'avatar_url' => $request->user()->avatar_url,
            ] : null,
            'permissions' => $request->user()
                ? $request->user()->getAllPermissions()->pluck('slug')->toArray()
                : [],
            'roles' => $request->user()
                ? $request->user()->roles->pluck('slug')->toArray()
                : [],
        ],
        'flash' => [
            'success' => fn () => $request->session()->get('success'),
            'error' => fn () => $request->session()->get('error'),
        ],
    ];
}
```

### 4. Crear Componentes UI Faltantes

Ya tienes algunos componentes UI, pero asegúrate de tener:

```bash
# Si no existen, créalos
resources/js/Components/ui/Card.vue
resources/js/Components/ui/CardHeader.vue
resources/js/Components/ui/CardTitle.vue
resources/js/Components/ui/CardContent.vue
```

### 5. Agregar Rutas al Sidebar

En tu componente `Sidebar.vue`, agregar:

```vue
<SidebarItem
  href="/clientes"
  :active="$page.url.startsWith('/clientes')"
  :collapsed="isCollapsed"
  icon="UserCheck"
  label="Clientes"
  :permissions="['clients.view']"
/>

<SidebarItem
  href="/usuarios"
  :active="$page.url.startsWith('/usuarios')"
  :collapsed="isCollapsed"
  icon="Users"
  label="Usuarios"
  :permissions="['users.view']"
/>
```

---

## 🔧 Estado de Vistas

### Módulo Clientes ✅ COMPLETO
- ✅ `Clients/Index.vue` - Listado con filtros (búsqueda, tipo, categoría, estado)
- ✅ `Clients/Create.vue` - Crear con información general y comercial
- ✅ `Clients/Edit.vue` - Editar datos del cliente
- ✅ `Clients/Show.vue` - Ver detalle con tabs (Info, Contactos, Ventas, CxC, Actividad)

### Módulo Usuarios ✅ COMPLETO
- ✅ `Users/Index.vue` - Listado con filtros (búsqueda, rol, estado)
- ✅ `Users/Create.vue` - Crear con roles y permisos
- ✅ `Users/Edit.vue` - Editar con opciones de seguridad
- ✅ `Users/Show.vue` - Ver detalle con tabs (Info, Seguridad, Clientes, Actividad)

---

## 📝 Ejemplo de Uso en Componentes

### Verificar Permisos en Vue

```vue
<script setup>
import { usePermissions } from '@/Composables/usePermissions'

const { can, hasRole, isSuperAdmin } = usePermissions()
</script>

<template>
  <button v-if="can('clients.create')" @click="createClient">
    Crear Cliente
  </button>

  <div v-if="hasRole('vendedor-preventas')">
    Contenido solo para preventistas
  </div>

  <div v-if="isSuperAdmin">
    Panel de Super Admin
  </div>
</template>
```

### Usar Búsqueda con Debounce

```vue
<script setup>
import { ref } from 'vue'
import { useDebouncedRef } from '@/Composables/useDebouncedRef'

const search = ref('')

const debouncedSearch = useDebouncedRef(() => {
  // Ejecutar búsqueda
  console.log('Buscando:', search.value)
}, 500)
</script>

<template>
  <input
    v-model="search"
    type="text"
    @input="debouncedSearch"
    placeholder="Buscar..."
  />
</template>
```

---

## 🎨 Colores de la Marca en Componentes

Los componentes ya usan las clases:
- `bg-primary-700` - Verde principal
- `bg-accent-500` - Amarillo acento
- `text-primary-900` - Texto verde oscuro
- `hover:bg-primary-800` - Hover verde

Estas clases están definidas en `resources/css/app.css`:

```css
--color-primary-600: var(--color-brand-green-600); /* #006838 */
--color-accent-500: var(--color-brand-yellow-500); /* #FFD700 */
```

---

## 🚀 Próximos Pasos Recomendados

### Módulos Completados ✅
- ✅ Clientes (controlador + 4 vistas completas)
- ✅ Usuarios (controlador + 4 vistas completas)

### Próximos Módulos por Implementar

1. **Crear migraciones restantes**
   - Productos (products, categories, brands)
   - Inventario (warehouses, branches, stock_movements)
   - Preventas (pre_sales, pre_sale_items)
   - Ventas (sales, sale_items, payments)
   - Cuentas por Cobrar (receivables, payments, payment_plans)

2. **Crear módulo de Productos**
   - ProductController con CRUD completo
   - Products/Index.vue, Create.vue, Edit.vue, Show.vue
   - Gestión de categorías y marcas

3. **Crear módulo de Preventas**
   - PreSaleController con workflow de preventa
   - PreSales/Index.vue, Create.vue, Edit.vue, Show.vue
   - Flujo: creación → revisión → conversión a venta

4. **Crear módulo de Ventas**
   - SaleController con facturación
   - Sales/Index.vue, Create.vue, Show.vue
   - Integración con inventario y cuentas por cobrar

5. **Crear módulo de Inventario**
   - InventoryController con movimientos de stock
   - Inventory/Index.vue, Movements.vue, Transfer.vue
   - Control multi-bodega

6. **Crear módulo de Cuentas por Cobrar**
   - ReceivableController con gestión de cobros
   - Receivables/Index.vue, Show.vue, Payment.vue
   - Dashboard de cartera vencida

7. **Implementar middleware de permisos** en todas las rutas

8. **Crear tests** para controladores y modelos

---

## 📦 Comandos Útiles

```bash
# Ejecutar migraciones
php artisan migrate

# Ejecutar seeders
php artisan db:seed

# Limpiar caché
php artisan optimize:clear

# Compilar assets (frontend)
npm run dev

# Ver rutas
php artisan route:list

# Crear controlador
php artisan make:controller NombreController

# Crear modelo con migración
php artisan make:model NombreModelo -m
```

---

**Documento creado por:** Gabriel
**Fecha:** 20 de Octubre, 2025
