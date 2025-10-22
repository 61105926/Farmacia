<template>
  <AdminLayout>
    <!-- Page Header -->
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Usuarios</h1>
          <p class="text-sm text-gray-600 mt-1">Gestión de usuarios del sistema</p>
        </div>
        <div class="flex gap-2">
          <Link
            v-if="can('users.create')"
            href="/usuarios/crear"
            class="px-4 py-2 bg-primary-700 text-white rounded-md hover:bg-primary-800 transition-colors"
          >
            Nuevo Usuario
          </Link>
          <!-- <button
            @click="exportUsers"
            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors"
          >
            Exportar CSV
          </button> -->
        </div>
      </div>
    </template>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-6">
      <Card>
        <CardContent class="p-6">
          <div class="flex items-center">
            <Users class="h-8 w-8 text-blue-500" />
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Total Usuarios</p>
              <p class="text-2xl font-bold">{{ users.total || 0 }}</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardContent class="p-6">
          <div class="flex items-center">
            <UserCheck class="h-8 w-8 text-green-500" />
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Activos</p>
              <p class="text-2xl font-bold">{{ activeUsersCount }}</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardContent class="p-6">
          <div class="flex items-center">
            <UserX class="h-8 w-8 text-red-500" />
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Bloqueados</p>
              <p class="text-2xl font-bold">{{ blockedUsersCount }}</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardContent class="p-6">
          <div class="flex items-center">
            <Shield class="h-8 w-8 text-purple-500" />
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Administradores</p>
              <p class="text-2xl font-bold">{{ adminUsersCount }}</p>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
    <!-- Filters -->
    <Card class="mb-6">
      <CardContent class="p-6">
        <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
            <input
              v-model="filters.search"
              type="text"
              placeholder="Nombre, email..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
            />
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Rol</label>
            <select
              v-model="filters.role"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
              <option value="">Todos los roles</option>
              <option v-for="role in roles" :key="role.id" :value="role.name">
                {{ getRoleDescription(role.name) }}
              </option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
            <select
              v-model="filters.status"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
              <option value="">Todos</option>
              <option value="active">Activos</option>
              <option value="blocked">Bloqueados</option>
            </select>
          </div>
          
          <div class="flex items-end">
            <button
              type="submit"
              class="w-full px-4 py-2 bg-primary-700 text-white rounded-md hover:bg-primary-800 transition-colors"
            >
              Filtrar
            </button>
          </div>
        </form>
      </CardContent>
    </Card>

    <!-- Users Table -->
    <Card>
      <CardContent class="p-0">
        <div v-if="users?.data && Array.isArray(users.data) && users.data.length > 0" class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Usuario
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Rol
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Estado
                </th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Acciones
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <div class="h-10 w-10 rounded-full bg-primary-100 flex items-center justify-center">
                        <span class="text-sm font-medium text-primary-700">
                          {{ user.name.charAt(0).toUpperCase() }}
                        </span>
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                      <div class="text-sm text-gray-500">{{ user.email }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex flex-wrap gap-1">
                    <Badge 
                      v-for="role in user.roles" 
                      :key="role.id"
                      :variant="getRoleVariant(role.name)"
                    >
                      {{ getRoleDescription(role.name) }}
                    </Badge>
                    <span v-if="!user.roles || user.roles.length === 0" class="text-sm text-gray-500">
                      Sin roles
                    </span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div
                      class="w-2 h-2 rounded-full mr-2"
                      :class="user.status === 'blocked' ? 'bg-red-500' : 'bg-green-500'"
                    ></div>
                    <span class="text-sm" :class="user.status === 'blocked' ? 'text-red-600' : 'text-green-600'">
                      {{ user.status === 'blocked' ? 'Bloqueado' : 'Activo' }}
                    </span>
                  </div>
                </td>
            
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end gap-2">
                    <Link
                      :href="`/usuarios/${user.id}`"
                      class="p-2 text-primary-600 hover:text-primary-900 hover:bg-primary-50 rounded-md transition-colors"
                      :title="'Ver detalles de ' + user.name"
                    >
                      <Eye class="h-4 w-4" />
                    </Link>
                    <Link
                      v-if="can('users.update')"
                      :href="`/usuarios/${user.id}/editar`"
                      class="p-2 text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded-md transition-colors"
                      :title="'Editar ' + user.name"
                    >
                      <Edit class="h-4 w-4" />
                    </Link>
                    <button
                      v-if="can('users.update')"
                      @click="toggleUserStatus(user)"
                      :class="getStatusButtonClass(user)"
                      class="p-2 rounded-md transition-colors"
                      :title="getStatusButtonTitle(user)"
                    >
                      <component :is="getStatusIcon(user)" class="h-4 w-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <div v-else class="text-center py-12">
          <Users class="mx-auto h-12 w-12 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900">No hay usuarios</h3>
          <p class="mt-1 text-sm text-gray-500">
            Comienza creando un nuevo usuario.
          </p>
          <div class="mt-6">
            <Link
              href="/usuarios/crear"
              class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-700 hover:bg-primary-800"
            >
              Nuevo Usuario
            </Link>
          </div>
        </div>
      </CardContent>
      
      <!-- Pagination -->
      <div v-if="users?.data && Array.isArray(users.data) && users.data.length > 0" class="px-6 py-3 border-t border-gray-200">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Mostrando {{ users.from }} a {{ users.to }} de {{ users.total }} resultados
          </div>
          <div class="flex items-center space-x-2">
            <Link
              v-for="link in users.links"
              :key="link.label"
              :href="link.url"
              v-html="link.label"
              :class="[
                'px-3 py-2 text-sm rounded-md',
                link.active 
                  ? 'bg-primary-700 text-white' 
                  : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50'
              ]"
            />
          </div>
        </div>
      </div>
    </Card>

    <!-- Alert Dialog -->
    <AlertDialog
      :show="alertState.show"
      :type="alertState.type"
      :title="alertState.title"
      :message="alertState.message"
      :confirm-text="alertState.confirmText"
      @confirm="handleConfirm"
      @close="hideAlert"
    />
  </AdminLayout>
</template>

<script setup>
import { reactive, computed, watch } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Card from '@/Components/ui/Card.vue'
import CardContent from '@/Components/ui/CardContent.vue'
import Badge from '@/Components/ui/Badge.vue'
import { Users, UserCheck, UserX, Shield, Eye, Edit } from 'lucide-vue-next'
import { usePermissions } from '@/composables/usePermissions'
import { useAlert } from '@/composables/useAlert'
import AlertDialog from '@/Components/ui/AlertDialog.vue'

const props = defineProps({
  users: {
    type: Object,
    default: () => ({
      data: [],
      links: [],
      from: 0,
      to: 0,
      total: 0
    })
  },
  roles: {
    type: Array,
    default: () => []
  },
  filters: {
    type: Object,
    default: () => ({})
  }
})

const { can } = usePermissions()
const { alertState, showConfirm, hideAlert, handleConfirm } = useAlert()

const filters = reactive({
  search: props.filters?.search || '',
  role: props.filters?.role || '',
  status: props.filters?.status || '',
})

const formatDate = (date) => {
  if (!date || date === null || date === undefined) return 'Nunca'
  try {
    const dateObj = new Date(date)
    if (isNaN(dateObj.getTime())) return 'Fecha inválida'
    return dateObj.toLocaleDateString('es-BO')
  } catch (e) {
    return 'Fecha inválida'
  }
}

const getRoleDescription = (roleName) => {
  if (!roleName || roleName === null || roleName === undefined) return 'Sin rol'
  
  const descriptions = {
    'super-admin': 'Super Administrador',
    'admin': 'Administrador',
    'vendedor-ventas': 'Vendedor de Ventas',
    'vendedor-preventas': 'Vendedor de Preventas',
    'cobrador': 'Cobrador',
    'almacen': 'Almacén',
    'contador': 'Contador',
  }
  return descriptions[roleName] || roleName || 'Sin rol'
}

const getRoleVariant = (roleName) => {
  if (!roleName || roleName === null || roleName === undefined) return 'secondary'
  
  const variants = {
    'super-admin': 'danger',
    'admin': 'warning',
    'vendedor-ventas': 'success',
    'vendedor-preventas': 'info',
    'cobrador': 'primary',
    'almacen': 'secondary',
    'contador': 'purple',
  }
  return variants[roleName] || 'secondary'
}

const activeUsersCount = computed(() => {
  if (!props.users?.data || !Array.isArray(props.users.data)) return 0
  return props.users.data.filter(user => {
    return user && user.blocked_at === null
  }).length
})

const blockedUsersCount = computed(() => {
  if (!props.users?.data || !Array.isArray(props.users.data)) return 0
  return props.users.data.filter(user => {
    return user && user.blocked_at !== null && user.blocked_at !== undefined
  }).length
})

const adminUsersCount = computed(() => {
  if (!props.users?.data || !Array.isArray(props.users.data)) return 0
  return props.users.data.filter(user => {
    if (!user || !user.roles || !Array.isArray(user.roles)) return false
    return user.roles.some(role => {
      return role && role.name && ['super-admin', 'admin'].includes(role.name)
    })
  }).length
})

const applyFilters = () => {
  router.get('/usuarios', filters, {
    preserveState: true,
    preserveScroll: true,
  })
}

// Removed - now using showConfirm from useAlert

// Helper functions for user status
const isUserActive = (user) => {
  return user.status === 'active'
}

const getStatusButtonClass = (user) => {
  if (isUserActive(user)) {
    return 'text-red-600 hover:text-red-900 hover:bg-red-50'
  } else {
    return 'text-green-600 hover:text-green-900 hover:bg-green-50'
  }
}

const getStatusButtonTitle = (user) => {
  if (isUserActive(user)) {
    return `Deshabilitar ${user.name}`
  } else {
    return `Activar ${user.name}`
  }
}

const getStatusIcon = (user) => {
  if (isUserActive(user)) {
    return UserX
  } else {
    return UserCheck
  }
}

const toggleUserStatus = (user) => {
  const isActive = isUserActive(user)
  const action = isActive ? 'deshabilitar' : 'activar'
  const actionCapitalized = action.charAt(0).toUpperCase() + action.slice(1)

  showConfirm({
    title: `${actionCapitalized} Usuario`,
    message: `¿Estás seguro de ${action} el usuario "${user.name}"?`,
    confirmText: actionCapitalized,
    onConfirm: () => {
      const endpoint = isActive ? 'disable' : 'activate'
      router.post(`/usuarios/${user.id}/${endpoint}`, {}, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
          router.reload({ only: ['users'] })
        },
        onError: (errors) => {
          console.error('Error:', errors)
        }
      })
    }
  })
}

const exportUsers = () => {
  window.open('/usuarios/exportar/csv', '_blank')
}

// Watch for flash messages
const page = usePage()
let lastFlashSuccess = null
let lastFlashError = null

watch(
  () => page.props.flash,
  (flash) => {
    if (flash?.success && flash.success !== lastFlashSuccess && flash.success.trim() !== '') {
      lastFlashSuccess = flash.success
      window.$notify?.success('Éxito', flash.success)
    }

    // Filtrar errores vacíos, arrays vacíos, objetos vacíos, y strings vacíos
    const hasValidError = flash?.error
      && flash.error !== lastFlashError
      && flash.error !== '[]'
      && flash.error !== '{}'
      && typeof flash.error === 'string'
      && flash.error.trim() !== ''

    if (hasValidError) {
      lastFlashError = flash.error
      window.$notify?.error('Error', flash.error)
    }
  },
  { deep: true }
)
</script>