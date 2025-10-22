<template>
  <AdminLayout>
    <!-- Page Header -->
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Clientes</h1>
          <p class="text-sm text-gray-600 mt-1">Gestión de clientes y farmacias</p>
        </div>
        <div class="flex gap-2">
          <Link
            v-if="can('clients.create')"
            href="/clientes/crear"
            class="px-4 py-2 bg-primary-700 text-white rounded-md hover:bg-primary-800 transition-colors"
          >
            Nuevo Cliente
          </Link>
          <button
            @click="exportClients"
            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors"
          >
            Exportar CSV
          </button>
        </div>
      </div>
    </template>

    <!-- Filters -->
    <Card class="mb-6">
      <CardContent class="p-6">
        <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-5 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
            <input
              v-model="filters.search"
              type="text"
              placeholder="Nombre, RUC, email..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
            />
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
            <select
              v-model="filters.status"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
              <option value="">Todos</option>
              <option value="active">Activos</option>
              <option value="inactive">Inactivos</option>
              <option value="blocked">Bloqueados</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
            <select
              v-model="filters.client_type"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
              <option value="">Todos</option>
              <option value="individual">Individual</option>
              <option value="company">Empresa</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Vendedor</label>
            <select
              v-model="filters.salesperson_id"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
              <option value="">Todos</option>
              <option v-for="salesperson in salespeople" :key="salesperson.id" :value="salesperson.id">
                {{ salesperson.name }}
              </option>
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

    <!-- Clients Table -->
    <Card>
      <CardContent class="p-0">
        <div v-if="clients.data && clients.data.length > 0" class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Cliente
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Tipo
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Estado
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Vendedor
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Crédito
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actividad
                </th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Acciones
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="client in clients.data" :key="client.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                        <Building2 class="h-5 w-5 text-blue-600" />
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ client.business_name || client.trade_name }}</div>
                      <div class="text-sm text-gray-500">{{ client.email || 'Sin email' }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <Badge :variant="getClientTypeVariant(client.client_type)">
                    {{ getClientTypeDescription(client.client_type) }}
                  </Badge>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div 
                      class="w-2 h-2 rounded-full mr-2"
                      :class="getStatusColor(client.status)"
                    ></div>
                    <span class="text-sm" :class="getStatusTextColor(client.status)">
                      {{ getStatusDescription(client.status) }}
                    </span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ client.salesperson?.name || 'Sin asignar' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <div class="flex flex-col">
                    <span class="font-medium">{{ formatCurrency(client.credit_limit || 0) }}</span>
                    <span class="text-xs text-gray-400">Límite</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex space-x-2">
                    <Link
                      :href="`/clientes/${client.id}/statistics`"
                      class="text-blue-600 hover:text-blue-900 text-sm"
                    >
                      Estadísticas
                    </Link>
                    <Link
                      :href="`/clientes/${client.id}/credit-history`"
                      class="text-green-600 hover:text-green-900 text-sm"
                    >
                      Crédito
                    </Link>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end gap-2">
                    <Link
                      :href="`/clientes/${client.id}`"
                      class="p-2 text-primary-600 hover:text-primary-900 hover:bg-primary-50 rounded-md transition-colors"
                      :title="'Ver detalles de ' + (client.business_name || client.trade_name)"
                    >
                      <Eye class="h-4 w-4" />
                    </Link>
                    <Link
                      v-if="can('clients.update')"
                      :href="`/clientes/${client.id}/editar`"
                      class="p-2 text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded-md transition-colors"
                      :title="'Editar ' + (client.business_name || client.trade_name)"
                    >
                      <Edit class="h-4 w-4" />
                    </Link>
                    <button
                      v-if="can('clients.update')"
                      @click="toggleStatus(client)"
                      class="p-2 text-yellow-600 hover:text-yellow-900 hover:bg-yellow-50 rounded-md transition-colors"
                      :title="client.status === 'active' ? 'Bloquear cliente' : 'Activar cliente'"
                    >
                      <Shield v-if="client.status === 'active'" class="h-4 w-4" />
                      <ShieldOff v-else class="h-4 w-4" />
                    </button>
                    <button
                      v-if="can('clients.delete')"
                      @click="disableClient(client)"
                      class="p-2 text-red-600 hover:text-red-900 hover:bg-red-50 rounded-md transition-colors"
                      :title="'Deshabilitar ' + (client.business_name || client.trade_name)"
                    >
                      <UserX class="h-4 w-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <div v-else class="text-center py-12">
          <Building2 class="mx-auto h-12 w-12 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900">No hay clientes</h3>
          <p class="mt-1 text-sm text-gray-500">
            Comienza creando un nuevo cliente.
          </p>
          <div class="mt-6">
            <Link
              href="/clientes/crear"
              class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-700 hover:bg-primary-800"
            >
              Nuevo Cliente
            </Link>
          </div>
        </div>
      </CardContent>
      
      <!-- Pagination -->
      <div v-if="clients.data && clients.data.length > 0" class="px-6 py-3 border-t border-gray-200">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Mostrando {{ clients.from }} a {{ clients.to }} de {{ clients.total }} resultados
          </div>
          <div class="flex items-center space-x-2">
            <Link
              v-for="link in clients.links"
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

    <!-- Client Statistics Summary -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-6">
      <Card>
        <CardContent class="p-6">
          <div class="flex items-center">
            <Building2 class="h-8 w-8 text-blue-500" />
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Total Clientes</p>
              <p class="text-2xl font-bold">{{ clients.total || 0 }}</p>
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
              <p class="text-2xl font-bold">{{ activeClientsCount }}</p>
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
              <p class="text-2xl font-bold">{{ blockedClientsCount }}</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardContent class="p-6">
          <div class="flex items-center">
            <CreditCard class="h-8 w-8 text-purple-500" />
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Con Crédito</p>
              <p class="text-2xl font-bold">{{ clientsWithCreditCount }}</p>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
    
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
import { ref, reactive, computed, watch } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Card from '@/Components/ui/Card.vue'
import CardContent from '@/Components/ui/CardContent.vue'
import Badge from '@/Components/ui/Badge.vue'
import { Building2, UserCheck, UserX, CreditCard, Eye, Edit, Shield, ShieldOff } from 'lucide-vue-next'
import { usePermissions } from '@/composables/usePermissions'
import { useAlert } from '@/composables/useAlert'
import AlertDialog from '@/Components/ui/AlertDialog.vue'

const props = defineProps({
  clients: {
    type: Object,
    default: () => ({
      data: [],
      links: [],
      from: 0,
      to: 0,
      total: 0
    })
  },
  salespeople: {
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
  status: props.filters?.status || '',
  client_type: props.filters?.client_type || '',
  salesperson_id: props.filters?.salesperson_id || '',
})

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('es-BO', {
    style: 'currency',
    currency: 'BOB'
  }).format(amount)
}

const getClientTypeDescription = (type) => {
  const types = {
    'individual': 'Individual',
    'company': 'Empresa',
  }
  return types[type] || 'Sin tipo'
}

const getClientTypeVariant = (type) => {
  const variants = {
    'individual': 'info',
    'company': 'primary',
  }
  return variants[type] || 'secondary'
}

const getStatusDescription = (status) => {
  const statuses = {
    'active': 'Activo',
    'inactive': 'Inactivo',
    'blocked': 'Bloqueado',
  }
  return statuses[status] || 'Sin estado'
}

const getStatusColor = (status) => {
  const colors = {
    'active': 'bg-green-500',
    'inactive': 'bg-gray-500',
    'blocked': 'bg-red-500',
  }
  return colors[status] || 'bg-gray-500'
}

const getStatusTextColor = (status) => {
  const colors = {
    'active': 'text-green-600',
    'inactive': 'text-gray-600',
    'blocked': 'text-red-600',
  }
  return colors[status] || 'text-gray-600'
}

const activeClientsCount = computed(() => {
  if (!props.clients?.data || !Array.isArray(props.clients.data)) return 0
  return props.clients.data.filter(client => client.status === 'active').length
})

const blockedClientsCount = computed(() => {
  if (!props.clients?.data || !Array.isArray(props.clients.data)) return 0
  return props.clients.data.filter(client => client.status === 'blocked').length
})

const clientsWithCreditCount = computed(() => {
  if (!props.clients?.data || !Array.isArray(props.clients.data)) return 0
  return props.clients.data.filter(client => (client.credit_limit || 0) > 0).length
})

const applyFilters = () => {
  router.get('/clientes', filters, {
    preserveState: true,
    preserveScroll: true,
  })
}

const toggleStatus = (client) => {
  const action = client.status === 'active' ? 'bloquear' : 'activar'
  showConfirm({
    title: `${action.charAt(0).toUpperCase() + action.slice(1)} Cliente`,
    message: `¿Estás seguro de ${action} el cliente "${client.business_name || client.trade_name}"?`,
    confirmText: action.charAt(0).toUpperCase() + action.slice(1),
    onConfirm: () => {
      router.post(`/clientes/${client.id}/toggle-status`, {}, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
          router.reload({ only: ['clients'] })
        }
      })
    }
  })
}

const disableClient = (client) => {
  showConfirm({
    title: 'Deshabilitar Cliente',
    message: `¿Estás seguro de deshabilitar el cliente "${client.business_name || client.trade_name}"? Esta acción no se puede deshacer.`,
    confirmText: 'Deshabilitar',
    onConfirm: () => {
      router.post(`/clientes/${client.id}/disable`, {}, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
          router.reload({ only: ['clients'] })
        }
      })
    }
  })
}

const exportClients = () => {
  window.open('/clientes/exportar/csv', '_blank')
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