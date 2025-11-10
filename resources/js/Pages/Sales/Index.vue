<template>
  <AdminLayout>
    <div class="p-6">
      <!-- Header -->
      <div class="mb-6 flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Ventas</h1>
          <p class="text-sm text-gray-600 mt-1">Gestión completa de ventas del sistema</p>
        </div>
        <div class="flex gap-3">
          <Link
            v-if="can('sales.create')"
            href="/ventas/crear"
            class="px-4 py-2 bg-primary-700 text-white rounded-md hover:bg-primary-800 transition-colors flex items-center gap-2"
          >
            <Plus class="h-4 w-4" />
            Nueva Venta
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
                <Receipt class="h-8 w-8 text-blue-600" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Total Ventas</p>
                <p class="text-2xl font-semibold text-gray-900">{{ stats?.total_sales || 0 }}</p>
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
                <p class="text-2xl font-semibold text-gray-900">{{ stats?.pending_sales || 0 }}</p>
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
                <p class="text-sm font-medium text-gray-500">Completadas</p>
                <p class="text-2xl font-semibold text-gray-900">{{ stats?.complete_sales || 0 }}</p>
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
                <option value="pending">Pendiente</option>
                <option value="completed">Completada</option>
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

            <!-- Payment Method -->
            <div>
              <Label for="payment_method">Método de Pago</Label>
              <select
                id="payment_method"
                v-model="filters.payment_method"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
              >
                <option value="">Todos los métodos</option>
                <option value="cash">Efectivo</option>
                <option value="credit">Crédito</option>
                <option value="transfer">Transferencia</option>
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

      <!-- Sales Table -->
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
                    Método de Pago
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Estado de Pago
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
                <tr v-for="sale in sales.data" :key="sale.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ sale.code }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ sale.client?.business_name || 'N/A' }}</div>
                    <div class="text-sm text-gray-500">{{ sale.client?.trade_name || '' }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ sale.salesperson?.name || 'N/A' }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <Badge :variant="getStatusVariant(sale.status)">
                      {{ getStatusText(sale.status) }}
                    </Badge>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <Badge :variant="getPaymentMethodVariant(sale.payment_method)">
                      {{ getPaymentMethodText(sale.payment_method) }}
                    </Badge>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <Badge :variant="getPaymentStatusVariant(sale.payment_status)">
                      {{ getPaymentStatusText(sale.payment_status) }}
                    </Badge>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ formatCurrency(sale.total) }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ formatDate(sale.created_at) }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex items-center justify-end gap-2">
                      <!-- Ver -->
                      <Button
                        variant="outline"
                        size="sm"
                        @click="viewSale(sale.id)"
                        title="Ver detalles"
                      >
                        <Eye class="h-4 w-4" />
                      </Button>

                      <!-- Imprimir -->
                      <Button
                        variant="outline"
                        size="sm"
                        @click="printSale(sale.id)"
                        title="Imprimir"
                      >
                        <Printer class="h-4 w-4" />
                      </Button>

                      <!-- Editar (solo si es borrador) -->
                      <Button
                        v-if="sale.status === 'draft' && can('sales.update')"
                        variant="outline"
                        size="sm"
                        @click="editSale(sale.id)"
                        title="Editar"
                      >
                        <Edit class="h-4 w-4" />
                      </Button>


                      <!-- Completar (solo si es borrador) -->
                      <Button
                        v-if="sale.status === 'draft' && can('sales.complete')"
                        variant="outline"
                        size="sm"
                        @click="completeSale(sale.id)"
                        title="Completar"
                        class="text-green-600 hover:text-green-700"
                      >
                        <CheckCircle class="h-4 w-4" />
                      </Button>

                      <!-- Cancelar -->
                      <Button
                        v-if="sale.status !== 'cancelled' && sale.status !== 'canceled' && can('sales.cancel')"
                        variant="outline"
                        size="sm"
                        @click="cancelSale(sale.id)"
                        title="Cancelar"
                        class="text-red-600 hover:text-red-700"
                      >
                        <XCircle class="h-4 w-4" />
                      </Button>

                      <!-- Eliminar (solo borradores) -->
                      <Button
                        v-if="sale.status === 'draft' && can('sales.delete')"
                        variant="outline"
                        size="sm"
                        @click="deleteSale(sale.id)"
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
          <div v-if="sales.data.length === 0" class="text-center py-12">
            <Receipt class="mx-auto h-12 w-12 text-gray-400" />
            <h3 class="mt-2 text-sm font-medium text-gray-900">No hay ventas</h3>
            <p class="mt-1 text-sm text-gray-500">Comienza creando una nueva venta.</p>
            <div class="mt-6">
              <Link
                v-if="can('sales.create')"
                href="/ventas/crear"
                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700"
              >
                <Plus class="h-4 w-4 mr-2" />
                Nueva Venta
              </Link>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Pagination -->
      <div v-if="sales && sales.links && sales.links.length > 0" class="mt-6">
        <Pagination 
          :links="sales.links" 
          :pagination-data="{
            from: sales.from,
            to: sales.to,
            total: sales.total,
            current_page: sales.current_page,
            last_page: sales.last_page
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
  XCircle,
  Trash2,
  Receipt,
  Clock,
  DollarSign,
  AlertCircle
} from 'lucide-vue-next'

const props = defineProps({
  sales: {
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
  payment_method: props.filters.payment_method || '',
  date_from: props.filters.date_from || '',
  date_to: props.filters.date_to || ''
})

// Methods
const applyFilters = () => {
  router.get('/ventas', filters, {
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
    pending: 'secondary',
    completed: 'success',
    complete: 'success',
    cancelled: 'destructive',
    canceled: 'destructive'
  }
  return variants[status] || 'secondary'
}

const getStatusText = (status) => {
  const texts = {
    draft: 'Borrador',
    pending: 'Pendiente',
    completed: 'Completada',
    complete: 'Completada',
    cancelled: 'Cancelada',
    canceled: 'Cancelada'
  }
  return texts[status] || status
}

const getPaymentMethodVariant = (method) => {
  const variants = {
    cash: 'success',
    credit: 'warning',
    transfer: 'primary'
  }
  return variants[method] || 'secondary'
}

const getPaymentMethodText = (method) => {
  const texts = {
    cash: 'Efectivo',
    credit: 'Crédito',
    transfer: 'Transferencia'
  }
  return texts[method] || method
}

const getPaymentStatusVariant = (status) => {
  const variants = {
    paid: 'success',
    pending: 'warning',
    partial: 'secondary'
  }
  return variants[status] || 'secondary'
}

const getPaymentStatusText = (status) => {
  const texts = {
    paid: 'Pagado',
    pending: 'Pendiente',
    partial: 'Pago Parcial'
  }
  return texts[status] || status
}

// Actions
const viewSale = (id) => {
  router.visit(`/ventas/${id}`)
}

const printSale = (id) => {
  window.open(`/ventas/${id}/imprimir`, '_blank')
}

const editSale = (id) => {
  router.visit(`/ventas/${id}/editar`)
}

const completeSale = (id) => {
  showConfirm({
    title: 'Completar Venta',
    message: '¿Estás seguro de que quieres completar esta venta?',
    confirmText: 'Sí, completar',
    onConfirm: () => {
      router.post(`/ventas/${id}/completar`, {}, {
        preserveScroll: true,
        preserveState: false,
        onSuccess: () => {
          router.reload({ only: ['sales'] })
          showAlert({
            type: 'success',
            title: 'Éxito',
            message: 'La venta ha sido completada exitosamente.'
          })
        },
        onError: (errors) => {
          showAlert({
            type: 'error',
            title: 'Error',
            message: errors?.message || 'No se pudo completar la venta. Por favor, intente nuevamente.'
          })
        }
      })
    }
  })
}

const cancelSale = (id) => {
  showConfirm({
    title: 'Cancelar Venta',
    message: '¿Estás seguro de que quieres cancelar esta venta?',
    confirmText: 'Sí, cancelar',
    onConfirm: () => {
      router.post(`/ventas/${id}/cancelar`, {}, {
        preserveScroll: true,
        preserveState: false,
        onSuccess: () => {
          router.reload({ only: ['sales'] })
          showAlert({
            type: 'success',
            title: 'Éxito',
            message: 'La venta ha sido cancelada exitosamente.'
          })
        },
        onError: (errors) => {
          showAlert({
            type: 'error',
            title: 'Error',
            message: errors?.message || 'No se pudo cancelar la venta. Por favor, intente nuevamente.'
          })
        }
      })
    }
  })
}

const deleteSale = (id) => {
  showConfirm({
    title: 'Eliminar Venta',
    message: '¿Estás seguro de que quieres eliminar esta venta? Esta acción no se puede deshacer.',
    confirmText: 'Sí, eliminar',
    onConfirm: () => {
      router.delete(`/ventas/${id}`, {
        preserveScroll: true,
        preserveState: false,
        onSuccess: () => {
          router.reload({ only: ['sales'] })
          showAlert({
            type: 'success',
            title: 'Éxito',
            message: 'La venta ha sido eliminada exitosamente.'
          })
        },
        onError: (errors) => {
          showAlert({
            type: 'error',
            title: 'Error',
            message: errors?.message || 'No se pudo eliminar la venta. Por favor, intente nuevamente.'
          })
        }
      })
    }
  })
}

</script>