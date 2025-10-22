# 🔔 Sistema de Notificaciones Unificado

## ✅ Implementación Completada

Se ha estandarizado el sistema de notificaciones en todo el sistema, utilizando el `NotificationContainer` global.

---

## 🎯 Cambios Realizados

### 1. **Módulo de Usuarios** - Migrado a Notificaciones Globales ✅

**Archivo**: `/resources/js/Pages/Users/Index.vue`

#### **Antes** (Sistema personalizado):
```vue
<!-- Template -->
<div v-if="showConfirmModal">...</div>
<div v-if="showAlert">...</div>

<!-- Script -->
const showConfirmModal = ref(false)
const showAlert = ref(false)
const confirmModal = reactive({ ... })
const alertData = reactive({ ... })

const showConfirmDialog = (title, message, confirmText, buttonClass, action) => {
  // Mostrar modal personalizado
}

const showAlertMessage = (title, message, type) => {
  // Mostrar alerta personalizada
}
```

#### **Ahora** (Sistema global):
```vue
<!-- Sin modales en template -->

<!-- Script -->
import { watch } from 'vue'
import { usePage } from '@inertiajs/vue3'

// Confirmación nativa del navegador
const showConfirmDialog = (title, message) => {
  return window.confirm(`${title}\n\n${message}`)
}

// Notificaciones globales
window.$notify?.success('Título', 'Mensaje')
window.$notify?.error('Título', 'Mensaje')

// Watch para flash messages
const page = usePage()
watch(
  () => page.props.flash,
  (flash) => {
    if (flash?.success) window.$notify?.success('Éxito', flash.success)
    if (flash?.error) window.$notify?.error('Error', flash.error)
  },
  { deep: true, immediate: true }
)
```

---

### 2. **Módulo de Clientes** - Ya usa Sistema Global ✅

**Archivo**: `/resources/js/Pages/Clients/Index.vue`

Ya implementado con el mismo patrón:
```javascript
const page = usePage()
watch(
  () => page.props.flash,
  (flash) => {
    if (flash?.success) window.$notify?.success('Éxito', flash.success)
    if (flash?.error) window.$notify?.error('Error', flash.error)
  },
  { deep: true, immediate: true }
)
```

---

## 📋 Sistema de Notificaciones Global

### **Componente Principal**
**Ubicación**: `/resources/js/Components/layout/NotificationContainer.vue`

### **Cómo se Inicializa**
Se monta en `AdminLayout.vue`:
```vue
<NotificationContainer />
```

Al montarse, expone métodos globales:
```javascript
window.$notify = {
  success: (title, message) => { ... },
  error: (title, message) => { ... },
  warning: (title, message) => { ... },
  info: (title, message) => { ... },
  remove: (id) => { ... }
}
```

---

## 🎨 Tipos de Notificaciones

### 1. **Success** ✅
```javascript
window.$notify.success('Éxito', 'Cliente creado exitosamente')
```
- **Color**: Verde
- **Icono**: CheckCircle
- **Duración**: 5 segundos (auto-cierre)

### 2. **Error** ❌
```javascript
window.$notify.error('Error', 'No se pudo guardar el cliente')
```
- **Color**: Rojo
- **Icono**: XCircle
- **Duración**: No se auto-cierra (requiere cerrar manualmente)

### 3. **Warning** ⚠️
```javascript
window.$notify.warning('Advertencia', 'El límite de crédito está cerca del máximo')
```
- **Color**: Amarillo
- **Icono**: AlertCircle
- **Duración**: 5 segundos

### 4. **Info** ℹ️
```javascript
window.$notify.info('Información', 'Los cambios se guardarán automáticamente')
```
- **Color**: Azul
- **Icono**: Info
- **Duración**: 5 segundos

---

## 🔄 Flujo Completo

### Backend → Frontend

1. **Backend (Laravel Controller)**
   ```php
   return redirect()->route('users.index')
       ->with('success', 'Usuario creado exitosamente');
   ```

2. **Middleware Inertia** (automático)
   ```php
   'flash' => [
       'success' => fn () => $request->session()->get('success'),
       'error' => fn () => $request->session()->get('error'),
   ]
   ```

3. **Frontend (Vue Component)**
   ```javascript
   const page = usePage()
   watch(() => page.props.flash, (flash) => {
     if (flash?.success) {
       window.$notify?.success('Éxito', flash.success)
     }
   })
   ```

4. **NotificationContainer** (automático)
   - Muestra la notificación visualmente
   - Inicia temporizador de auto-cierre
   - Muestra barra de progreso

---

## 📝 Patrón Estándar para Nuevos Módulos

### 1. **Imports**
```javascript
import { watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
```

### 2. **Watcher de Flash Messages**
```javascript
const page = usePage()
watch(
  () => page.props.flash,
  (flash) => {
    if (flash?.success) {
      window.$notify?.success('Éxito', flash.success)
    }
    if (flash?.error) {
      window.$notify?.error('Error', flash.error)
    }
  },
  { deep: true, immediate: true }
)
```

### 3. **Notificaciones Directas** (sin recargar página)
```javascript
// En callbacks de Inertia
router.post('/ruta', data, {
  onSuccess: () => {
    window.$notify?.success('Éxito', 'Operación completada')
  },
  onError: () => {
    window.$notify?.error('Error', 'Algo salió mal')
  }
})
```

### 4. **Confirmaciones**
```javascript
const confirmed = window.confirm('¿Estás seguro de continuar?')
if (confirmed) {
  // Ejecutar acción
}
```

---

## 🚀 Ventajas del Sistema Unificado

### ✅ **Consistencia Visual**
- Todas las notificaciones tienen el mismo diseño
- Misma posición, animaciones y comportamiento

### ✅ **Menos Código**
- No necesitas crear modales personalizados en cada componente
- Código más limpio y mantenible

### ✅ **Mejor UX**
- Notificaciones no bloquean la interfaz
- Se auto-cierran después de 5 segundos
- Múltiples notificaciones se apilan correctamente

### ✅ **Fácil de Usar**
- API simple y global (`window.$notify`)
- Compatible con mensajes flash de Laravel

---

## 📊 Estado Actual de Módulos

| Módulo | Sistema de Notificaciones | Estado |
|--------|---------------------------|--------|
| **Usuarios** | ✅ Global (window.$notify) | ✅ Completado |
| **Clientes** | ✅ Global (window.$notify) | ✅ Completado |
| **Productos** | ⚠️ Por migrar | ⏳ Pendiente |
| **Preventas** | ⚠️ Por migrar | ⏳ Pendiente |
| **Ventas** | ⚠️ Por migrar | ⏳ Pendiente |
| **Inventario** | ⚠️ Por migrar | ⏳ Pendiente |
| **Reportes** | ⚠️ Por migrar | ⏳ Pendiente |

---

## 🔧 Cómo Migrar Otros Módulos

### Paso 1: Eliminar Modales Personalizados del Template
```diff
- <div v-if="showAlert">...</div>
- <div v-if="showConfirmModal">...</div>
```

### Paso 2: Agregar Imports
```diff
+ import { watch } from 'vue'
+ import { usePage } from '@inertiajs/vue3'
```

### Paso 3: Eliminar Variables de Estado
```diff
- const showAlert = ref(false)
- const showConfirmModal = ref(false)
- const alertData = reactive({ ... })
- const confirmModal = reactive({ ... })
```

### Paso 4: Reemplazar Funciones
```diff
- const showAlertMessage = (title, message, type) => { ... }
+ // Usar window.$notify.success() directamente
```

### Paso 5: Agregar Watcher
```javascript
const page = usePage()
watch(
  () => page.props.flash,
  (flash) => {
    if (flash?.success) window.$notify?.success('Éxito', flash.success)
    if (flash?.error) window.$notify?.error('Error', flash.error)
  },
  { deep: true, immediate: true }
)
```

---

## 🐛 Troubleshooting

### No aparecen las notificaciones

**Solución 1**: Verificar que NotificationContainer esté montado
```javascript
// En consola del navegador
console.log(window.$notify)
// Debe mostrar: { success: ƒ, error: ƒ, warning: ƒ, info: ƒ }
```

**Solución 2**: Verificar que AdminLayout incluya NotificationContainer
```vue
<NotificationContainer />
```

**Solución 3**: Verificar flash messages en props
```javascript
// En Vue DevTools
$page.props.flash
```

### Las notificaciones no se auto-cierran

- **Error notifications** no se auto-cierran por diseño
- Para otros tipos, verificar que `autoDismiss: true` en NotificationContainer

---

**Fecha**: 2025-10-21
**Versión**: 2.0
**Estado**: ✅ IMPLEMENTADO (Usuarios y Clientes)
