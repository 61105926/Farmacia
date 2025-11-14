<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PresaleController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

/**
 * Rutas para módulos principales
 * Middleware: auth, permissions
 */
Route::middleware(['auth'])->group(function () {

    // Clientes
    Route::prefix('clientes')->name('clients.')->group(function () {
        Route::get('/', [ClientController::class, 'index'])->middleware('permission:clients.view')->name('index');
        Route::get('/crear', [ClientController::class, 'create'])->middleware('permission:clients.create')->name('create');
        Route::post('/', [ClientController::class, 'store'])->middleware('permission:clients.create')->name('store');
        Route::get('/{client}', [ClientController::class, 'show'])->middleware('permission:clients.view')->name('show');
        Route::get('/{client}/editar', [ClientController::class, 'edit'])->middleware('permission:clients.update')->name('edit');
        Route::put('/{client}', [ClientController::class, 'update'])->middleware('permission:clients.update')->name('update');
        Route::delete('/{client}', [ClientController::class, 'destroy'])->middleware('permission:clients.delete')->name('destroy');
        Route::get('/exportar', [ClientController::class, 'export'])->middleware('permission:clients.export')->name('export');
        Route::get('/exportar/csv', [ClientController::class, 'export'])->middleware('permission:clients.export')->name('export.csv');
        
        // Funcionalidades avanzadas
        Route::post('/{client}/toggle-status', [ClientController::class, 'toggleStatus'])->middleware('permission:clients.update')->name('toggle-status');
        Route::post('/{client}/update-credit-limit', [ClientController::class, 'updateCreditLimit'])->middleware('permission:clients.update')->name('update-credit-limit');
        Route::get('/{client}/credit-history', [ClientController::class, 'creditHistory'])->middleware('permission:clients.view')->name('credit-history');
        Route::get('/{client}/statistics', [ClientController::class, 'statistics'])->middleware('permission:clients.view')->name('statistics');
        Route::get('/crear-tabla-credito', [ClientController::class, 'createCreditHistoryTablePublic'])->name('create-credit-table');
        Route::get('/crear-tabla-receivables', [ClientController::class, 'createReceivablesTablePublic'])->name('create-receivables-table');
        Route::post('/{client}/disable', [ClientController::class, 'disable'])->middleware('permission:clients.delete')->name('disable');
        Route::get('/credito-excedido', [ClientController::class, 'creditExceeded'])->middleware('permission:clients.view')->name('credit-exceeded');
        Route::get('/facturas-vencidas', [ClientController::class, 'overdueInvoices'])->middleware('permission:clients.view')->name('overdue-invoices');
    });

    // Usuarios
    Route::prefix('usuarios')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->middleware('permission:users.view')->name('index');
        Route::get('/crear', [UserController::class, 'create'])->middleware('permission:users.create')->name('create');
        Route::post('/', [UserController::class, 'store'])->middleware('permission:users.create')->name('store');
        Route::get('/{user}', [UserController::class, 'show'])->middleware('permission:users.view')->name('show');
        Route::get('/{user}/editar', [UserController::class, 'edit'])->middleware('permission:users.update')->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->middleware('permission:users.update')->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->middleware('permission:users.delete')->name('destroy');

        // Acciones especiales
        Route::post('/{user}/reset-password', [UserController::class, 'resetPassword'])->middleware('permission:users.update')->name('reset-password');
        Route::post('/{user}/unblock', [UserController::class, 'unblock'])->middleware('permission:users.update')->name('unblock');
        Route::get('/{user}/estadisticas', [UserController::class, 'statistics'])->middleware('permission:users.view')->name('statistics');
        Route::post('/{user}/toggle-block', [UserController::class, 'toggleBlock'])->middleware('permission:users.update')->name('toggle-block');
        Route::post('/{user}/disable', [UserController::class, 'disable'])->middleware('permission:users.delete')->name('disable');
        Route::post('/{user}/activate', [UserController::class, 'activate'])->middleware('permission:users.update')->name('activate');
        Route::post('/{user}/assign-clients', [UserController::class, 'assignClients'])->middleware('permission:users.update')->name('assign-clients');
        Route::get('/exportar/csv', [UserController::class, 'export'])->middleware('permission:users.view')->name('export');
        
    });

    // Productos
    Route::prefix('productos')->name('products.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/crear', [ProductController::class, 'create'])->name('create');
        Route::post('/', [ProductController::class, 'store'])->name('store');
        
        // Rutas específicas ANTES de las rutas con parámetros
        Route::get('/inventario', [ProductController::class, 'inventory'])->name('inventory');
        Route::get('/stock-bajo', [ProductController::class, 'lowStock'])->name('low-stock');
        Route::get('/sin-stock', [ProductController::class, 'outOfStock'])->name('out-of-stock');
        Route::get('/categorias', [ProductController::class, 'categories'])->name('categories');
        
        // Importación desde Excel - ANTES de las rutas con parámetros
        Route::post('/importar-excel', [ProductController::class, 'importExcel'])->name('import-excel');
        Route::get('/descargar-plantilla', [ProductController::class, 'downloadTemplate'])->name('download-template');
        
        // Rutas con parámetros DESPUÉS de las rutas específicas
        Route::get('/{product}', [ProductController::class, 'show'])->name('show');
        Route::get('/{product}/editar', [ProductController::class, 'edit'])->name('edit');
        Route::put('/{product}', [ProductController::class, 'update'])->name('update');
        Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');

        // Acciones especiales
        Route::post('/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{product}/update-stock', [ProductController::class, 'updateStock'])->name('update-stock');
        Route::post('/{product}/ajustar-stock', [ProductController::class, 'adjustStock'])->name('adjust-stock');
        Route::get('/{product}/historial-stock', [ProductController::class, 'stockHistory'])->name('stock-history');
    });

    // Inventario
    Route::prefix('inventario')->name('inventory.')->group(function () {
        Route::get('/', [App\Http\Controllers\InventoryController::class, 'index'])->name('index');
        Route::get('/movimientos', [App\Http\Controllers\InventoryController::class, 'movements'])->name('movements');
        Route::get('/stock', [App\Http\Controllers\InventoryController::class, 'stock'])->name('stock');
        Route::get('/crear', [App\Http\Controllers\InventoryController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\InventoryController::class, 'store'])->name('store');
        Route::get('/{inventory}', [App\Http\Controllers\InventoryController::class, 'show'])->name('show');
        Route::post('/ajustar', [App\Http\Controllers\InventoryController::class, 'adjust'])->name('adjust');
        Route::get('/stock-bajo', [App\Http\Controllers\InventoryController::class, 'lowStock'])->name('low-stock');
        Route::get('/por-vencer', [App\Http\Controllers\InventoryController::class, 'expired'])->name('expired');
    });

    // Preventas
    Route::prefix('preventas')->name('presales.')->group(function () {
        Route::get('/', [App\Http\Controllers\PreSaleController::class, 'index'])->name('index');
        Route::get('/crear', [App\Http\Controllers\PreSaleController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\PreSaleController::class, 'store'])->name('store');
        Route::get('/{presale}', [App\Http\Controllers\PreSaleController::class, 'show'])->name('show');
        Route::get('/{presale}/editar', [App\Http\Controllers\PreSaleController::class, 'edit'])->name('edit');
        Route::put('/{presale}', [App\Http\Controllers\PreSaleController::class, 'update'])->name('update');
        Route::delete('/{presale}', [App\Http\Controllers\PreSaleController::class, 'destroy'])->name('destroy');
        Route::post('/{presale}/enviar', [App\Http\Controllers\PreSaleController::class, 'submit'])->name('submit');
        Route::post('/{presale}/aprobar', [App\Http\Controllers\PreSaleController::class, 'approve'])->name('approve');
        Route::post('/{presale}/rechazar', [App\Http\Controllers\PreSaleController::class, 'reject'])->name('reject');
        Route::post('/{presale}/convertir', [App\Http\Controllers\PreSaleController::class, 'convertToSale'])->name('convert');
        Route::get('/{presale}/imprimir', [App\Http\Controllers\PreSaleController::class, 'print'])->name('print');
    });

    // Ventas
    Route::prefix('ventas')->name('sales.')->group(function () {
        Route::get('/', [App\Http\Controllers\SaleController::class, 'index'])->name('index');
        Route::get('/crear', [App\Http\Controllers\SaleController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\SaleController::class, 'store'])->name('store');
        Route::get('/preventa/{presaleId}/items', [App\Http\Controllers\SaleController::class, 'getPresaleItems'])->name('presale.items');
        // Rutas específicas antes de las genéricas
        Route::post('/{saleId}/marcar-pagada', [App\Http\Controllers\SaleController::class, 'markAsPaid'])->name('mark-as-paid')->where('saleId', '[0-9]+');
        
        // Ruta de prueba para depuración
        Route::get('/test-mark-paid/{saleId}', function($saleId) {
            \Log::info('Ruta de prueba llamada', ['saleId' => $saleId]);
            return response()->json(['message' => 'Ruta de prueba funcionando', 'saleId' => $saleId]);
        });
        Route::post('/{sale}/completar', [App\Http\Controllers\SaleController::class, 'complete'])->name('complete');
        Route::post('/{sale}/cancelar', [App\Http\Controllers\SaleController::class, 'cancel'])->name('cancel');
        Route::post('/{sale}/factura', [App\Http\Controllers\SaleController::class, 'generateInvoice'])->name('invoice');
        Route::get('/{sale}/editar', [App\Http\Controllers\SaleController::class, 'edit'])->name('edit');
        Route::get('/{sale}/imprimir', [App\Http\Controllers\SaleController::class, 'print'])->name('print');
        // Rutas genéricas al final
        Route::get('/{sale}', [App\Http\Controllers\SaleController::class, 'show'])->name('show');
        Route::put('/{sale}', [App\Http\Controllers\SaleController::class, 'update'])->name('update');
        Route::delete('/{sale}', [App\Http\Controllers\SaleController::class, 'destroy'])->name('destroy');
    });

    // Cuentas por Cobrar
    Route::prefix('cuentas-por-cobrar')->name('account-receivables.')->group(function () {
        Route::get('/', [App\Http\Controllers\AccountReceivableController::class, 'index'])->name('index');
        Route::get('/pagos/listado', [App\Http\Controllers\AccountReceivableController::class, 'payments'])->name('payments');
        Route::get('/pagos/export', [App\Http\Controllers\AccountReceivableController::class, 'exportPayments'])->name('payments.export');
        Route::post('/pagos', [App\Http\Controllers\AccountReceivableController::class, 'createPayment'])->name('payment.create');
        Route::get('/pagos/{payment}', [App\Http\Controllers\AccountReceivableController::class, 'showPayment'])->name('payment.show');
        Route::get('/pagos/{payment}/imprimir', [App\Http\Controllers\AccountReceivableController::class, 'printPaymentNote'])->name('payment.print');
        Route::post('/pagos/{payment}/aprobar', [App\Http\Controllers\AccountReceivableController::class, 'approvePayment'])->name('payment.approve');
        Route::post('/pagos/{payment}/cancelar', [App\Http\Controllers\AccountReceivableController::class, 'cancelPayment'])->name('payment.cancel');
        Route::get('/vencidas', [App\Http\Controllers\AccountReceivableController::class, 'overdue'])->name('overdue');
        Route::get('/cliente/{client}/estado', [App\Http\Controllers\AccountReceivableController::class, 'clientStatement'])->name('client.statement');
        Route::get('/reporte-antiguedad', [App\Http\Controllers\AccountReceivableController::class, 'agingReport'])->name('aging-report');
        Route::get('/export', [App\Http\Controllers\AccountReceivableController::class, 'export'])->name('export');
        Route::get('/api/cliente/{clientId}/facturas', [App\Http\Controllers\AccountReceivableController::class, 'getClientInvoices'])->name('api.client.invoices');
        Route::get('/{invoice}', [App\Http\Controllers\AccountReceivableController::class, 'show'])->name('show');
    });

    // Reportes
    Route::prefix('reportes')->name('reports.')->group(function () {
        Route::get('/', [App\Http\Controllers\ReportController::class, 'index'])->name('index');
        Route::get('/ventas', [App\Http\Controllers\ReportController::class, 'sales'])->name('sales');
        Route::get('/inventario', [App\Http\Controllers\ReportController::class, 'inventory'])->name('inventory');
        Route::get('/financiero', [App\Http\Controllers\ReportController::class, 'financial'])->name('financial');
        Route::get('/clientes', [App\Http\Controllers\ReportController::class, 'clients'])->name('clients');
        Route::get('/exportar', [App\Http\Controllers\ReportController::class, 'export'])->name('export');
    });

    // Rutas de monitoreo del sistema
    Route::prefix('sistema')->name('system.')->group(function () {
        Route::get('/monitor', [App\Http\Controllers\SystemMonitorController::class, 'index'])->name('monitor');
        Route::post('/limpiar-cache', [App\Http\Controllers\SystemMonitorController::class, 'clearCache'])->name('clear-cache');
        Route::post('/optimizar-bd', [App\Http\Controllers\SystemMonitorController::class, 'optimizeDatabase'])->name('optimize-database');
    });

    // Rutas para configuración de base de datos
    Route::prefix('setup')->name('setup.')->group(function () {
        Route::get('/verificar-tablas', [App\Http\Controllers\DatabaseSetupController::class, 'checkTables'])->name('check-tables');
        Route::post('/crear-tablas', [App\Http\Controllers\DatabaseSetupController::class, 'createTables'])->name('create-tables');
    });

    // Configuración
    Route::prefix('configuracion')->name('config.')->group(function () {
        Route::get('/', [App\Http\Controllers\ConfigurationController::class, 'index'])->middleware('permission:config.index')->name('index');
        Route::put('/', [App\Http\Controllers\ConfigurationController::class, 'update'])->middleware('permission:config.update')->name('update');
        Route::post('/theme', [App\Http\Controllers\ConfigurationController::class, 'updateTheme'])->name('update-theme');
    });
});
