# Arreglo: Ajuste de Stock y Historial de Movimientos

## Problema

El usuario reportó dos problemas principales:

1. **"Movimientos de Stock - Historial completo de ajustes de stock"** - El botón de historial no funcionaba
2. **"al recargar la página recién se actualiza el stock"** - Los ajustes de stock no se reflejaban en tiempo real en la UI

---

## Cambios Implementados

### 1. **Ajuste de Stock en Tiempo Real**

#### Archivo: `/resources/js/Pages/Products/Index.vue`

**Problema**: La función `submitStockAdjustment` no recargaba los datos después de ajustar el stock.

**Antes**:
```javascript
const submitStockAdjustment = () => {
  if (!canSubmitStockAdjustment.value) return

  router.post(`/productos/${stockAdjustment.product_id}/ajustar-stock`, stockAdjustment, {
    onSuccess: () => {
      showStockAdjustment.value = false
      showAlert('success', 'Stock Ajustado', 'El stock del producto ha sido ajustado exitosamente.')
    },
    onError: () => {
      showAlert('error', 'Error', 'No se pudo ajustar el stock del producto.')
    }
  })
}
```

**Ahora**:
```javascript
const submitStockAdjustment = () => {
  if (!canSubmitStockAdjustment.value) return

  router.post(`/productos/${stockAdjustment.product_id}/ajustar-stock`, stockAdjustment, {
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => {
      showStockAdjustment.value = false
      // Recargar productos para actualizar UI en tiempo real
      router.reload({ only: ['products', 'stats'] })
      // Flash message will be handled by watcher
    },
    onError: (errors) => {
      console.error('Error:', errors)
      // Flash message will be handled by watcher
    }
  })
}
```

**Cambios**:
- ✅ Agregado `router.reload({ only: ['products', 'stats'] })` para recargar datos en tiempo real
- ✅ Eliminadas notificaciones manuales (ahora las maneja el watcher de flash messages)
- ✅ Agregado `preserveState` y `preserveScroll` para mejor UX

---

### 2. **Botón de Historial de Stock**

#### Archivo: `/resources/js/Pages/Products/Index.vue`

**Problema**: El botón "Historial de Stock" ya tenía el `@click` pero faltaba verificar el backend.

**Código**:
```vue
<Button
  @click="$inertia.visit(`/productos/${product.id}/historial-stock`)"
  variant="outline"
  size="sm"
  class="h-8 w-8 p-0"
  title="Historial de Stock"
>
  <History class="h-4 w-4" />
</Button>
```

---

### 3. **Método `stockHistory` en Backend**

#### Archivo: `/app/Http/Controllers/ProductController.php`

**Agregado nuevo método**:
```php
/**
 * Ver historial de movimientos de stock de un producto
 */
public function stockHistory(Product $product): Response
{
    try {
        $movements = DB::table('stock_movements')
            ->where('product_id', $product->id)
            ->leftJoin('users', 'stock_movements.created_by', '=', 'users.id')
            ->select(
                'stock_movements.*',
                'users.name as user_name'
            )
            ->orderBy('stock_movements.created_at', 'desc')
            ->paginate(50);

        return Inertia::render('Products/StockHistory', [
            'product' => InertiaHelper::sanitizeData($product),
            'movements' => InertiaHelper::sanitizeData($movements),
        ]);

    } catch (\Exception $e) {
        \Log::error('ProductController stockHistory error: ' . $e->getMessage());

        return Inertia::render('Products/StockHistory', [
            'product' => InertiaHelper::sanitizeData($product),
            'movements' => [],
            'error' => 'Error al cargar historial: ' . $e->getMessage()
        ]);
    }
}
```

**Características**:
- ✅ Obtiene movimientos de stock del producto
- ✅ Hace JOIN con users para obtener el nombre del usuario que hizo el ajuste
- ✅ Ordena por fecha descendente (más recientes primero)
- ✅ Paginación de 50 registros por página
- ✅ Manejo de errores

---

### 4. **Ruta para Historial de Stock**

#### Archivo: `/routes/web_clients_users.php`

**Ruta ya existente** (línea 75):
```php
Route::get('/{product}/historial-stock', [ProductController::class, 'stockHistory'])->name('stock-history');
```

---

### 5. **Componente StockHistory.vue**

#### Archivo: `/resources/js/Pages/Products/StockHistory.vue`

**Mejora**: Agregada función para mostrar el símbolo correcto según el tipo de movimiento.

**Nueva función**:
```javascript
const getMovementQuantitySign = (type) => {
  switch (type) {
    case 'add': return '+'
    case 'subtract': return '-'
    case 'set': return '='
    case 'adjustment': return '±'
    default: return ''
  }
}
```

**Template actualizado**:
```vue
<td class="py-3 px-4 text-right">
  <span :class="getMovementQuantityClass(movement.type)">
    {{ getMovementQuantitySign(movement.type) }}{{ movement.quantity }}
  </span>
</td>
```

**Resultado**:
- ✅ **add** → `+cantidad` (verde)
- ✅ **subtract** → `-cantidad` (rojo)
- ✅ **set** → `=cantidad` (púrpura)
- ✅ **adjustment** → `±cantidad` (azul)

---

### 6. **Restaurar Botón de Ajustar Stock en Header**

#### Archivo: `/resources/js/Pages/Products/Index.vue`

**Problema**: El botón "Ajustar Stock" del header había sido eliminado.

**Restaurado**:
```vue
<div class="flex gap-2">
  <Button
    v-if="can('products.create')"
    @click="$inertia.visit('/productos/crear')"
    class="bg-primary-600 hover:bg-primary-700"
  >
    <Plus class="h-4 w-4 mr-2" />
    Nuevo Producto
  </Button>
  <Button
    @click="openStockAdjustmentModal"
    variant="outline"
    class="border-orange-500 text-orange-600 hover:bg-orange-50"
  >
    <Package class="h-4 w-4 mr-2" />
    Ajustar Stock
  </Button>
</div>
```

---

### 7. **Limpieza de Código**

#### Archivo: `/resources/js/Pages/Products/Index.vue`

**Removido import duplicado**:
```javascript
// Antes:
import { useAlert } from '@/composables/useAlert'
import AlertDialog from '@/Components/ui/AlertDialog.vue'
import { showAlert } from '@/composables/useAlert'  // ❌ Duplicado
import { debounce } from 'lodash-es'

// Ahora:
import { useAlert } from '@/composables/useAlert'
import AlertDialog from '@/Components/ui/AlertDialog.vue'
import { debounce } from 'lodash-es'
```

---

## Verificación de Tabla `stock_movements`

La tabla ya existe con la estructura correcta:

```
Columns: 12
Columns:
  - id (bigint, autoincrement)
  - product_id (bigint)
  - type (enum: 'add','subtract','set','adjustment')
  - quantity (decimal 10,3)
  - previous_stock (decimal 10,3)
  - new_stock (decimal 10,3)
  - reference_type (varchar, nullable)
  - reference_id (bigint, nullable)
  - notes (text, nullable)
  - created_by (bigint)
  - created_at (timestamp)
  - updated_at (timestamp)

Foreign Keys:
  - stock_movements_created_by_foreign → users.id
  - stock_movements_product_id_foreign → products.id (cascade on delete)
```

El método `adjustStock` en el ProductController ya estaba actualizado para usar estas columnas correctas.

---

## Flujo Completo

### 1. **Ajustar Stock desde Header**

1. Usuario hace clic en "Ajustar Stock" en el header
2. Se abre modal sin producto seleccionado (`productLocked: false`)
3. Usuario selecciona producto, tipo, cantidad y motivo
4. Se envía POST a `/productos/{product}/ajustar-stock`
5. Backend:
   - Valida datos
   - Actualiza `stock_quantity` del producto
   - Registra movimiento en `stock_movements`
   - Retorna flash message de éxito
6. Frontend:
   - Cierra modal
   - Recarga `products` y `stats` (actualización en tiempo real)
   - Watcher detecta flash message y muestra notificación

### 2. **Ajustar Stock desde Fila de Producto**

1. Usuario hace clic en botón de "Ajustar Stock" (ícono Package) en la fila del producto
2. Se abre modal con producto pre-seleccionado (`productLocked: true`)
3. Select de producto está deshabilitado
4. Usuario solo ingresa tipo, cantidad y motivo
5. Mismo flujo backend/frontend que desde header

### 3. **Ver Historial de Stock**

1. Usuario hace clic en botón "Historial de Stock" (ícono History) en la fila del producto
2. Navega a `/productos/{product}/historial-stock`
3. Backend:
   - Obtiene todos los movimientos del producto
   - JOIN con `users` para obtener nombre del usuario
   - Paginación de 50 registros
4. Frontend muestra:
   - Información del producto (stock actual, mínimo, precios)
   - Tabla de movimientos con:
     - Fecha y hora
     - Tipo (badge con color)
     - Cantidad con símbolo (±, +, -, =)
     - Stock anterior y nuevo
     - Motivo
     - Usuario que hizo el ajuste
   - Paginación

---

## Resultado

### Antes ❌:
- Al ajustar stock, la UI no se actualizaba hasta recargar página manualmente
- Botón "Historial de Stock" existía pero no estaba verificado
- Notificaciones duplicadas en ajustes de stock
- Faltaba botón "Ajustar Stock" en header

### Ahora ✅:
- Stock se actualiza en tiempo real (sin recargar página completa)
- Historial de stock funciona completamente
- Una sola notificación por acción (manejada por watcher)
- Botón "Ajustar Stock" disponible tanto en header como en filas
- Símbolos correctos en historial de movimientos (+, -, =, ±)
- Mejor UX con `preserveState` y `preserveScroll`

---

## Pruebas

### 1. **Ajustar Stock desde Header**
- ✅ Ir a `/productos`
- ✅ Hacer clic en "Ajustar Stock" en header
- ✅ Seleccionar producto, tipo, cantidad, motivo
- ✅ Guardar
- ✅ Verificar que el stock se actualiza en tiempo real sin recargar página
- ✅ Verificar que aparece solo 1 notificación de éxito

### 2. **Ajustar Stock desde Fila**
- ✅ Ir a `/productos`
- ✅ Hacer clic en ícono Package en algún producto
- ✅ Verificar que producto está pre-seleccionado y bloqueado
- ✅ Ingresar tipo, cantidad, motivo
- ✅ Guardar
- ✅ Verificar actualización en tiempo real

### 3. **Ver Historial de Stock**
- ✅ Ir a `/productos`
- ✅ Hacer clic en ícono History en algún producto
- ✅ Verificar que muestra información del producto
- ✅ Verificar que muestra tabla de movimientos
- ✅ Verificar símbolos correctos (+, -, =, ±)
- ✅ Verificar colores según tipo de movimiento
- ✅ Verificar paginación

---

**Fecha**: 2025-10-21
**Versión**: 1.0
**Estado**: ✅ COMPLETADO
