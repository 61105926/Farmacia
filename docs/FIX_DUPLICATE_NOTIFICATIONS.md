# 🔧 Fix: Notificaciones Duplicadas

## 🔴 Problema

Al realizar acciones (crear, editar, bloquear usuarios/clientes), aparecían **múltiples notificaciones duplicadas**:

```
Éxito - Usuario deshabilitado exitosamente.
Error - []
Usuario actualizado - El usuario "..." ha sido deshabilitardo exitosamente.
Éxito - Usuario deshabilitado exitosamente.
Error - []
```

---

## 🔍 Causa Raíz

### 1. **Watcher con `immediate: true`**
El watcher se ejecutaba inmediatamente al montar el componente y luego cada vez que cambiaban los props, causando múltiples ejecuciones.

```javascript
watch(
  () => page.props.flash,
  (flash) => {
    if (flash?.success) {
      window.$notify?.success('Éxito', flash.success)
    }
  },
  { deep: true, immediate: true } // ❌ immediate causa ejecución inicial
)
```

### 2. **Notificaciones Manuales + Flash Messages**
El código estaba mostrando notificaciones manualmente **Y** el watcher también las mostraba desde flash messages:

```javascript
router.post(`/usuarios/${user.id}/${endpoint}`, {}, {
  onSuccess: () => {
    // ❌ Notificación manual
    window.$notify?.success('Usuario actualizado', '...')
  }
})

// ❌ El watcher también muestra el flash message del backend
watch(() => page.props.flash, (flash) => {
  if (flash?.success) window.$notify?.success('Éxito', flash.success)
})
```

### 3. **Errores Vacíos `[]`**
Laravel a veces retorna errores como array vacío `[]` que se mostraban como notificación.

---

## ✅ Solución Implementada

### 1. **Eliminar `immediate: true`**

**Antes**:
```javascript
watch(
  () => page.props.flash,
  (flash) => { ... },
  { deep: true, immediate: true } // ❌
)
```

**Ahora**:
```javascript
watch(
  () => page.props.flash,
  (flash) => { ... },
  { deep: true } // ✅ Solo deep, sin immediate
)
```

---

### 2. **Tracking de Últimos Mensajes**

Implementado un sistema de tracking para evitar mostrar el mismo mensaje dos veces:

```javascript
let lastFlashSuccess = null
let lastFlashError = null

watch(
  () => page.props.flash,
  (flash) => {
    // Solo mostrar si es un mensaje nuevo
    if (flash?.success && flash.success !== lastFlashSuccess) {
      lastFlashSuccess = flash.success
      window.$notify?.success('Éxito', flash.success)
    }

    // Filtrar errores vacíos y duplicados
    if (flash?.error && flash.error !== lastFlashError && flash.error !== '[]') {
      lastFlashError = flash.error
      window.$notify?.error('Error', flash.error)
    }
  },
  { deep: true }
)
```

**Beneficios**:
- ✅ Solo muestra cada mensaje una vez
- ✅ Filtra errores vacíos `[]`
- ✅ No muestra mensajes duplicados

---

### 3. **Eliminar Notificaciones Manuales**

**Antes** (Users/Index.vue):
```javascript
router.post(`/usuarios/${user.id}/${endpoint}`, {}, {
  onSuccess: () => {
    router.reload({ only: ['users'] })
    // ❌ Duplicado con flash message
    window.$notify?.success(
      'Usuario actualizado',
      `El usuario "${user.name}" ha sido ${action}do exitosamente.`
    )
  },
  onError: (errors) => {
    console.error('Error:', errors)
    // ❌ Duplicado con flash message
    window.$notify?.error('Error', `Error al ${action} el usuario.`)
  }
})
```

**Ahora**:
```javascript
router.post(`/usuarios/${user.id}/${endpoint}`, {}, {
  onSuccess: () => {
    router.reload({ only: ['users'] })
    // ✅ El watcher se encarga de mostrar el flash message
  },
  onError: (errors) => {
    console.error('Error:', errors)
    // ✅ El watcher se encarga de mostrar el flash message
  }
})
```

---

## 📝 Archivos Modificados

### 1. **`/resources/js/Pages/Users/Index.vue`**

#### Watcher actualizado:
```javascript
// Watch for flash messages
const page = usePage()
let lastFlashSuccess = null
let lastFlashError = null

watch(
  () => page.props.flash,
  (flash) => {
    if (flash?.success && flash.success !== lastFlashSuccess) {
      lastFlashSuccess = flash.success
      window.$notify?.success('Éxito', flash.success)
    }
    if (flash?.error && flash.error !== lastFlashError && flash.error !== '[]') {
      lastFlashError = flash.error
      window.$notify?.error('Error', flash.error)
    }
  },
  { deep: true }
)
```

#### Función `toggleUserStatus` simplificada:
```javascript
const toggleUserStatus = (user) => {
  const isActive = isUserActive(user)
  const action = isActive ? 'deshabilitar' : 'activar'
  const actionCapitalized = action.charAt(0).toUpperCase() + action.slice(1)

  const confirmed = showConfirmDialog(
    `${actionCapitalized} Usuario`,
    `¿Estás seguro de ${action} el usuario "${user.name}"?`
  )

  if (confirmed) {
    const endpoint = isActive ? 'disable' : 'activate'
    router.post(`/usuarios/${user.id}/${endpoint}`, {}, {
      preserveState: true,
      preserveScroll: true,
      onSuccess: () => {
        router.reload({ only: ['users'] })
        // ✅ No muestra notificación manual
      },
      onError: (errors) => {
        console.error('Error:', errors)
        // ✅ No muestra notificación manual
      }
    })
  }
}
```

---

### 2. **`/resources/js/Pages/Clients/Index.vue`**

Mismo patrón aplicado:
```javascript
const page = usePage()
let lastFlashSuccess = null
let lastFlashError = null

watch(
  () => page.props.flash,
  (flash) => {
    if (flash?.success && flash.success !== lastFlashSuccess) {
      lastFlashSuccess = flash.success
      window.$notify?.success('Éxito', flash.success)
    }
    if (flash?.error && flash.error !== lastFlashError && flash.error !== '[]') {
      lastFlashError = flash.error
      window.$notify?.error('Error', flash.error)
    }
  },
  { deep: true }
)
```

---

### 3. **`/resources/js/Pages/Clients/Edit.vue`**

Mismo patrón aplicado.

---

## 🎯 Resultado

### Antes ❌:
```
Éxito - Usuario deshabilitado exitosamente.
Error - []
Usuario actualizado - El usuario "..." ha sido deshabilitardo exitosamente.
Éxito - Usuario deshabilitado exitosamente.
Error - []
```
**5 notificaciones** (3 duplicadas, 2 errores vacíos)

### Ahora ✅:
```
Éxito - Usuario deshabilitado exitosamente.
```
**1 notificación** (la correcta)

---

## 🧪 Pruebas

### 1. **Deshabilitar Usuario**
- Ir a `/usuarios`
- Hacer clic en botón de deshabilitar
- Confirmar
- ✅ **Resultado**: Solo 1 notificación "Usuario deshabilitado exitosamente"

### 2. **Activar Usuario**
- Ir a `/usuarios`
- Hacer clic en botón de activar
- Confirmar
- ✅ **Resultado**: Solo 1 notificación "Usuario activado exitosamente"

### 3. **Crear Cliente**
- Ir a `/clientes/crear`
- Llenar formulario y guardar
- ✅ **Resultado**: Solo 1 notificación "Cliente creado exitosamente"

### 4. **Editar Cliente**
- Ir a `/clientes/{id}/editar`
- Modificar y guardar
- ✅ **Resultado**: Solo 1 notificación "Cliente actualizado exitosamente"

---

## 📚 Patrón Estándar para Todos los Módulos

Este patrón debe aplicarse en **TODOS** los módulos:

```javascript
// 1. Imports
import { watch } from 'vue'
import { usePage } from '@inertiajs/vue3'

// 2. Variables de tracking
let lastFlashSuccess = null
let lastFlashError = null

// 3. Watcher (al final del script)
const page = usePage()
watch(
  () => page.props.flash,
  (flash) => {
    if (flash?.success && flash.success !== lastFlashSuccess) {
      lastFlashSuccess = flash.success
      window.$notify?.success('Éxito', flash.success)
    }
    if (flash?.error && flash.error !== lastFlashError && flash.error !== '[]') {
      lastFlashError = flash.error
      window.$notify?.error('Error', flash.error)
    }
  },
  { deep: true } // ✅ NO usar immediate: true
)

// 4. En callbacks de router, NO mostrar notificaciones manuales
router.post('/ruta', data, {
  onSuccess: () => {
    // ✅ Solo recargar, el watcher muestra la notificación
    router.reload({ only: ['recurso'] })
  },
  onError: (errors) => {
    // ✅ Solo log, el watcher muestra el error
    console.error('Error:', errors)
  }
})
```

---

## ⚠️ Reglas Importantes

### ✅ **DO** (Hacer):
1. Usar el watcher para mostrar flash messages
2. Trackear último mensaje para evitar duplicados
3. Filtrar errores vacíos `[]`
4. Confiar en el backend para enviar mensajes con `->with('success', '...')`

### ❌ **DON'T** (No Hacer):
1. No usar `immediate: true` en el watcher
2. No mostrar notificaciones manuales cuando hay flash message
3. No mostrar notificaciones tanto en `onSuccess` como en watcher
4. No mostrar errores vacíos `[]`

---

## 🐛 Troubleshooting

### Sigo viendo notificaciones duplicadas

**Verificar**:
1. Que no haya `immediate: true` en el watcher
2. Que no se estén mostrando notificaciones manuales en callbacks
3. Que el backend no esté enviando múltiples flash messages

### No aparecen notificaciones

**Verificar**:
1. Que el watcher esté presente
2. Que `window.$notify` esté disponible
3. Que el backend esté enviando flash messages con `->with('success', '...')`

### Aparecen errores vacíos `[]`

**Verificar**:
1. Que se esté filtrando `flash.error !== '[]'` en el watcher
2. Que Laravel no esté enviando errores vacíos

---

**Fecha**: 2025-10-21
**Versión**: 1.0
**Estado**: ✅ CORREGIDO
