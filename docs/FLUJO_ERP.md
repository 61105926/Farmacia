# Flujo Completo del ERP - Sistema de Farmacia

## Visión General del Sistema

Este es un ERP completo para la gestión de una farmacia que incluye los siguientes módulos principales:

### Módulos Implementados

1. **Clientes (Farmacias)**
2. **Productos**
3. **Inventario**
4. **Pre-Ventas**
5. **Ventas**
6. **Facturas**
7. **Cuentas por Cobrar**
8. **Pagos**
9. **Usuarios y Permisos**
10. **Dashboard y Reportes**

---

## Flujo Principal de Negocio

### 1. PRE-VENTA → VENTA → FACTURA → CUENTA POR COBRAR → PAGO

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│  PRE-VENTA  │────>│    VENTA    │────>│   FACTURA   │────>│  CUENTAS    │────>│    PAGO     │
│   (Draft)   │     │   (Draft)   │     │  (Pending)  │     │ POR COBRAR  │     │ (Completed) │
└─────────────┘     └─────────────┘     └─────────────┘     └─────────────┘     └─────────────┘
      │                    │                    │                    │                    │
      v                    v                    v                    v                    v
  Confirmada          Completada          Aprobada             Pendiente              Aplicado
  Convertida      (-Stock Inventario)       Pagada          Parcial/Vencida         A Factura
```

---

## Detalle de Cada Módulo

### 1. Pre-Ventas (Preventas)

**Archivo**: `app/Http/Controllers/PreSaleController.php`  
**Modelo**: `app/Models/Presale.php`  
**Migración**: `database/migrations/2025_10_20_180000_create_presales_table.php`

#### Estados:
- `draft`: Borrador (editable)
- `confirmed`: Confirmada (lista para convertir)
- `converted`: Convertida a venta
- `cancelled`: Cancelada

#### Flujo:
1. Crear pre-venta con items
2. Aprobar/Confirmar pre-venta
3. Convertir a venta

#### Funcionalidades:
- Creación con múltiples items
- Cálculo automático de descuentos y totales
- Conversión automática a venta
- Impresión de pre-venta

---

### 2. Ventas

**Archivo**: `app/Http/Controllers/SaleController.php`  
**Modelo**: `app/Models/Sale.php`  
**Migración**: `database/migrations/2025_10_20_180100_create_sales_table.php`

#### Estados:
- `draft`: Borrador
- `complete`: Completada
- `cancelled`: Cancelada

#### Métodos de Pago:
- `cash`: Efectivo
- `credit`: Crédito (genera factura)
- `transfer`: Transferencia

#### Flujo Crítico al Completar Venta:

```php
public function complete(Sale $sale)
{
    DB::beginTransaction();
    
    // 1. Verificar stock disponible
    foreach ($sale->items as $item) {
        if ($product->stock_quantity < $item->quantity) {
            // ERROR: Stock insuficiente
        }
    }
    
    // 2. Descontar stock
    foreach ($sale->items as $item) {
        $product->updateStock($item->quantity, 'subtract');
    }
    
    // 3. Actualizar estado
    $sale->update(['status' => 'complete']);
    
    // 4. Si es crédito, generar factura automáticamente
    if ($sale->payment_method === 'credit') {
        $this->generateInvoiceForSale($sale);
    }
    
    DB::commit();
}
```

---

### 3. Facturas (Invoices)

**Archivo**: `app/Http/Controllers/AccountReceivableController.php`  
**Modelo**: `app/Models/Invoice.php`  
**Migración**: `database/migrations/2025_10_20_174752_create_invoices_table.php`

#### Estados:
- `draft`: Borrador
- `pending`: Pendiente
- `approved`: Aprobada
- `paid`: Pagada
- `partially_paid`: Parcialmente pagada
- `overdue`: Vencida
- `cancelled`: Cancelada

#### Estados de Pago:
- `unpaid`: No pagado
- `partial`: Parcial
- `paid`: Pagado

#### Generación Automática:

Cuando se completa una venta a crédito:

```php
protected function generateInvoiceForSale(Sale $sale)
{
    // Crear factura
    $invoice = Invoice::create([
        'invoice_number' => Invoice::generateInvoiceNumber(),
        'sale_id' => $sale->id,
        'client_id' => $sale->client_id,
        'due_date' => now()->addDays($sale->client->credit_days ?? 30),
        'total' => $sale->total,
        'balance' => $sale->total,
        'payment_status' => 'unpaid',
        // ...
    ]);
    
    // Crear items de factura
    foreach ($sale->items as $item) {
        InvoiceItem::create([...]);
    }
    
    // Crear cuenta por cobrar
    Receivable::create([
        'invoice_id' => $invoice->id,
        'amount' => $sale->total,
        'balance' => $sale->total,
        'due_date' => $invoice->due_date,
    ]);
}
```

---

### 4. Cuentas por Cobrar (Receivables)

**Archivo**: `app/Http/Controllers/AccountReceivableController.php`  
**Modelo**: `app/Models/Receivable.php`  
**Migración**: `database/migrations/2025_10_20_212700_create_receivables_table.php`

#### Estados:
- `pending`: Pendiente
- `partial`: Parcialmente pagado
- `overdue`: Vencido
- `paid`: Pagado
- `cancelled`: Cancelado

#### Características:
- Seguimiento de días de vencimiento
- Reportes de antigüedad de saldos
- Estado de cuenta por cliente
- Alertas de cuentas vencidas

---

### 5. Pagos (Payments)

**Archivo**: `app/Http/Controllers/AccountReceivableController.php`  
**Modelo**: `app/Models/Payment.php`  
**Migración**: `database/migrations/2025_10_20_174807_create_payments_table.php`

#### Estados:
- `pending`: Pendiente
- `completed`: Completado
- `cancelled`: Cancelado
- `rejected`: Rechazado

#### Métodos de Pago:
- `cash`: Efectivo
- `transfer`: Transferencia
- `check`: Cheque
- `credit_card`: Tarjeta de Crédito
- `debit_card`: Tarjeta de Débito
- `other`: Otro

#### Flujo de Pago:

```php
public function createPayment(Request $request)
{
    DB::beginTransaction();
    
    // Crear pago
    $payment = Payment::create([
        'payment_number' => Payment::generatePaymentNumber(),
        'invoice_id' => $validated['invoice_id'],
        'amount' => $validated['amount'],
        'status' => 'completed',
    ]);
    
    // Actualizar saldo de factura
    $invoice = $payment->invoice;
    $totalPaid = $invoice->payments()->completed()->sum('amount');
    $balance = $invoice->total - $totalPaid;
    
    $paymentStatus = 'unpaid';
    if ($balance <= 0) {
        $paymentStatus = 'paid';
    } elseif ($totalPaid > 0) {
        $paymentStatus = 'partial';
    }
    
    $invoice->update([
        'paid_amount' => $totalPaid,
        'balance' => $balance,
        'payment_status' => $paymentStatus,
    ]);
    
    DB::commit();
}
```

---

### 6. Gestión de Inventario

**Modelo**: `app/Models/Product.php`

#### Método de Actualización de Stock:

```php
public function updateStock($quantity, $type = 'set', $notes = null)
{
    $currentStock = $this->stock_quantity;
    $newStock = $currentStock;
    
    switch ($type) {
        case 'add':
            $newStock = $currentStock + $quantity;
            break;
        case 'subtract':
            $newStock = max(0, $currentStock - $quantity);
            break;
        case 'set':
            $newStock = $quantity;
            break;
    }
    
    $this->update(['stock_quantity' => $newStock]);
    
    // Registrar movimiento
    if (Schema::hasTable('stock_movements')) {
        DB::table('stock_movements')->insert([
            'product_id' => $this->id,
            'type' => $type,
            'quantity' => $quantity,
            'previous_stock' => $currentStock,
            'new_stock' => $newStock,
            'notes' => $notes,
        ]);
    }
}
```

---

### 7. Clientes

**Archivo**: `app/Http/Controllers/ClientController.php`  
**Modelo**: `app/Models/Client.php`

#### Información Clave:
- `credit_limit`: Límite de crédito
- `credit_days`: Días de crédito (default: 30)
- `pending_balance`: Saldo pendiente (calculado)
- `available_credit`: Crédito disponible (calculado)

#### Relaciones:
- `presales`: Pre-ventas del cliente
- `sales`: Ventas del cliente
- `invoices`: Facturas del cliente
- `receivables`: Cuentas por cobrar
- `payments`: Pagos recibidos

---

## Dashboard y Métricas

**Archivo**: `app/Http/Controllers/DashboardController.php`

### Estadísticas Mostradas:

1. **Generales**:
   - Total de usuarios, clientes, productos
   - Total de preventas, ventas, facturas
   - Total de pagos recibidos

2. **Mensuales**:
   - Preventas del mes
   - Ventas del mes
   - Ingresos del mes
   - Facturas del mes

3. **Productos**:
   - Stock bajo
   - Sin stock
   - Productos activos/inactivos

4. **Cuentas por Cobrar**:
   - Total por cobrar
   - Facturas vencidas
   - Facturas pendientes

5. **Gráficos**:
   - Ventas últimos 6 meses
   - Crecimiento mensual
   - Top clientes

6. **Alertas**:
   - Productos con stock bajo
   - Productos sin stock
   - Clientes bloqueados
   - Facturas vencidas

---

## Permisos y Seguridad

El sistema utiliza Spatie Laravel Permission para gestionar roles y permisos.

### Middleware de Permisos:
```php
Route::middleware(['auth', 'permission:sales.create'])
```

### Permisos por Módulo:
- `clients.view`, `clients.create`, `clients.update`, `clients.delete`
- `products.view`, `products.create`, `products.update`, `products.delete`
- `sales.view`, `sales.create`, `sales.update`, `sales.delete`
- `presales.view`, `presales.create`, `presales.update`, `presales.delete`
- etc.

---

## Rutas Principales

**Archivo**: `routes/web_clients_users.php`

```php
// Clientes
Route::prefix('clientes')->name('clients.')

// Productos  
Route::prefix('productos')->name('products.')

// Pre-ventas
Route::prefix('preventas')->name('presales.')

// Ventas
Route::prefix('ventas')->name('sales.')

// Cuentas por Cobrar
Route::prefix('cuentas-por-cobrar')->name('account-receivables.')

// Reportes
Route::prefix('reportes')->name('reports.')
```

---

## Transacciones y Seguridad

Todo el código crítico usa transacciones de base de datos:

```php
DB::beginTransaction();
try {
    // Operaciones críticas
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    // Manejo de errores
}
```

---

## Próximos Pasos

1. Ejecutar migraciones:
```bash
php artisan migrate
```

2. Poblar datos de prueba (opcional):
```bash
php artisan db:seed
```

3. Verificar permisos:
```bash
php artisan permission:cache-reset
```

---

## Casos de Uso Completos

### Caso 1: Venta al Contado

1. Cliente hace pedido
2. Se crea una venta directa (sin pre-venta)
3. Se selecciona método de pago: `cash`
4. Se completa la venta
5. Se descuenta el stock automáticamente
6. **NO se genera factura** (pago inmediato)

### Caso 2: Venta a Crédito

1. Se crea pre-venta
2. Se confirma pre-venta
3. Se convierte a venta con método: `credit`
4. Se completa la venta
5. Se descuenta el stock automáticamente
6. **Se genera factura automáticamente**
7. Se crea cuenta por cobrar
8. Cliente paga después
9. Se registra pago
10. Se actualiza saldo de factura

### Caso 3: Pago Parcial

1. Factura existe con balance $1000
2. Cliente paga $500
3. Se registra pago
4. Factura actualiza:
   - `paid_amount` = $500
   - `balance` = $500
   - `payment_status` = 'partial'
5. Cliente paga $500 restantes
6. Factura actualiza:
   - `paid_amount` = $1000
   - `balance` = $0
   - `payment_status` = 'paid'
   - `paid_at` = now()

---

## Códigos de Numeración

- **PRE000001**: Código de pre-venta
- **VEN000001**: Código de venta
- **INV000001**: Número de factura
- **PAG000001**: Número de pago

Todos se generan automáticamente de forma secuencial.

---

## Stack Tecnológico

- **Backend**: Laravel 10+
- **Frontend**: Vue.js 3 con Inertia.js
- **Estilos**: TailwindCSS
- **Base de Datos**: MySQL/MariaDB
- **Autenticación**: Laravel Breeze
- **Permisos**: Spatie Laravel Permission

---

**Fecha de Última Actualización**: 2025-10-20  
**Versión del Sistema**: 2.0 - ERP Completo
