# 🚀 Guía de Deployment en Coolify - Sistema Farmacia

## 📋 Tabla de Contenidos

1. [Problema Actual](#problema-actual)
2. [Solución Implementada](#solución-implementada)
3. [Cómo Actualizar Producción](#cómo-actualizar-producción)
4. [Scripts Disponibles](#scripts-disponibles)
5. [Troubleshooting](#troubleshooting)

---

## 🔴 Problema Actual

### Síntoma:
En producción (Coolify) solo se ven los títulos de menú:
- ✅ Principal → Dashboard
- ❌ Gestión → (vacío, no se ven Usuarios, Clientes, Productos)
- ❌ Operaciones → (vacío, no se ven Inventario, Preventas, Ventas, Cuentas por Cobrar)
- ❌ Análisis → (vacío, no se ven Reportes, Configuración, Monitor)

### Causa Raíz:
**Los permisos en la base de datos NO coinciden con los que el sistema requiere.**

El sistema requiere permisos como:
- `users.index`, `clients.index`, `products.index`, etc.

Pero el seeder original solo creaba:
- `users.view`, `users.create`, `users.edit`, `users.delete`

**Resultado:** El usuario admin no tiene los permisos `.index` necesarios para ver los menús.

---

## ✅ Solución Implementada

### 1. **RolePermissionSeeder Actualizado**

Se actualizó `/database/seeders/RolePermissionSeeder.php` para crear todos los permisos necesarios:

```php
$permissions = [
    // Cada módulo ahora tiene:
    'users.index',      // ← NUEVO (para ver en sidebar)
    'users.view',       // ← Ya existía
    'users.create',
    'users.edit',
    'users.update',     // ← NUEVO
    'users.delete',

    // Lo mismo para:
    // - clients.*
    // - products.*
    // - inventory.*
    // - presales.*
    // - sales.*
    // - receivables.*
    // - reports.*
    // - config.*
    // - system.monitor
];
```

### 2. **Scripts de Actualización Creados**

Se crearon dos scripts para actualizar la base de datos en producción:

#### a) `update-permissions-production.sh` (RECOMENDADO)
- ✅ **Seguro**: No borra datos existentes
- ✅ Solo actualiza permisos
- ✅ Mantiene usuarios, clientes, productos, ventas, etc.

#### b) `reset-db-production.sh` (PELIGROSO)
- ⚠️ **Destructivo**: Borra TODA la base de datos
- ⚠️ Recrea todo desde cero
- ⚠️ Solo usar en desarrollo o si necesitas empezar de cero

---

## 🔧 Cómo Actualizar Producción

### **Método 1: Actualizar Solo Permisos (RECOMENDADO)**

Si ya tienes datos en producción y solo quieres agregar los permisos faltantes:

```bash
# 1. Conectarse al contenedor en Coolify
# (Desde la UI de Coolify, abre la terminal del contenedor)

# 2. Ejecutar el script de actualización
bash /var/www/html/update-permissions-production.sh
```

**Resultado esperado:**
```
✅ Permisos actualizados exitosamente

📋 Permisos ahora disponibles:
   ✓ users.index, users.view, users.create, ...
   ✓ clients.index, clients.view, clients.create, ...
   ✓ products.index, products.view, products.create, ...
   [... más permisos ...]
```

Después de ejecutar este script:
1. Refresca la página en tu navegador
2. Deberías ver todos los menús: Usuarios, Clientes, Productos, Inventario, etc.

---

### **Método 2: Reiniciar Base de Datos Completa (Solo si es necesario)**

⚠️ **ADVERTENCIA:** Esto eliminará TODOS los datos existentes.

Solo usar si:
- Es tu primer deployment
- La base de datos está corrupta
- Quieres empezar de cero

```bash
# 1. Conectarse al contenedor en Coolify

# 2. Ejecutar el script de reinicio
bash /var/www/html/reset-db-production.sh

# 3. El script pedirá confirmación
# Escribe "SI" (en mayúsculas) para confirmar
```

**Resultado:**
- Base de datos completamente nueva
- Usuario admin creado:
  - Email: `admin@farmacia.com`
  - Password: `admin123`
- Roles creados: Administrador, Vendedor, Inventario, Contador
- Todos los permisos asignados

---

### **Método 3: Actualización Manual (Avanzado)**

Si prefieres ejecutar los comandos manualmente:

```bash
# 1. Limpiar caché
php artisan config:clear
php artisan cache:clear

# 2. Ejecutar seeder de permisos
php artisan db:seed --class=RolePermissionSeeder --force

# 3. Limpiar caché de permisos
php artisan permission:cache-reset

# 4. (Opcional) Verificar permisos
php artisan tinker
>>> \Spatie\Permission\Models\Permission::all()->pluck('name')
```

---

## 📦 Scripts Disponibles

### 1. `update-permissions-production.sh`

**Propósito:** Actualizar permisos sin borrar datos

**Ubicación:** `/var/www/html/update-permissions-production.sh`

**Qué hace:**
1. Limpia caché de Laravel
2. Ejecuta `RolePermissionSeeder`
3. Limpia caché de permisos
4. Muestra lista de permisos actualizados

**Cuándo usar:**
- ✅ Después de hacer push de cambios en permisos
- ✅ Si los menús no aparecen en producción
- ✅ Si agregaste nuevos módulos

**Seguridad:** ✅ Seguro - No borra datos

---

### 2. `reset-db-production.sh`

**Propósito:** Reiniciar completamente la base de datos

**Ubicación:** `/var/www/html/reset-db-production.sh`

**Qué hace:**
1. Pide confirmación (debes escribir "SI")
2. Limpia toda la base de datos
3. Ejecuta todas las migraciones
4. Ejecuta todos los seeders
5. Crea usuario admin por defecto

**Cuándo usar:**
- ⚠️ Primer deployment en servidor nuevo
- ⚠️ Base de datos corrupta
- ⚠️ Desarrollo/Testing

**Seguridad:** ❌ Peligroso - Borra todo

---

## 🔍 Troubleshooting

### Problema 1: "Los menús siguen sin aparecer después de ejecutar el script"

**Soluciones:**

```bash
# 1. Limpiar caché del navegador
# - Ctrl + Shift + R (Chrome/Firefox)
# - Cmd + Shift + R (Mac)

# 2. Verificar que el usuario tenga el rol correcto
php artisan tinker
>>> $user = \App\Models\User::where('email', 'admin@farmacia.com')->first();
>>> $user->roles->pluck('name'); // Debería mostrar "Administrador"

# 3. Verificar permisos del usuario
>>> $user->getAllPermissions()->pluck('name');
// Debería mostrar: users.index, clients.index, products.index, etc.

# 4. Limpiar TODO el caché
php artisan optimize:clear
php artisan permission:cache-reset
```

---

### Problema 2: "Error al ejecutar el script - Permission denied"

```bash
# Dar permisos de ejecución al script
chmod +x /var/www/html/update-permissions-production.sh
chmod +x /var/www/html/reset-db-production.sh
```

---

### Problema 3: "Error: Database connection failed"

**Solución:**

```bash
# 1. Verificar que MariaDB esté corriendo
service mariadb status

# 2. Si no está corriendo, iniciarlo
service mariadb start

# 3. Verificar conexión
php artisan tinker
>>> DB::connection()->getPdo();
```

---

### Problema 4: "Error: SQLSTATE[HY000] [2002] Connection refused"

Esto significa que el archivo `.env` no está configurado correctamente.

**Solución:**

```bash
# 1. Verificar que existe .env
ls -la /var/www/html/.env

# 2. Si no existe, copiar .env.docker
cp /var/www/html/.env.docker /var/www/html/.env

# 3. Generar app key
php artisan key:generate --force

# 4. Reiniciar Apache
service apache2 restart
```

---

### Problema 5: "Los permisos están duplicados"

Si ejecutaste el script de permisos múltiples veces, podrías tener permisos duplicados.

**Solución:**

```bash
# Opción A: Ignorar (no afecta funcionalidad)
# Los permisos duplicados no causan problemas

# Opción B: Limpiar y recrear (borra TODO)
bash /var/www/html/reset-db-production.sh
```

---

## 📊 Verificación Post-Deployment

Después de ejecutar cualquiera de los scripts, verifica que todo funcione:

### 1. **Verificar Login**
```
Email: admin@farmacia.com
Password: admin123
```

### 2. **Verificar Menús Visibles**

Deberías ver en el sidebar:

**Principal**
- ✅ Dashboard

**Gestión**
- ✅ Usuarios
- ✅ Clientes
- ✅ Productos

**Operaciones**
- ✅ Inventario
- ✅ Preventas
- ✅ Ventas
- ✅ Cuentas por Cobrar

**Análisis**
- ✅ Reportes
- ✅ Configuración
- ✅ Monitor Sistema

### 3. **Verificar Acceso a Páginas**

Prueba hacer clic en cada menú:
- `/usuarios` → Lista de usuarios
- `/clientes` → Lista de clientes
- `/productos` → Lista de productos
- `/inventario` → Inventario
- `/preventas` → Preventas
- `/ventas` → Ventas
- `/cuentas-por-cobrar` → Cuentas por cobrar

---

## 🔄 Flujo de Deployment Completo

### Para Nuevos Deployments:

```bash
# 1. Push a Git
git add .
git commit -m "Fix: Actualizar permisos para sidebar"
git push origin main

# 2. Coolify hace deploy automático
# (espera a que termine el build)

# 3. Una vez deployed, conectarse al contenedor
# (desde UI de Coolify)

# 4. Ejecutar script de actualización
bash /var/www/html/update-permissions-production.sh

# 5. Verificar en el navegador
# - Refrescar página
# - Login como admin
# - Ver que aparezcan todos los menús
```

---

### Para Actualizar Permisos Sin Redeploy:

Si solo necesitas actualizar los permisos (sin cambios de código):

```bash
# 1. Conectarse al contenedor en Coolify

# 2. Ejecutar script
bash /var/www/html/update-permissions-production.sh

# 3. Listo - refresca el navegador
```

---

## 📝 Notas Importantes

1. **Backup Antes de Resetear:**
   - Si vas a usar `reset-db-production.sh`, haz backup primero
   - Exporta datos importantes si existen

2. **Cambiar Contraseña Admin:**
   - SIEMPRE cambia la contraseña por defecto (`admin123`)
   - Ir a Usuarios → Editar Admin → Nueva contraseña

3. **Variables de Entorno:**
   - El `.env.docker` se copia automáticamente
   - Para producción real, configura:
     - `APP_DEBUG=false`
     - `APP_ENV=production`
     - `APP_URL` con tu dominio real

4. **Logs:**
   - Ver logs de Laravel: `tail -f storage/logs/laravel.log`
   - Ver logs de Apache: `tail -f /var/log/apache2/error.log`

---

## 🎯 Checklist de Deployment

Antes de considerar el deployment exitoso:

- [ ] Build completa sin errores
- [ ] Contenedor corriendo en Coolify
- [ ] Script de permisos ejecutado
- [ ] Login funciona
- [ ] Todos los menús aparecen en sidebar
- [ ] Cada página es accesible
- [ ] Contraseña admin cambiada
- [ ] Logs sin errores críticos

---

**Última actualización:** 2025-10-21
**Versión:** 1.0
**Mantenido por:** Sistema Farmacia Pando Central
