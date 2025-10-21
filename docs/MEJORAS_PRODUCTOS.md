# 🎯 Mejoras Profesionales - Módulo de Productos

## Versión 2.6 - Sistema de Acciones Inteligentes

---

## 📋 Resumen Ejecutivo

Se han implementado mejoras en el **ProductController** para proporcionar un sistema de acciones dinámicas basadas en el estado del producto y su uso en transacciones. Los botones de acción ahora son iconos funcionales con lógica de negocio inteligente.

---

## ✅ Mejoras Implementadas

### 1. **Sistema de Acciones Disponibles (availableActions)**

#### ProductController - Método show()

**Antes:**
```php
public function show(Product $product): Response
{
    $product->load(['category', 'createdBy:id,name']);

    return Inertia::render('Products/Show', [
        'product' => $product,
        'stats' => $stats,
    ]);
}
```

**Ahora:**
```php
public function show(Product $product): Response
{
    $product->load(['category', 'createdBy:id,name']);

    // ... estadísticas ...

    // Obtener acciones disponibles para el producto
    $availableActions = $this->getAvailableActions($product);

    return Inertia::render('Products/Show', [
        'product' => $product,
        'stats' => $stats,
        'availableActions' => $availableActions,
    ]);
}
```

**Beneficios:**
- ✅ UI condicional basada en estado del producto
- ✅ Prevención de acciones no permitidas
- ✅ Mejor experiencia de usuario
- ✅ Iconos funcionales inteligentes

---

### 2. **Método getAvailableActions() - Lógica Inteligente**

```php
private function getAvailableActions(Product $product): array
{
    // Verificar si el producto está siendo usado en ventas o preventas
    $hasSales = Schema::hasTable('sale_items') ?
        DB::table('sale_items')->where('product_id', $product->id)->exists() : false;

    $hasPresales = Schema::hasTable('presale_items') ?
        DB::table('presale_items')->where('product_id', $product->id)->exists() : false;

    $isUsedInTransactions = $hasSales || $hasPresales;

    return [
        // Ver siempre está disponible (ya estamos en la vista)
        'canView' => true,

        // Editar: siempre disponible
        'canEdit' => true,

        // Activar/Desactivar: siempre disponible
        'canToggleStatus' => true,
        'currentStatus' => $product->is_active,
        'toggleAction' => $product->is_active ? 'deactivate' : 'activate',
        'toggleLabel' => $product->is_active ? 'Desactivar' : 'Activar',

        // Eliminar: NO disponible (según solicitud del usuario)
        'canDelete' => false,
        'deleteDisabledReason' => $isUsedInTransactions
            ? 'El producto está siendo usado en ventas o preventas'
            : null,

        // Información adicional
        'isUsedInTransactions' => $isUsedInTransactions,
        'hasSales' => $hasSales,
        'hasPresales' => $hasPresales,
    ];
}
```

**Beneficios:**
- ✅ Validación de uso en transacciones
- ✅ Lógica de negocio centralizada
- ✅ Información contextual para la UI
- ✅ Toggle dinámico (Activar/Desactivar)

---

### 3. **ProductController index() - Acciones en Listado**

**Ahora:**
```php
public function index(Request $request): Response
{
    // ... query y filtros ...

    $products = $query->latest()->paginate(15)->withQueryString();

    // Agregar acciones disponibles a cada producto
    $products->getCollection()->transform(function ($product) {
        $product->availableActions = $this->getAvailableActions($product);
        return $product;
    });

    return Inertia::render('Products/Index', [
        'products' => InertiaHelper::sanitizeData($products),
        // ... otros datos ...
    ]);
}
```

**Beneficios:**
- ✅ Cada producto en el listado tiene sus acciones disponibles
- ✅ UI puede mostrar/ocultar botones dinámicamente
- ✅ No es necesario hacer cálculos en el frontend

---

## 🎨 Estructura de availableActions

### Objeto Retornado

```javascript
{
  // Acciones principales
  canView: true,
  canEdit: true,
  canToggleStatus: true,
  canDelete: false,  // ❌ Usuario pidió quitarlo

  // Estado actual
  currentStatus: true,  // is_active

  // Toggle action
  toggleAction: 'deactivate',  // 'activate' o 'deactivate'
  toggleLabel: 'Desactivar',   // 'Activar' o 'Desactivar'

  // Razón de deshabilitación
  deleteDisabledReason: 'El producto está siendo usado en ventas o preventas',

  // Información adicional
  isUsedInTransactions: true,
  hasSales: true,
  hasPresales: false
}
```

---

## 🔧 Uso en el Frontend (Vue.js)

### Ejemplo: Products/Show.vue

```vue
<template>
  <div>
    <h1>{{ product.name }}</h1>

    <!-- Botones de Acción con Iconos -->
    <div class="flex gap-2">
      <!-- Ver (siempre visible) -->
      <button
        v-if="availableActions.canView"
        class="btn-icon"
        title="Ver detalles"
      >
        <EyeIcon class="w-5 h-5" />
      </button>

      <!-- Editar -->
      <button
        v-if="availableActions.canEdit"
        @click="editProduct"
        class="btn-icon btn-primary"
        title="Editar producto"
      >
        <PencilIcon class="w-5 h-5" />
      </button>

      <!-- Activar/Desactivar (Toggle) -->
      <button
        v-if="availableActions.canToggleStatus"
        @click="toggleStatus"
        :class="availableActions.currentStatus ? 'btn-icon btn-warning' : 'btn-icon btn-success'"
        :title="availableActions.toggleLabel"
      >
        <component
          :is="availableActions.currentStatus ? PowerIcon : CheckCircleIcon"
          class="w-5 h-5"
        />
      </button>

      <!-- Eliminar (NO se muestra según solicitud del usuario) -->
      <!-- <button v-if="availableActions.canDelete" ...> -->
    </div>

    <!-- Advertencia si está en uso -->
    <div v-if="availableActions.isUsedInTransactions" class="alert alert-info">
      <InfoIcon class="w-5 h-5" />
      <span>
        Este producto está siendo usado en
        <span v-if="availableActions.hasSales">ventas</span>
        <span v-if="availableActions.hasSales && availableActions.hasPresales"> y </span>
        <span v-if="availableActions.hasPresales">preventas</span>
      </span>
    </div>
  </div>
</template>

<script setup>
import { EyeIcon, PencilIcon, PowerIcon, CheckCircleIcon, InfoIcon } from '@heroicons/vue/24/outline'

defineProps({
  product: Object,
  availableActions: Object,
  stats: Object
})

const editProduct = () => {
  router.visit(route('products.edit', product.id))
}

const toggleStatus = () => {
  router.post(route('products.toggleStatus', product.id), {}, {
    preserveScroll: true
  })
}
</script>
```

---

### Ejemplo: Products/Index.vue - Tabla con Iconos

```vue
<template>
  <table>
    <thead>
      <tr>
        <th>Código</th>
        <th>Nombre</th>
        <th>Stock</th>
        <th>Estado</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="product in products.data" :key="product.id">
        <td>{{ product.code }}</td>
        <td>{{ product.name }}</td>
        <td>{{ product.stock_quantity }}</td>
        <td>
          <span :class="product.is_active ? 'badge-success' : 'badge-danger'">
            {{ product.is_active ? 'Activo' : 'Inactivo' }}
          </span>
        </td>
        <td>
          <div class="flex gap-2 justify-center">
            <!-- Ver -->
            <Link
              v-if="product.availableActions.canView"
              :href="route('products.show', product.id)"
              class="btn-icon btn-sm"
              title="Ver detalles"
            >
              <EyeIcon class="w-4 h-4" />
            </Link>

            <!-- Editar -->
            <Link
              v-if="product.availableActions.canEdit"
              :href="route('products.edit', product.id)"
              class="btn-icon btn-sm btn-primary"
              title="Editar"
            >
              <PencilIcon class="w-4 h-4" />
            </Link>

            <!-- Toggle Status -->
            <button
              v-if="product.availableActions.canToggleStatus"
              @click="toggleStatus(product)"
              :class="product.availableActions.currentStatus ? 'btn-icon btn-sm btn-warning' : 'btn-icon btn-sm btn-success'"
              :title="product.availableActions.toggleLabel"
            >
              <component
                :is="product.availableActions.currentStatus ? PowerIcon : CheckCircleIcon"
                class="w-4 h-4"
              />
            </button>

            <!-- NO hay botón de eliminar según solicitud del usuario -->
          </div>
        </td>
      </tr>
    </tbody>
  </table>
</template>
```

---

## 📊 Comparativa: Antes vs Ahora

| Aspecto | Antes | Ahora |
|---------|-------|-------|
| **Botones de acción** | Genéricos, siempre visibles | Iconos dinámicos basados en estado |
| **Lógica de UI** | En el frontend | En el backend (centralizada) |
| **Validación de uso** | No existía | Verifica ventas y preventas |
| **Toggle status** | No existía | Implementado con ruta dedicada |
| **Botón eliminar** | Visible | Removido según solicitud |
| **Información contextual** | Limitada | Completa (isUsedInTransactions, etc.) |
| **Experiencia de usuario** | Básica | Profesional con iconos y tooltips |

---

## 🎯 Acciones Disponibles por Producto

### Producto Activo - NO usado en transacciones
```javascript
{
  canView: true,
  canEdit: true,
  canToggleStatus: true,     // Puede desactivar
  toggleLabel: 'Desactivar',
  canDelete: false,           // ❌ Removido
  isUsedInTransactions: false
}
```

### Producto Activo - USADO en transacciones
```javascript
{
  canView: true,
  canEdit: true,
  canToggleStatus: true,      // Puede desactivar
  toggleLabel: 'Desactivar',
  canDelete: false,            // ❌ No se puede eliminar
  deleteDisabledReason: 'El producto está siendo usado en ventas o preventas',
  isUsedInTransactions: true,
  hasSales: true,
  hasPresales: false
}
```

### Producto Inactivo
```javascript
{
  canView: true,
  canEdit: true,
  canToggleStatus: true,      // Puede activar
  toggleLabel: 'Activar',
  canDelete: false,            // ❌ Removido
  isUsedInTransactions: false
}
```

---

## 🚀 Rutas Necesarias

Asegúrate de tener estas rutas en `routes/web.php`:

```php
Route::middleware(['auth'])->prefix('products')->name('products.')->group(function () {
    Route::get('/', [ProductController::class, 'index'])->name('index');
    Route::get('/create', [ProductController::class, 'create'])->name('create');
    Route::post('/', [ProductController::class, 'store'])->name('store');
    Route::get('/{product}', [ProductController::class, 'show'])->name('show');
    Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
    Route::put('/{product}', [ProductController::class, 'update'])->name('update');

    // Toggle Status (nuevo)
    Route::post('/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('toggleStatus');

    // Delete (opcional, ya que está deshabilitado)
    Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');

    // Stock
    Route::post('/{product}/update-stock', [ProductController::class, 'updateStock'])->name('updateStock');
});
```

---

## 💡 Recomendaciones de Iconos

### Usando Heroicons (Vue)

```javascript
import {
  EyeIcon,           // Ver
  PencilIcon,        // Editar
  PowerIcon,         // Desactivar
  CheckCircleIcon,   // Activar
  TrashIcon,         // Eliminar (deshabilitado en este caso)
} from '@heroicons/vue/24/outline'
```

### Usando Font Awesome

```html
<i class="fas fa-eye"></i>       <!-- Ver -->
<i class="fas fa-edit"></i>      <!-- Editar -->
<i class="fas fa-power-off"></i> <!-- Desactivar -->
<i class="fas fa-check-circle"></i> <!-- Activar -->
```

---

## 🔒 Validaciones de Seguridad

### En el Backend (ProductController)

```php
public function toggleStatus(Product $product): RedirectResponse
{
    try {
        // El usuario siempre puede activar/desactivar
        // No hay restricción por uso en transacciones

        DB::table('products')
            ->where('id', $product->id)
            ->update([
                'is_active' => !$product->is_active,
                'updated_at' => now(),
            ]);

        $status = !$product->is_active ? 'activado' : 'desactivado';

        return back()->with('success', "Producto {$status} exitosamente.");

    } catch (\Exception $e) {
        Log::error('ProductController toggleStatus error: ' . $e->getMessage());
        return back()->with('error', 'Error al cambiar el estado del producto.');
    }
}
```

**Beneficios:**
- ✅ Transición segura de estado
- ✅ Logging de errores
- ✅ Mensaje de éxito claro
- ✅ Manejo de excepciones robusto

---

## 📈 Mejoras de UX Implementadas

1. **Iconos en lugar de texto**
   - Interfaz más limpia y profesional
   - Menor espacio ocupado
   - Reconocimiento visual rápido

2. **Tooltips informativos**
   - `title="Ver detalles"`
   - `title="Editar producto"`
   - `title="Desactivar"` / `title="Activar"`

3. **Colores semánticos**
   - **Ver**: Gris/Neutral
   - **Editar**: Azul/Primary
   - **Desactivar**: Naranja/Warning
   - **Activar**: Verde/Success

4. **Información contextual**
   - Advertencias cuando el producto está en uso
   - Razones de por qué una acción está deshabilitada

---

## 🎯 Próximas Mejoras Recomendadas

1. **Confirmación de Desactivación**
   ```javascript
   const toggleStatus = (product) => {
     if (product.availableActions.currentStatus) {
       // Si está activo, confirmar desactivación
       if (confirm('¿Está seguro de desactivar este producto?')) {
         router.post(route('products.toggleStatus', product.id))
       }
     } else {
       // Si está inactivo, activar directamente
       router.post(route('products.toggleStatus', product.id))
     }
   }
   ```

2. **Dropdown de Acciones**
   ```vue
   <Dropdown>
     <DropdownLink :href="route('products.show', product.id)">
       <EyeIcon /> Ver detalles
     </DropdownLink>
     <DropdownLink :href="route('products.edit', product.id)">
       <PencilIcon /> Editar
     </DropdownLink>
     <DropdownButton @click="toggleStatus(product)">
       <PowerIcon /> {{ product.availableActions.toggleLabel }}
     </DropdownButton>
   </Dropdown>
   ```

3. **Permisos de Usuario**
   ```php
   return [
       'canView' => true,
       'canEdit' => auth()->user()->can('edit_products'),
       'canToggleStatus' => auth()->user()->can('manage_products'),
       'canDelete' => false,
   ];
   ```

---

## 📝 Resumen de Cambios en Archivos

### ProductController.php

1. **Método `show()`**
   - ✅ Agregado `availableActions` al response

2. **Método `index()`**
   - ✅ Agregado `transform()` para incluir `availableActions` en cada producto

3. **Método `getAvailableActions()`** (nuevo)
   - ✅ Verificación de uso en ventas
   - ✅ Verificación de uso en preventas
   - ✅ Lógica de toggle dinámico
   - ✅ Información contextual completa

4. **Método `toggleStatus()`** (ya existía)
   - ✅ Funcional y listo para usar

---

**Fecha**: 2025-10-21
**Versión**: 2.6 - Sistema de Acciones Inteligentes
**Estado**: ✅ IMPLEMENTADO
**Solicitado por**: Usuario
**Requisitos**: Iconos funcionales, sin botón eliminar, activar/desactivar
