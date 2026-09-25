<template>
  <AdminLayout>
    <div class="p-6">
      <!-- Header -->
      <div class="mb-6 flex items-start justify-between">
        <div>
          <Link href="/usuarios" class="text-sm text-primary-700 hover:text-primary-800 mb-2 inline-block">
            ← Volver a usuarios
          </Link>
          <div class="flex items-center gap-3">
            <div class="h-16 w-16 rounded-full bg-primary-100 flex items-center justify-center">
              <span class="text-primary-700 font-bold text-2xl">
                {{ user.name.charAt(0).toUpperCase() }}
              </span>
            </div>
            <div>
              <h1 class="text-2xl font-bold text-gray-900">{{ user.name }}</h1>
              <p class="text-sm text-gray-600">{{ user.email }}</p>
              <div class="flex items-center gap-2 mt-2">
                <span
                  class="px-2 py-1 text-xs font-medium rounded-full"
                  :class="{
                    'bg-green-100 text-green-800': user.status === 'active',
                    'bg-gray-100 text-gray-800': user.status === 'inactive',
                    'bg-red-100 text-red-800': user.status === 'blocked',
                  }"
                >
                  {{ statusLabels[user.status] }}
                </span>
                <span
                  v-for="role in user.roles"
                  :key="role.id"
                  class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full"
                >
                  {{ roleLabel(role.name) }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <div class="flex gap-2">
          <Link
            v-if="can('users.update')"
            :href="`/usuarios/${user.id}/editar`"
            class="px-4 py-2 bg-primary-700 text-white rounded-md hover:bg-primary-800 transition-colors"
          >
            Editar
          </Link>
          <button
            v-if="can('users.delete') && user.id !== $page.props.auth.user.id"
            @click="deleteUser"
            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors"
          >
            Eliminar
          </button>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <Card>
          <CardContent class="p-4">
            <p class="text-sm text-gray-600">Último Acceso</p>
            <p class="text-lg font-semibold text-gray-900">
              {{ lastLogin ? formatDate(lastLogin) : 'Nunca' }}
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-4">
            <p class="text-sm text-gray-600">Intentos Fallidos</p>
            <p class="text-2xl font-bold" :class="user.failed_login_attempts > 0 ? 'text-red-600' : 'text-gray-900'">
              {{ user.failed_login_attempts || 0 }}
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-4">
            <p class="text-sm text-gray-600">Cuenta Creada</p>
            <p class="text-sm font-medium text-gray-900">
              {{ formatDate(user.created_at) }}
            </p>
          </CardContent>
        </Card>
      </div>

      <!-- Tabs -->
      <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex space-x-8">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            class="py-4 px-1 border-b-2 font-medium text-sm transition-colors"
            :class="{
              'border-primary-700 text-primary-700': activeTab === tab.id,
              'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== tab.id,
            }"
          >
            {{ tab.label }}
          </button>
        </nav>
      </div>

      <!-- Tab Content -->
      <div>
        <!-- General Info Tab -->
        <div v-show="activeTab === 'general'">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <Card>
              <CardHeader>
                <CardTitle>Información Personal</CardTitle>
              </CardHeader>
              <CardContent class="space-y-3">
                <div>
                  <p class="text-sm text-gray-600">Nombre Completo</p>
                  <p class="font-medium">{{ user.name }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-600">Email</p>
                  <p class="font-medium">{{ user.email }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-600">Teléfono</p>
                  <p class="font-medium">{{ user.phone || 'No registrado' }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-600">Documento de Identidad</p>
                  <p class="font-medium">{{ user.document_number || 'No registrado' }}</p>
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Roles y Permisos</CardTitle>
              </CardHeader>
              <CardContent class="space-y-3">
                <div>
                  <p class="text-sm text-gray-600 mb-2">Roles Asignados</p>
                  <div class="flex flex-wrap gap-2">
                    <span
                      v-for="role in user.roles"
                      :key="role.id"
                      class="px-3 py-1 text-sm font-medium bg-blue-100 text-blue-800 rounded-full"
                    >
                      {{ roleLabel(role.name) }}
                    </span>
                    <span v-if="user.roles.length === 0" class="text-gray-500">Sin roles asignados</span>
                  </div>
                </div>
                <div v-if="user.permissions && user.permissions.length > 0">
                  <p class="text-sm text-gray-600 mb-2">Permisos Directos</p>
                  <div class="max-h-40 overflow-y-auto">
                    <div class="flex flex-wrap gap-1">
                      <span
                        v-for="permission in user.permissions"
                        :key="permission.id"
                        class="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded"
                      >
                        {{ permission.name }}
                      </span>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>

        <!-- Security Tab -->
        <div v-show="activeTab === 'security'">
          <Card class="max-w-xl">
            <CardHeader>
              <CardTitle>Cambiar Contraseña</CardTitle>
            </CardHeader>
            <CardContent>
              <form v-if="can('users.update')" @submit.prevent="changePassword" class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Nueva contraseña</label>
                  <input
                    v-model="passwordForm.password"
                    type="password"
                    autocomplete="new-password"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                    :class="{ 'border-red-500': passwordForm.errors.password }"
                  />
                  <p v-if="passwordForm.errors.password" class="text-sm text-red-600 mt-1">{{ passwordForm.errors.password }}</p>
                  <p v-else class="text-xs text-gray-500 mt-1">Mínimo 8 caracteres</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar contraseña</label>
                  <input
                    v-model="passwordForm.password_confirmation"
                    type="password"
                    autocomplete="new-password"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                  />
                </div>
                <button
                  type="submit"
                  :disabled="passwordForm.processing"
                  class="w-full px-4 py-2 bg-primary-700 text-white rounded-md hover:bg-primary-800 disabled:opacity-50 transition-colors"
                >
                  {{ passwordForm.processing ? 'Guardando...' : 'Cambiar Contraseña' }}
                </button>
              </form>
              <p v-else class="text-sm text-gray-500">No tienes permiso para cambiar la contraseña de este usuario.</p>
            </CardContent>
          </Card>
        </div>

        <!-- Activity Tab -->
        <div v-show="activeTab === 'activity'">
          <Card>
            <CardHeader>
              <CardTitle>Actividad Reciente</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="space-y-4">
                <div class="flex items-start gap-3">
                  <div class="w-2 h-2 bg-primary-700 rounded-full mt-2"></div>
                  <div>
                    <p class="text-sm font-medium">Usuario creado</p>
                    <p class="text-xs text-gray-500">{{ formatDateTime(user.created_at) }}</p>
                  </div>
                </div>
                <div v-if="user.updated_at !== user.created_at" class="flex items-start gap-3">
                  <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                  <div>
                    <p class="text-sm font-medium">Última actualización</p>
                    <p class="text-xs text-gray-500">{{ formatDateTime(user.updated_at) }}</p>
                  </div>
                </div>
                <div v-if="lastLogin" class="flex items-start gap-3">
                  <div class="w-2 h-2 bg-green-500 rounded-full mt-2"></div>
                  <div>
                    <p class="text-sm font-medium">Último inicio de sesión</p>
                    <p class="text-xs text-gray-500">{{ formatDateTime(lastLogin) }}</p>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { roleLabel } from '@/utils/roles'
import { ref, computed } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardHeader, CardTitle, CardContent } from '@/Components/ui'
import { usePermissions } from '@/composables/usePermissions'
import { useAlert } from '@/composables/useAlert'


const { can } = usePermissions()
const { showAlert } = useAlert()

const props = defineProps({
  user: Object,
})

const activeTab = ref('general')

const tabs = [
  { id: 'general', label: 'Información General' },
  { id: 'security', label: 'Seguridad' },
  { id: 'activity', label: 'Actividad' },
]

const statusLabels = {
  active: 'Activo',
  inactive: 'Inactivo',
  blocked: 'Bloqueado',
}

// Convierte la fecha del servidor; devuelve null si falta o no es válida
const parseDate = (date) => {
  if (!date) return null
  const parsed = date instanceof Date ? date : new Date(String(date).replace(' ', 'T'))
  return isNaN(parsed) ? null : parsed
}

const lastLogin = computed(() => parseDate(props.user.last_login_at) ?? parseDate(props.user.ultimo_acceso))

const formatDate = (date) => {
  const parsed = parseDate(date)
  if (!parsed) return 'Nunca'
  return parsed.toLocaleDateString('es-CO', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

const formatDateTime = (date) => {
  const parsed = parseDate(date)
  if (!parsed) return 'Nunca'
  return parsed.toLocaleDateString('es-CO', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const deleteUser = () => {
  if (confirm(`¿Está seguro de eliminar el usuario ${props.user.name}? Esta acción no se puede deshacer.`)) {
    router.delete(`/usuarios/${props.user.id}`)
  }
}

const passwordForm = useForm({
  password: '',
  password_confirmation: '',
})

const changePassword = () => {
  passwordForm.post(`/usuarios/${props.user.id}/reset-password`, {
    preserveScroll: true,
    onSuccess: () => {
      passwordForm.reset()
      showAlert({ type: 'success', title: 'Contraseña actualizada', message: 'La contraseña del usuario se cambió correctamente' })
    },
  })
}
</script>
