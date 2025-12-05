# 🔔 Sistema de Notificaciones - Guía de Uso

## 📋 ¿Qué Notificaciones se Mostrarán?

Actualmente, el sistema de notificaciones está **implementado pero vacío**. Las notificaciones solo aparecerán cuando se creen desde los controladores usando el `NotificationHelper`.

### 🎯 Notificaciones que Puedes Crear

#### 1. **Notificaciones Manuales** (Desde Controladores)
- ✅ Nueva venta realizada
- ✅ Nuevo pedido recibido
- ✅ Factura vencida
- ✅ Stock bajo de productos
- ✅ Pago recibido
- ✅ Cliente bloqueado
- ✅ Cualquier evento importante del sistema

#### 2. **Notificaciones Automáticas** (Pendientes de implementar)
- ⏳ Alertas de stock bajo (desde Dashboard)
- ⏳ Facturas vencidas
- ⏳ Productos próximos a vencer
- ⏳ Nuevas ventas (para administradores)
- ⏳ Cambios de estado en pedidos

---

## 🚀 Cómo Probar el Sistema

### Opción 1: Crear Notificaciones de Ejemplo

Ejecuta el comando para crear notificaciones de prueba:

```bash
php artisan notifications:create-samples
```

Esto creará 4 notificaciones de ejemplo para los primeros 5 usuarios activos.

Para un usuario específico:

```bash
php artisan notifications:create-samples 1
```

### Opción 2: Crear Notificaciones Manualmente desde Código

```php
use App\Helpers\NotificationHelper;

// En cualquier controlador
NotificationHelper::success(
    auth()->user(),
    'Título de la notificación',
    'Mensaje descriptivo',
    '/ruta-opcional'
);
```

---

## 📍 Dónde Agregar Notificaciones Automáticas

### 1. **Al Crear una Venta** (`SaleController@store`)

```php
use App\Helpers\NotificationHelper;

// Después de crear la venta exitosamente
NotificationHelper::success(
    auth()->user(),
    'Venta Creada',
    "Venta {$sale->code} creada exitosamente por $" . number_format($sale->total, 2),
    route('sales.show', $sale)
);

// Notificar a administradores
$admins = User::role('admin')->get();
NotificationHelper::createForUsers(
    $admins->all(),
    'Nueva Venta',
    "Venta {$sale->code} por $" . number_format($sale->total, 2),
    'info',
    route('sales.show', $sale)
);
```

### 2. **Al Recibir un Pago** (`PaymentController@store`)

```php
NotificationHelper::success(
    auth()->user(),
    'Pago Recibido',
    "Pago de $" . number_format($payment->amount, 2) . " registrado",
    route('payments.show', $payment)
);
```

### 3. **Al Detectar Stock Bajo** (Desde Dashboard o Job)

```php
// En un Job o comando programado
$lowStockProducts = Product::where('stock_quantity', '<=', 10)
    ->where('stock_quantity', '>', 0)
    ->get();

foreach ($lowStockProducts as $product) {
    NotificationHelper::warning(
        auth()->user(), // O usuarios específicos
        'Stock Bajo',
        "{$product->name} tiene solo {$product->stock_quantity} unidades",
        route('products.show', $product)
    );
}
```

### 4. **Al Detectar Facturas Vencidas** (Job Programado)

```php
// En un Job diario
$overdueInvoices = Invoice::where('due_date', '<', now())
    ->where('payment_status', '!=', 'paid')
    ->get();

foreach ($overdueInvoices as $invoice) {
    NotificationHelper::error(
        $invoice->client->salesperson, // Vendedor asignado
        'Factura Vencida',
        "La factura {$invoice->invoice_number} está vencida",
        route('invoices.show', $invoice)
    );
}
```

### 5. **Al Crear un Pedido/Preventa** (`PreSaleController@store`)

```php
NotificationHelper::info(
    auth()->user(),
    'Nuevo Pedido',
    "Pedido {$presale->code} creado por $" . number_format($presale->total, 2),
    route('presales.show', $presale)
);
```

---

## 🎨 Tipos de Notificaciones Disponibles

### 1. **Success** (Verde) ✅
```php
NotificationHelper::success($user, 'Título', 'Mensaje', '/ruta');
```
- Para acciones exitosas
- Ejemplo: "Venta creada exitosamente"

### 2. **Error** (Rojo) ❌
```php
NotificationHelper::error($user, 'Título', 'Mensaje', '/ruta');
```
- Para errores o problemas críticos
- Ejemplo: "Factura vencida"

### 3. **Warning** (Amarillo) ⚠️
```php
NotificationHelper::warning($user, 'Título', 'Mensaje', '/ruta');
```
- Para advertencias
- Ejemplo: "Stock bajo"

### 4. **Info** (Azul) ℹ️
```php
NotificationHelper::info($user, 'Título', 'Mensaje', '/ruta');
```
- Para información general
- Ejemplo: "Nuevo pedido recibido"

---

## 🔄 Actualización Automática

Las notificaciones se actualizan automáticamente:
- ✅ Cada 30 segundos (polling automático)
- ✅ Al abrir el dropdown de notificaciones
- ✅ Al cargar cualquier página (via Inertia props)
- ✅ Al marcar como leída

---

## 📊 Estado Actual

| Funcionalidad | Estado |
|--------------|--------|
| Sistema de notificaciones | ✅ Implementado |
| Base de datos | ✅ Creada |
| API endpoints | ✅ Funcionales |
| Frontend (Navbar) | ✅ Implementado |
| Actualización automática | ✅ Cada 30 seg |
| Notificaciones automáticas | ⏳ Pendiente |
| Notificaciones de ejemplo | ✅ Comando creado |

---

## 🎯 Próximos Pasos Recomendados

1. **Ejecutar el comando de ejemplo** para ver notificaciones:
   ```bash
   php artisan notifications:create-samples
   ```

2. **Agregar notificaciones automáticas** en:
   - `SaleController@store` - Al crear ventas
   - `PaymentController@store` - Al recibir pagos
   - `PreSaleController@store` - Al crear pedidos
   - Jobs programados - Para alertas de stock y facturas vencidas

3. **Personalizar** según tus necesidades de negocio

---

## 💡 Ejemplos de Uso Real

### Ejemplo 1: Notificar a todos los administradores
```php
$admins = User::role('admin')->get();
NotificationHelper::createForUsers(
    $admins->all(),
    'Nueva Venta Importante',
    "Venta de $" . number_format($total, 2) . " realizada",
    'success',
    route('sales.show', $sale)
);
```

### Ejemplo 2: Notificar al vendedor asignado
```php
if ($sale->salesperson) {
    NotificationHelper::success(
        $sale->salesperson,
        'Venta Asignada',
        "Se te ha asignado la venta {$sale->code}",
        route('sales.show', $sale)
    );
}
```

### Ejemplo 3: Notificación con datos adicionales
```php
NotificationHelper::create(
    $user,
    'Pedido Procesado',
    'Tu pedido está siendo preparado',
    'info',
    route('orders.show', $order),
    ['order_id' => $order->id, 'estimated_delivery' => $deliveryDate]
);
```

