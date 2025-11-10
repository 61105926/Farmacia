<template>
  <AdminLayout>
    <div class="p-6">
      <!-- Header -->
      <div class="mb-6 flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Preventas</h1>
          <p class="text-sm text-gray-600 mt-1">Gestión completa de preventas del sistema</p>
        </div>
        <div class="flex gap-3">
          <Link
            v-if="can('presales.create')"
            href="/preventas/crear"
            class="px-4 py-2 bg-primary-700 text-white rounded-md hover:bg-primary-800 transition-colors flex items-center gap-2"
          >
            <Plus class="h-4 w-4" />
            Nueva Preventa
          </Link>
        </div>
      </div>

      <!-- Error Message -->
      <div v-if="error" class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
        <div class="flex">
          <AlertCircle class="h-5 w-5 text-red-400 flex-shrink-0" />
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">Error</h3>
            <div class="mt-2 text-sm text-red-700">
              <p>{{ error }}</p>
            </div>
            <div class="mt-4">
              <Link
                href="/usuarios"
                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors"
              >
                Ir a Usuarios para Crear Tablas
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- Statistics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <Card>
          <CardContent class="p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <FileText class="h-8 w-8 text-blue-600" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Total Preventas</p>
                <p class="text-2xl font-semibold text-gray-900">{{ stats?.total_presales || 0 }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <Clock class="h-8 w-8 text-yellow-600" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Pendientes</p>
                <p class="text-2xl font-semibold text-gray-900">{{ stats?.pending_presales || 0 }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <CheckCircle class="h-8 w-8 text-green-600" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Confirmadas</p>
                <p class="text-2xl font-semibold text-gray-900">{{ stats?.confirmed_presales || 0 }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <DollarSign class="h-8 w-8 text-purple-600" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Valor Total</p>
                <p class="text-2xl font-semibold text-gray-900">{{ formatCurrency(stats?.total_value || 0) }}</p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Filters -->
      <Card class="mb-6">
        <CardContent class="p-4">
          <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <!-- Search -->
            <div>
              <Label for="search">Buscar</Label>
              <Input
                id="search"
                v-model="filters.search"
                placeholder="Código, cliente..."
                class="mt-1"
              />
            </div>

            <!-- Status -->
            <div>
              <Label for="status">Estado</Label>
              <select
                id="status"
                v-model="filters.status"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
              >
                <option value="">Todos los estados</option>
                <option value="draft">Borrador</option>
                <option value="converted">Convertida</option>
                <option value="cancelled">Cancelada</option>
              </select>
            </div>

            <!-- Salesperson -->
            <div>
              <Label for="salesperson">Vendedor</Label>
              <select
                id="salesperson"
                v-model="filters.salesperson_id"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
              >
                <option value="">Todos los vendedores</option>
                <option v-for="salesperson in salespeople" :key="salesperson.id" :value="salesperson.id">
                  {{ salesperson.name }}
                </option>
              </select>
            </div>

            <!-- Date From -->
            <div>
              <Label for="date_from">Desde</Label>
              <Input
                id="date_from"
                v-model="filters.date_from"
                type="date"
                class="mt-1"
              />
            </div>

            <!-- Date To -->
            <div>
              <Label for="date_to">Hasta</Label>
              <Input
                id="date_to"
                v-model="filters.date_to"
                type="date"
                class="mt-1"
              />
            </div>

            <!-- Actions -->
            <div class="flex items-end gap-2">
              <Button type="submit" class="flex-1">
                <Search class="h-4 w-4 mr-2" />
                Filtrar
              </Button>
              <Button type="button" variant="outline" @click="clearFilters">
                <X class="h-4 w-4" />
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>

      <!-- Presales Table -->
      <Card>
        <CardContent class="p-0">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Código
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Cliente
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Vendedor
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Estado
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Total
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Fecha
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Acciones
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="presale in presales.data" :key="presale.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ presale.code }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ presale.client?.business_name || 'N/A' }}</div>
                    <div class="text-sm text-gray-500">{{ presale.client?.trade_name || '' }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ presale.salesperson?.name || 'N/A' }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <Badge :variant="getStatusVariant(presale.status)">
                      {{ getStatusText(presale.status) }}
                    </Badge>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ formatCurrency(presale.total) }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ formatDate(presale.created_at) }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex items-center justify-end gap-2">
                      <!-- Ver -->
                      <Button
                        variant="outline"
                        size="sm"
                        @click="viewPresale(presale.id)"
                        title="Ver detalles"
                      >
                        <Eye class="h-4 w-4" />
                      </Button>

                      <!-- Imprimir -->
                      <Button
                        variant="outline"
                        size="sm"
                        @click="printPresale(presale.id)"
                        title="Imprimir"
                      >
                        <Printer class="h-4 w-4" />
                      </Button>

                      <!-- Editar (solo si es borrador) -->
                      <Button
                        v-if="presale.status === 'draft' && can('presales.update')"
                        variant="outline"
                        size="sm"
                        @click="editPresale(presale.id)"
                        title="Editar"
                      >
                        <Edit class="h-4 w-4" />
                      </Button>

                      <!-- Aprobar (solo si es borrador) -->
                      <Button
                        v-if="presale.status === 'draft' && can('presales.approve')"
                        variant="outline"
                        size="sm"
                        @click="approvePresale(presale.id)"
                        title="Aprobar"
                        class="text-green-600 hover:text-green-700"
                      >
                        <CheckCircle class="h-4 w-4" />
                      </Button>

                      <!-- Convertir a Venta (solo si está confirmada) -->
                      <Button
                        v-if="presale.status === 'confirmed' && can('presales.convert')"
                        variant="outline"
                        size="sm"
                        @click="convertToSale(presale.id)"
                        title="Convertir a Venta"
                        class="text-blue-600 hover:text-blue-700"
                      >
                        <ShoppingCart class="h-4 w-4" />
                      </Button>

                      <!-- Cancelar -->
                      <Button
                        v-if="presale.status !== 'cancelled' && can('presales.cancel')"
                        variant="outline"
                        size="sm"
                        @click="cancelPresale(presale.id)"
                        title="Cancelar"
                        class="text-red-600 hover:text-red-700"
                      >
                        <XCircle class="h-4 w-4" />
                      </Button>

                      <!-- Eliminar (solo borradores) -->
                      <Button
                        v-if="presale.status === 'draft' && can('presales.delete')"
                        variant="outline"
                        size="sm"
                        @click="deletePresale(presale.id)"
                        title="Eliminar"
                        class="text-red-600 hover:text-red-700"
                      >
                        <Trash2 class="h-4 w-4" />
                      </Button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Empty State -->
          <div v-if="presales.data.length === 0" class="text-center py-12">
            <FileText class="mx-auto h-12 w-12 text-gray-400" />
            <h3 class="mt-2 text-sm font-medium text-gray-900">No hay preventas</h3>
            <p class="mt-1 text-sm text-gray-500">Comienza creando una nueva preventa.</p>
            <div class="mt-6">
              <Link
                v-if="can('presales.create')"
                href="/preventas/crear"
                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700"
              >
                <Plus class="h-4 w-4 mr-2" />
                Nueva Preventa
              </Link>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Pagination -->
      <div v-if="presales && presales.links && presales.links.length > 0" class="mt-6">
        <Pagination 
          :links="presales.links" 
          :pagination-data="{
            from: presales.from,
            to: presales.to,
            total: presales.total,
            current_page: presales.current_page,
            last_page: presales.last_page
          }"
        />
      </div>
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
import { ref, reactive, computed, onMounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Card from '@/Components/ui/Card.vue'
import CardContent from '@/Components/ui/CardContent.vue'
import Button from '@/Components/ui/Button.vue'
import Input from '@/Components/ui/Input.vue'
import Label from '@/Components/ui/Label.vue'
import Badge from '@/Components/ui/Badge.vue'
import Pagination from '@/Components/ui/Pagination.vue'
import AlertDialog from '@/Components/ui/AlertDialog.vue'
import { usePermissions } from '@/composables/usePermissions'
import { useAlert } from '@/composables/useAlert'
import {
  Plus,
  Search,
  X,
  Eye,
  Printer,
  Edit,
  CheckCircle,
  ShoppingCart,
  XCircle,
  Trash2,
  FileText,
  Clock,
  DollarSign,
  AlertCircle
} from 'lucide-vue-next'

const props = defineProps({
  presales: {
    type: Object,
    default: () => ({ data: [], links: null })
  },
  filters: {
    type: Object,
    default: () => ({})
  },
  salespeople: {
    type: Array,
    default: () => []
  },
  stats: {
    type: Object,
    default: () => ({})
  },
  error: {
    type: String,
    default: null
  }
})

const { can } = usePermissions()
const { alertState, showAlert, showConfirm, hideAlert, handleConfirm } = useAlert()

// Filters
const filters = reactive({
  search: props.filters.search || '',
  status: props.filters.status || '',
  salesperson_id: props.filters.salesperson_id || '',
  date_from: props.filters.date_from || '',
  date_to: props.filters.date_to || ''
})

// Methods
const applyFilters = () => {
  router.get('/preventas', filters, {
    preserveState: true,
    preserveScroll: true
  })
}

const clearFilters = () => {
  Object.keys(filters).forEach(key => {
    filters[key] = ''
  })
  applyFilters()
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('es-BO', {
    style: 'currency',
    currency: 'BOB',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(amount || 0)
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('es-BO')
}

const getStatusVariant = (status) => {
  const variants = {
    draft: 'secondary',
    confirmed: 'success',
    converted: 'primary',
    cancelled: 'destructive'
  }
  return variants[status] || 'secondary'
}

const getStatusText = (status) => {
  const texts = {
    draft: 'Borrador',
    confirmed: 'Confirmada',
    converted: 'Convertida',
    cancelled: 'Cancelada'
  }
  return texts[status] || status
}

// Actions
const viewPresale = (id) => {
  router.visit(`/preventas/${id}`)
}

const printPresale = (id) => {
  window.open(`/preventas/${id}/imprimir`, '_blank')
}

const editPresale = (id) => {
  router.visit(`/preventas/${id}/editar`)
}

const approvePresale = (id) => {
  showConfirm(
    'Aprobar Preventa',
    '¿Estás seguro de que quieres aprobar esta preventa?',
    'Aprobar',
    () => {
      router.post(`/preventas/${id}/aprobar`, {}, {
        onSuccess: () => {
          showAlert('success', 'Preventa aprobada', 'La preventa ha sido aprobada exitosamente.')
        },
        onError: (errors) => {
          showAlert('error', 'Error', 'No se pudo aprobar la preventa.')
        }
      })
    }
  )
}

const convertToSale = (id) => {
  showConfirm(
    'Convertir a Venta',
    '¿Estás seguro de que quieres convertir esta preventa en una venta?',
    'Convertir',
    () => {
      router.post(`/preventas/${id}/convertir`, {}, {
        onSuccess: () => {
          showAlert('success', 'Preventa convertida', 'La preventa ha sido convertida en venta exitosamente.')
        },
        onError: (errors) => {
          showAlert('error', 'Error', 'No se pudo convertir la preventa.')
        }
      })
    }
  )
}

const cancelPresale = (id) => {
  showConfirm(
    'Cancelar Preventa',
    '¿Estás seguro de que quieres cancelar esta preventa?',
    'Cancelar',
    () => {
      router.post(`/preventas/${id}/rechazar`, {}, {
        onSuccess: () => {
          showAlert('success', 'Preventa cancelada', 'La preventa ha sido cancelada exitosamente.')
        },
        onError: (errors) => {
          showAlert('error', 'Error', 'No se pudo cancelar la preventa.')
        }
      })
    }
  )
}

const deletePresale = (id) => {
  showConfirm(
    'Eliminar Preventa',
    '¿Estás seguro de que quieres eliminar esta preventa? Esta acción no se puede deshacer.',
    'Eliminar',
    () => {
      router.delete(`/preventas/${id}`, {
        onSuccess: () => {
          showAlert('success', 'Preventa eliminada', 'La preventa ha sido eliminada exitosamente.')
        },
        onError: (errors) => {
          showAlert('error', 'Error', 'No se pudo eliminar la preventa.')
        }
      })
    }
  )
}
</script>