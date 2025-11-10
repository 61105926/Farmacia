# 🚀 Configuración de Coolify para Farmacia

Esta guía te ayudará a configurar tu aplicación Laravel en Coolify con migraciones frescas y seeders.

## 📋 Requisitos Previos

1. Tener una cuenta en Coolify
2. Tener un servidor configurado en Coolify
3. Tener acceso a la base de datos MySQL/MariaDB

## 🔧 Configuración en Coolify

### Opción 1: Usar Post Deploy Commands (Recomendado)

1. **En la interfaz de Coolify:**
   - Ve a tu aplicación
   - Ve a la sección **"Post Deploy Commands"** o **"Health Check & Deploy"**
   - Agrega los siguientes comandos:

```bash
php artisan migrate:fresh --seed --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

2. **Guardar y desplegar**

### Opción 2: Usar Variable de Entorno

1. **En Coolify, agrega una variable de entorno:**
   - Nombre: `COOLIFY_FRESH_MIGRATE`
   - Valor: `true`

2. Esto hará que el script `start-services.sh` ejecute `migrate:fresh --seed` automáticamente en el primer despliegue.

### Opción 3: Ejecutar Manualmente después del Deploy

Si prefieres ejecutar los comandos manualmente después del despliegue:

1. **Conéctate al contenedor en Coolify:**
   - Ve a tu aplicación en Coolify
   - Haz clic en "Terminal" o "Shell"

2. **Ejecuta los comandos:**
```bash
php artisan migrate:fresh --seed --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

## 📝 Variables de Entorno Necesarias

Asegúrate de configurar estas variables en Coolify:

```env
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:tu-app-key-aqui
APP_URL=https://tu-dominio.com

DB_CONNECTION=mysql
DB_HOST=tu-host-mysql
DB_PORT=3306
DB_DATABASE=farmacia
DB_USERNAME=tu-usuario
DB_PASSWORD=tu-password

# Otras variables según necesites
```

## 🔄 Flujo de Despliegue

1. **Push a Git:**
```bash
git add .
git commit -m "Configuración para Coolify"
git push origin main
```

2. **Coolify detecta el push y comienza el build**

3. **Después del build, Coolify ejecuta:**
   - Los comandos de Post Deploy (si los configuraste)
   - O puedes ejecutarlos manualmente desde la terminal

4. **Verificar el despliegue:**
   - Accede a tu aplicación
   - Verifica que la base de datos esté poblada
   - Login con: `admin@farmacia.com` / `admin123`

## ⚠️ Importante

- **`migrate:fresh` borra TODA la base de datos** y la recrea desde cero
- Solo úsalo en:
  - Primer despliegue
  - Cuando quieras resetear completamente la base de datos
  - En entornos de desarrollo/testing

- **Para actualizaciones normales**, usa:
```bash
php artisan migrate --force
```

## 🛠️ Troubleshooting

### Error: "Database connection failed"

Verifica que las variables de entorno de la base de datos estén correctas en Coolify.

### Error: "Class not found"

Asegúrate de que `composer install` se ejecute durante el build. Esto debería estar en el Dockerfile.

### Los seeders no se ejecutan

Verifica que el flag `--force` esté presente:
```bash
php artisan migrate:fresh --seed --force
```

### Permisos de storage

Si hay errores de permisos:
```bash
chown -R www-data:www-data /var/www/html/storage
chmod -R 755 /var/www/html/storage
```

## 📚 Archivos Relacionados

- `Dockerfile` - Configuración del contenedor
- `start-services.sh` - Script de inicio del contenedor
- `coolify-deploy.sh` - Script de despliegue (opcional)
- `.coolify.yml` - Configuración de Coolify (opcional)

## ✅ Checklist de Despliegue

- [ ] Variables de entorno configuradas en Coolify
- [ ] Post Deploy Commands configurados (o ejecutados manualmente)
- [ ] Build completado sin errores
- [ ] Base de datos conectada
- [ ] Migraciones ejecutadas
- [ ] Seeders ejecutados
- [ ] Aplicación accesible
- [ ] Login funcionando
- [ ] Dashboard mostrando datos

