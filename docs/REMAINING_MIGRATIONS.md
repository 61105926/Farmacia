# MIGRACIONES RESTANTES - GUÍA DE IMPLEMENTACIÓN

## Migraciones Creadas ✅

1. ✅ Módulo Usuarios (6 migraciones)
   - 2024_01_01_000001_create_roles_table
   - 2024_01_01_000002_create_permissions_table
   - 2024_01_01_000003_update_users_table
   - 2024_01_01_000004_create_role_user_table
   - 2024_01_01_000005_create_permission_role_table
   - 2024_01_01_000006_create_sessions_table

2. ✅ Módulo Clientes (3 migraciones)
   - 2024_01_02_000001_create_clients_table
   - 2024_01_02_000002_create_client_contacts_table
   - 2024_01_02_000003_create_client_addresses_table

## Migraciones Pendientes - Ejecutar en Orden

### MÓDULO 3: PRODUCTOS

```bash
php artisan make:migration create_product_categories_table
php artisan make:migration create_products_table
php artisan make:migration create_product_images_table
php artisan make:migration create_price_lists_table
php artisan make:migration create_product_prices_table
```

### MÓDULO 4: INVENTARIO (Crear ANTES de Preventas/Ventas)

```bash
php artisan make:migration create_branches_table
php artisan make:migration create_warehouses_table
php artisan make:migration create_inventory_table
php artisan make:migration create_inventory_lots_table
php artisan make:migration create_inventory_movements_table
php artisan make:migration create_stock_transfers_table
php artisan make:migration create_stock_transfer_items_table
```

### MÓDULO 5: VENTAS

```bash
php artisan make:migration create_payment_terms_table
php artisan make:migration create_presales_table
php artisan make:migration create_presale_items_table
php artisan make:migration create_sales_table
php artisan make:migration create_sale_items_table
php artisan make:migration create_credit_notes_table
php artisan make:migration create_credit_note_items_table
```

### MÓDULO 6: CUENTAS POR COBRAR

```bash
php artisan make:migration create_receivables_table
php artisan make:migration create_payments_table
php artisan make:migration create_payment_applications_table
```

### MÓDULO 7: AUDITORÍA

```bash
php artisan make:migration create_activity_log_table
php artisan make:migration create_system_settings_table
```

## Comando para Ejecutar Todas

```bash
php artisan migrate
```

## Rollback si es Necesario

```bash
php artisan migrate:rollback
php artisan migrate:fresh  # Cuidado: Elimina todos los datos
```
