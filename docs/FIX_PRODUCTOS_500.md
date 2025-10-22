# 🔧 Fix: Error 500 al Crear Productos

## 🔴 Problema

Al intentar crear un producto, se obtenía un **Error 500** y no se guardaba en la base de datos.

### Error en Logs:

```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'purchase_price' in 'field list'
```

---

## 🔍 Causa Raíz

**Desincronización entre el Controller y la Migration:**

### Controller usaba:
- ❌ `purchase_price`
- ❌ `unit`
- ❌ `notes`

### Migration define:
- ✅ `cost_price` (Precio de costo)
- ✅ `base_price` (Precio base)
- ✅ `sale_price` (Precio de venta)
- ✅ `unit_type` (Tipo de unidad)
- ✅ `slug` (Required, unique)

**Además**, faltaba la generación automática del campo `slug` que es **obligatorio** en la tabla.

---

## ✅ Solución Implementada

### 1. **ProductController - store() method**

#### Antes:
```php
$validated = $request->validate([
    'purchase_price' => 'required|numeric|min:0',  // ❌ Columna no existe
    'unit' => 'required|string|max:50',            // ❌ Columna no existe
    'notes' => 'nullable|string|max:1000',         // ❌ Columna no existe
]);

DB::table('products')->insertGetId([
    'purchase_price' => $validated['purchase_price'],  // ❌ Error
    'unit' => $validated['unit'],                      // ❌ Error
    // Falta 'slug' (required)                        // ❌ Error
]);
```

#### Ahora:
```php
$validated = $request->validate([
    'cost_price' => 'required|numeric|min:0',        // ✅ Correcto
    'unit_type' => 'nullable|string|max:50',         // ✅ Correcto
    'brand' => 'nullable|string|max:255',            // ✅ Nuevo campo
]);

// Generar slug único
$slug = \Str::slug($validated['name']);
$slugCount = DB::table('products')->where('slug', 'like', $slug . '%')->count();
if ($slugCount > 0) {
    $slug = $slug . '-' . ($slugCount + 1);
}

DB::table('products')->insertGetId([
    'slug' => $slug,                                 // ✅ Generado automáticamente
    'cost_price' => $validated['cost_price'],        // ✅ Correcto
    'base_price' => $validated['cost_price'],        // ✅ Correcto
    'sale_price' => $validated['sale_price'],        // ✅ Correcto
    'unit_type' => $validated['unit_type'] ?? 'unit', // ✅ Correcto
    'brand' => $validated['brand'] ?? null,          // ✅ Correcto
]);
```

---

### 2. **ProductController - update() method**

Se aplicaron las mismas correcciones:

```php
// Generar slug si cambió el nombre
if ($product->name !== $validated['name']) {
    $slug = \Str::slug($validated['name']);
    $slugCount = DB::table('products')
        ->where('slug', 'like', $slug . '%')
        ->where('id', '!=', $product->id)
        ->count();
    if ($slugCount > 0) {
        $slug = $slug . '-' . ($slugCount + 1);
    }
    $updateData['slug'] = $slug;
}
```

---

### 3. **Formularios Vue - Create.vue y Edit.vue**

Se actualizaron los nombres de campos:

#### Antes:
```javascript
const form = useForm({
    purchase_price: '',  // ❌
    unit: '',            // ❌
    notes: '',           // ❌
})
```

#### Ahora:
```javascript
const form = useForm({
    cost_price: '',      // ✅
    unit_type: '',       // ✅
    brand: '',           // ✅ Nuevo
})
```

---

## 📊 Campos de la Tabla Products (Según Migration)

### Información Básica:
- `id` - Auto-increment
- ✅ `code` - Código único del producto
- ✅ `name` - Nombre del producto
- ✅ `description` - Descripción (nullable)
- ✅ `slug` - URL amigable (unique, required)

### Categorización:
- ✅ `category_id` - FK a product_categories (nullable)
- ✅ `brand` - Marca/Laboratorio (nullable)

### Identificación Farmacéutica:
- `active_ingredient` - Principio activo (nullable)
- `dosage` - Dosificación (nullable)
- `presentation` - Presentación (nullable)
- ✅ `unit_type` - Tipo de unidad (default: 'unit')
- `units_per_package` - Unidades por paquete (default: 1)

### Registro Sanitario:
- `sanitary_registration` - Registro sanitario (nullable)
- `sanitary_expiry_date` - Fecha de vencimiento (nullable)

### Códigos:
- `barcode` - Código de barras (nullable, unique)
- `sku` - SKU (nullable)

### Precios:
- ✅ `base_price` - Precio base (default: 0)
- ✅ `cost_price` - Precio de costo (default: 0)
- ✅ `sale_price` - Precio de venta (default: 0)
- `wholesale_price` - Precio mayorista (nullable)

### Impuestos:
- `tax_rate` - Tasa de impuesto % (default: 0)
- `tax_included` - Incluye impuestos (default: false)

### Inventario:
- ✅ `stock_quantity` - Cantidad en stock (default: 0)
- ✅ `min_stock` - Stock mínimo (default: 0)
- ✅ `max_stock` - Stock máximo (default: 0)
- `reorder_point` - Punto de reorden (default: 0)

### Características Farmacéuticas:
- `requires_prescription` - Requiere receta (default: false)
- `is_controlled` - Medicamento controlado (default: false)
- `storage_conditions` - Condiciones de almacenamiento (nullable)
- `administration_route` - Vía de administración (nullable)

### Estado:
- ✅ `is_active` - Activo (default: true)
- `is_available` - Disponible (default: true)
- `allow_backorder` - Permite backorder (default: false)

### Tracking:
- ✅ `created_by` - Usuario creador (nullable)
- ✅ `updated_by` - Usuario que actualizó (nullable)
- `created_at` - Fecha de creación
- `updated_at` - Fecha de actualización
- `deleted_at` - Soft delete (nullable)

---

## 🎯 Campos Actuales en Formularios

### Create.vue / Edit.vue - Campos Enviados:

```javascript
{
  name: '',              // ✅
  code: '',              // ✅
  description: '',       // ✅
  category_id: null,     // ✅
  brand: '',             // ✅ (agregado)
  cost_price: '',        // ✅ (corregido)
  sale_price: '',        // ✅
  stock_quantity: 0,     // ✅
  min_stock: 0,          // ✅
  max_stock: 0,          // ✅
  unit_type: 'unit',     // ✅ (corregido)
  is_active: true        // ✅
}
```

---

## ✅ Resultado

Ahora al crear un producto:

1. ✅ **Se valida correctamente** con los campos de la tabla
2. ✅ **Se genera el slug automáticamente** a partir del nombre
3. ✅ **Se inserta en la base de datos** sin errores
4. ✅ **Redirige a products.index** con mensaje de éxito
5. ✅ **Muestra notificación** (usando el sistema global)

---

## 🧪 Prueba

```bash
# 1. Ir a http://localhost/productos/crear
# 2. Llenar el formulario:
#    - Nombre: Aspirina 500mg
#    - Código: ASP-001
#    - Precio de Costo: 10.50
#    - Precio de Venta: 15.00
#    - Stock: 100
#    - Stock Mínimo: 10
# 3. Guardar
# 4. Resultado esperado:
#    ✅ Redirige a /productos
#    ✅ Muestra notificación "Producto creado exitosamente"
#    ✅ El producto aparece en la lista
```

---

## 📝 Archivos Modificados

### Backend:
1. ✅ `/app/Http/Controllers/ProductController.php`
   - Método `store()` - Validaciones y columnas corregidas
   - Método `update()` - Validaciones y columnas corregidas
   - Generación automática de slug

### Frontend:
1. ✅ `/resources/js/Pages/Products/Create.vue`
   - `purchase_price` → `cost_price`
   - `unit` → `unit_type`

2. ✅ `/resources/js/Pages/Products/Edit.vue`
   - `purchase_price` → `cost_price`
   - `unit` → `unit_type`

---

## 🚀 Próximas Mejoras Recomendadas

### 1. **Usar el Modelo en lugar de DB::table()**

```php
// En lugar de:
DB::table('products')->insertGetId([...]);

// Usar:
$product = Product::create($validated);
```

**Beneficios:**
- ✅ Manejo automático de fillable
- ✅ Eventos de modelo (creating, created, etc.)
- ✅ Relaciones automáticas
- ✅ Mutators y accessors

### 2. **Form Request Class**

Crear `App\Http\Requests\StoreProductRequest`:

```php
class StoreProductRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'cost_price' => 'required|numeric|min:0',
            // ... más reglas
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'El nombre del producto es obligatorio.',
            // ... más mensajes
        ];
    }
}
```

### 3. **Slug Observer**

Crear un observer para generar slug automáticamente:

```php
class ProductObserver
{
    public function creating(Product $product)
    {
        if (empty($product->slug)) {
            $product->slug = $this->generateUniqueSlug($product->name);
        }
    }
}
```

---

**Fecha**: 2025-10-21
**Versión**: 1.0
**Estado**: ✅ CORREGIDO
