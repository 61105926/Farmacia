# 🔔 Notificaciones Automáticas - Productos y Facturas

## 📋 Descripción

Sistema de notificaciones automáticas que verifica periódicamente:
- ✅ Productos próximos a vencer
- ✅ Facturas vencidas y próximas a vencer

---

## 🎯 Comandos Disponibles

### 1. Verificar Productos Próximos a Vencer

```bash
php artisan notifications:check-expiring-products
```

**Qué hace:**
- Busca productos con fecha de vencimiento en los próximos 30 días
- Crea notificaciones según la urgencia:
  - **≤ 7 días**: Notificación de ERROR (rojo) - "CRÍTICO"
  - **≤ 15 días**: Notificación de WARNING (amarillo) - "URGENTE"
  - **> 15 días**: Notificación de WARNING (amarillo) - "ATENCIÓN"

**Notifica a:**
- Administradores (super-admin, Administrador, administrador)
- Usuarios de Inventario (bodeguero, Inventario)

**Evita duplicados:**
- Solo crea una notificación por producto por día
- Verifica si ya existe una notificación del mismo tipo para ese producto hoy

---

### 2. Verificar Facturas Vencidas

```bash
php artisan notifications:check-overdue-invoices
```

**Qué hace:**
- Busca facturas vencidas (no pagadas)
- Busca facturas que vencen en los próximos 7 días
- Crea notificaciones según el estado:
  - **Vencidas**: Notificación de ERROR (rojo) - "Factura Vencida"
  - **Próximas a vencer (≤ 3 días)**: Notificación de WARNING (amarillo) - "Factura Próxima a Vencer"

**Notifica a:**
- Administradores (super-admin, Administrador, administrador)
- Cobradores (cobrador, Contador, contabilidad)

**Evita duplicados:**
- Solo crea una notificación por factura por día
- Verifica si ya existe una notificación del mismo tipo para esa factura hoy

---

## ⏰ Programación Automática

Los comandos están programados para ejecutarse automáticamente:

### Configuración en `routes/console.php`:

```php
// Productos próximos a vencer - Diariamente a las 8:00 AM
Schedule::command('notifications:check-expiring-products')
    ->dailyAt('08:00')
    ->timezone('America/La_Paz')
    ->withoutOverlapping()
    ->runInBackground();

// Facturas vencidas - Diariamente a las 9:00 AM
Schedule::command('notifications:check-overdue-invoices')
    ->dailyAt('09:00')
    ->timezone('America/La_Paz')
    ->withoutOverlapping()
    ->runInBackground();
```

---

## 🔧 Configuración del Cron Job

Para que las tareas programadas se ejecuten automáticamente, necesitas configurar el cron job en tu servidor.

### 1. Editar el crontab:

```bash
crontab -e
```

### 2. Agregar esta línea:

```bash
* * * * * cd /ruta/a/tu/proyecto && php artisan schedule:run >> /dev/null 2>&1
```

**Nota:** Reemplaza `/ruta/a/tu/proyecto` con la ruta real de tu proyecto.

### 3. Verificar que el cron está funcionando:

```bash
php artisan schedule:list
```

Esto mostrará todas las tareas programadas y cuándo se ejecutarán.

---

## 📊 Ejemplo de Notificaciones

### Productos Próximos a Vencer:

```
🔴 CRÍTICO: Producto Próximo a Vencer
Paracetamol 500mg vence en 5 día(s) - Stock: 50 unidades (Código: PROD-001)
```

```
🟡 URGENTE: Producto Próximo a Vencer
Ibuprofeno 400mg vence en 12 día(s) - Stock: 30 unidades (Código: PROD-002)
```

### Facturas Vencidas:

```
🔴 Factura Vencida
Factura FAC-001234 vencida hace 10 día(s) - Cliente: ABC Farmacia - Saldo: $2,500.00
```

```
🟡 Factura Próxima a Vencer
Factura FAC-001235 vence en 2 día(s) - Cliente: XYZ Farmacia - Saldo: $1,800.00
```

---

## 🧪 Pruebas Manuales

### Probar verificación de productos:

```bash
php artisan notifications:check-expiring-products
```

### Probar verificación de facturas:

```bash
php artisan notifications:check-overdue-invoices
```

---

## ⚙️ Personalización

### Cambiar la frecuencia de verificación:

Edita `routes/console.php` y modifica el horario:

```php
// Cada 6 horas
Schedule::command('notifications:check-expiring-products')
    ->everySixHours();

// Cada hora
Schedule::command('notifications:check-overdue-invoices')
    ->hourly();
```

### Cambiar los días de anticipación:

Edita los comandos en `app/Console/Commands/`:

- `CheckExpiringProducts.php`: Línea 30-33 (días de anticipación)
- `CheckOverdueInvoices.php`: Línea 30-32 (días de anticipación)

---

## 📝 Notas Importantes

1. **Evita duplicados**: El sistema verifica si ya existe una notificación del mismo tipo para el mismo producto/factura hoy, evitando notificaciones duplicadas.

2. **Roles requeridos**: Los usuarios deben tener los roles correctos para recibir notificaciones:
   - Productos: Administradores e Inventario
   - Facturas: Administradores y Cobradores

3. **Zona horaria**: Las tareas están configuradas para `America/La_Paz`. Ajusta según tu ubicación.

4. **Sin solapamiento**: Las tareas usan `withoutOverlapping()` para evitar que se ejecuten múltiples instancias al mismo tiempo.

---

## 🐛 Solución de Problemas

### Las notificaciones no se crean:

1. Verifica que los productos/facturas tengan fechas de vencimiento
2. Verifica que haya usuarios con los roles correctos
3. Ejecuta los comandos manualmente para ver errores
4. Revisa los logs: `storage/logs/laravel.log`

### El cron no ejecuta las tareas:

1. Verifica que el cron job esté configurado correctamente
2. Verifica los permisos del archivo `artisan`
3. Prueba ejecutando `php artisan schedule:run` manualmente
4. Revisa los logs del sistema

---

## ✅ Estado Actual

| Funcionalidad | Estado |
|--------------|--------|
| Verificación de productos próximos a vencer | ✅ Implementado |
| Verificación de facturas vencidas | ✅ Implementado |
| Programación automática | ✅ Configurado |
| Prevención de duplicados | ✅ Implementado |
| Notificaciones por roles | ✅ Implementado |

