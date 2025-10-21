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
                  {{ role.name }}
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
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <Card>
          <CardContent class="p-4">
            <p class="text-sm text-gray-600">Último Acceso</p>
            <p class="text-lg font-semibold text-gray-900">
              {{ user.last_login_at ? formatDate(user.last_login_at) : 'Nunca' }}
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
            <p class="text-sm text-gray-600">Clientes Asignados</p>
            <p class="text-2xl font-bold text-primary-700">
              {{ user.assigned_clients_count || 0 }}
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
                <div>
                  <p class="text-sm text-gray-600">Sucursal</p>
                  <p class="font-medium">{{ user.branch?.name || 'Sin asignar' }}</p>
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
                      {{ role.name }}
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
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <Card>
              <CardHeader>
                <CardTitle>Información de Acceso</CardTitle>
              </CardHeader>
              <CardContent class="space-y-3">
                <div>
                  <p class="text-sm text-gray-600">Último Acceso</p>
                  <p class="font-medium">{{ user.last_login_at ? formatDateTime(user.last_login_at) : 'Nunca' }}</p>
                </div>
                <div v-if="user.last_login_ip">
                  <p class="text-sm text-gray-600">IP del Último Acceso</p>
                  <p class="font-medium">{{ user.last_login_ip }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-600">Intentos Fallidos de Inicio de Sesión</p>
                  <p class="font-medium" :class="user.failed_login_attempts > 0 ? 'text-red-600' : ''">
                    {{ user.failed_login_attempts || 0 }}
                  </p>
                </div>
                <div v-if="user.blocked_at">
                  <p class="text-sm text-gray-600">Bloqueado Desde</p>
                  <p class="font-medium text-red-600">{{ formatDateTime(user.blocked_at) }}</p>
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Acciones de Seguridad</CardTitle>
              </CardHeader>
              <CardContent class="space-y-3">
                <button
                  v-if="can('users.update')"
                  @click="resetPassword"
                  class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
                >
                  Restablecer Contraseña
                </button>
                <button
                  v-if="can('users.update') && user.status === 'blocked'"
                  @click="unblockUser"
                  class="w-full px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700 transition-colors"
                >
                  Desbloquear Usuario
                </button>
                <button
                  v-if="can('users.update') && user.failed_login_attempts > 0"
                  @click="clearFailedAttempts"
                  class="w-full px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors"
                >
                  Limpiar Intentos Fallidos
                </button>
              </CardContent>
            </Card>
          </div>
        </div>

        <!-- Assigned Clients Tab -->
        <div v-show="activeTab === 'clients'">
          <Card>
            <CardHeader>
              <CardTitle>Clientes Asignados</CardTitle>
            </CardHeader>
            <CardContent>
              <div v-if="user.assigned_clients && user.assigned_clients.length > 0" class="space-y-3">
                <div
                  v-for="client in user.assigned_clients"
                  :key="client.id"
                  class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 flex items-center justify-between"
                >
                  <div>
                    <Link
                      :href="`/clientes/${client.id}`"
                      class="font-medium text-gray-900 hover:text-primary-700"
                    >
                      {{ client.business_name }}
                    </Link>
                    <p class="text-sm text-gray-600">{{ client.trade_name || 'Sin nombre comercial' }}</p>
                    <div class="flex items-center gap-2 mt-1">
                      <span class="text-xs text-gray-500">{{ client.city || 'Sin ciudad' }}</span>
                      <span class="text-xs px-2 py-0.5 bg-primary-100 text-primary-800 rounded">
                        Categoría {{ client.category }}
                      </span>
                    </div>
                  </div>
                  <Link
                    :href="`/clientes/${client.id}`"
                    class="text-primary-700 hover:text-primary-900 text-sm"
                  >
                    Ver detalles →
                  </Link>
                </div>
              </div>
              <p v-else class="text-gray-500 text-center py-8">
                No hay clientes asignados a este usuario
              </p>
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
                <div v-if="user.last_login_at" class="flex items-start gap-3">
                  <div class="w-2 h-2 bg-green-500 rounded-full mt-2"></div>
                  <div>
                    <p class="text-sm font-medium">Último inicio de sesión</p>
                    <p class="text-xs text-gray-500">{{ formatDateTime(user.last_login_at) }}</p>
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
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardHeader, CardTitle, CardContent } from '@/Components/ui'
import { usePermissions } from '@/Composables/usePermissions'


const { can } = usePermissions()

const props = defineProps({
  user: Object,
})

const activeTab = ref('general')

const tabs = [
  { id: 'general', label: 'Información General' },
  { id: 'security', label: 'Seguridad' },
  { id: 'clients', label: 'Clientes Asignados' },
  { id: 'activity', label: 'Actividad' },
]

const statusLabels = {
  active: 'Activo',
  inactive: 'Inactivo',
  blocked: 'Bloqueado',
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('es-CO', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

const formatDateTime = (date) => {
  return new Date(date).toLocaleDateString('es-CO', {
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

const resetPassword = () => {
  if (confirm('¿Está seguro de restablecer la contraseña de este usuario? Se enviará un email con las instrucciones.')) {
    router.post(`/usuarios/${props.user.id}/restablecer-password`)
  }
}

const unblockUser = () => {
  if (confirm('¿Está seguro de desbloquear este usuario?')) {
    router.post(`/usuarios/${props.user.id}/desbloquear`)
  }
}

const clearFailedAttempts = () => {
  if (confirm('¿Está seguro de limpiar los intentos fallidos de inicio de sesión?')) {
    router.post(`/usuarios/${props.user.id}/desbloquear`)
  }
}
</script>
