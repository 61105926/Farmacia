# 📋 Configuración de Condiciones de Pago

## 📍 Ubicación

Las condiciones de pago ahora se gestionan desde el archivo de configuración:

```
/config/payment_terms.php
```

---

## ✨ Características

### 1. **Condiciones de Pago Configurables**

Puedes agregar, modificar o eliminar condiciones de pago editando el array `terms` en la configuración:

```php
'terms' => [
    [
        'name' => 'Contado',
        'code' => 'CONT',
        'days' => 0,
        'description' => 'Pago al contado',
        'is_default' => true,
    ],
    [
        'name' => '30 días',
        'code' => '30D',
        'days' => 30,
        'description' => 'Pago a 30 días',
        'is_default' => false,
    ],
    // Agregar más términos aquí...
],
```

### 2. **Sincronización Automática**

Para sincronizar los cambios de configuración con la base de datos:

```bash
php artisan db:seed --class=PaymentTermSeeder
```

Este comando:
- ✅ Crea nuevas condiciones de pago que no existan
- ✅ Actualiza condiciones existentes (por código)
- ✅ No elimina condiciones existentes en la base de datos

---

## 🎯 Condiciones de Pago Predeterminadas

El sistema incluye las siguientes condiciones:

| Nombre    | Código | Días | Predeterminado |
|-----------|--------|------|----------------|
| Contado   | CONT   | 0    | ✅             |
| 15 días   | 15D    | 15   | ❌             |
| 30 días   | 30D    | 30   | ❌             |
| 45 días   | 45D    | 45   | ❌             |
| 60 días   | 60D    | 60   | ❌             |
| 90 días   | 90D    | 90   | ❌             |

---

## 🔧 Otras Configuraciones Disponibles

### Días de Visita

```php
'visit_days' => [
    'monday' => 'Lunes',
    'tuesday' => 'Martes',
    // ...
],
```

### Frecuencia de Visitas

```php
'visit_frequencies' => [
    'weekly' => 'Semanal',
    'biweekly' => 'Quincenal',
    'monthly' => 'Mensual',
],
```

### Categorías de Cliente

```php
'categories' => [
    'A' => 'A - Alto volumen',
    'B' => 'B - Medio volumen',
    'C' => 'C - Bajo volumen',
],
```

### Tipos de Cliente

```php
'client_types' => [
    'pharmacy' => 'Farmacia',
    'chain' => 'Cadena de Farmacias',
    'hospital' => 'Hospital',
    'clinic' => 'Clínica',
    'distributor' => 'Distribuidor',
    'other' => 'Otro',
],
```

---

## 📝 Cómo Agregar una Nueva Condición de Pago

### Paso 1: Editar la Configuración

Abre `/config/payment_terms.php` y agrega un nuevo término al array `terms`:

```php
[
    'name' => '120 días',
    'code' => '120D',
    'days' => 120,
    'description' => 'Pago a 120 días',
    'is_default' => false,
],
```

### Paso 2: Sincronizar con la Base de Datos

```bash
php artisan db:seed --class=PaymentTermSeeder
```

### Paso 3: Limpiar Caché (Producción)

```bash
php artisan config:clear
php artisan cache:clear
```

---

## 🚀 Deployment

### En Producción (Coolify)

Cuando despliegues a producción, asegúrate de ejecutar:

```bash
# En tu script de deployment
php artisan config:cache
php artisan db:seed --class=PaymentTermSeeder --force
```

---

## 📊 Estructura de la Base de Datos

### Tabla: `payment_terms`

| Campo       | Tipo         | Descripción                          |
|-------------|--------------|--------------------------------------|
| id          | bigint       | ID autoincremental                   |
| name        | varchar(255) | Nombre del término (ej: "30 días")   |
| code        | varchar(255) | Código único (ej: "30D")             |
| days        | integer      | Número de días de crédito            |
| description | text         | Descripción opcional                 |
| is_active   | boolean      | Si está activo o no                  |
| is_default  | boolean      | Si es el término predeterminado      |
| created_at  | timestamp    | Fecha de creación                    |
| updated_at  | timestamp    | Fecha de última actualización        |

---

## 🔍 Uso en el Código

### Obtener Condiciones Activas

```php
use App\Models\PaymentTerm;

$terms = PaymentTerm::active()->get();
```

### Obtener la Condición Predeterminada

```php
$defaultTerm = PaymentTerm::where('is_default', true)->first();
```

### Obtener desde Configuración

```php
$termsFromConfig = config('payment_terms.terms');
```

---

## ⚠️ Consideraciones Importantes

1. **No eliminar códigos existentes**: Si modificas una condición de pago existente, mantén el mismo `code` para que se actualice en lugar de crear un duplicado.

2. **Código único obligatorio**: Cada condición debe tener un `code` único.

3. **Solo un predeterminado**: Solo debe haber una condición con `is_default = true`.

4. **Clientes existentes**: Los clientes que ya tienen asignada una condición de pago mantendrán su configuración aunque modifiques el nombre o días de esa condición.

---

## 🐛 Troubleshooting

### No aparecen las nuevas condiciones en el select

```bash
# Limpiar caché
php artisan config:clear
php artisan cache:clear

# Sincronizar nuevamente
php artisan db:seed --class=PaymentTermSeeder
```

### Error "Duplicate entry for key 'code'"

Verifica que no tengas dos condiciones con el mismo `code` en la configuración.

---

**Fecha**: 2025-10-21
**Versión**: 1.0
**Estado**: ✅ IMPLEMENTADO
