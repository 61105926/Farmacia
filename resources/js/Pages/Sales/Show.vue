<template>
  <AdminLayout>
    <div class="p-6">
      <!-- Header -->
      <div class="mb-6 flex items-center justify-between">
        <div class="flex items-center gap-4">
          <Link
            href="/ventas"
            class="p-2 text-gray-400 hover:text-gray-600 transition-colors"
            title="Volver a ventas"
          >
            <ArrowLeft class="h-5 w-5" />
          </Link>
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Detalle de Venta</h1>
            <p class="text-sm text-gray-600 mt-1">{{ sale.code }}</p>
          </div>
        </div>
        <div class="flex gap-3">
          <Button
            variant="outline"
            @click="printSale"
            class="flex items-center gap-2"
          >
            <Printer class="h-4 w-4" />
            Imprimir
          </Button>
          <Button
            v-if="(sale.status === 'pending' || sale.status === 'draft') && can('sales.update')"
            variant="outline"
            @click="editSale"
            class="flex items-center gap-2"
          >
            <Edit class="h-4 w-4" />
            Editar
          </Button>
        </div>
      </div>

      <!-- Status Banner -->
      <div class="mb-6">
        <div :class="getStatusBannerClass(sale.status)" class="rounded-lg p-4">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <component :is="getStatusIcon(sale.status)" class="h-6 w-6" />
            </div>
            <div class="ml-3">
              <h3 class="text-lg font-medium">
                {{ getStatusTitle(sale.status) }}
              </h3>
              <p class="text-sm opacity-90">
                {{ getStatusDescription(sale.status) }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Client Information -->
          <Card>
            <CardContent class="p-6">
              <div class="flex items-center gap-3 mb-4">
                <User class="h-5 w-5 text-gray-600" />
                <h2 class="text-lg font-semibold text-gray-900">Información del Cliente</h2>
              </div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="text-sm font-medium text-gray-500">Razón Social</label>
                  <p class="text-sm text-gray-900">{{ sale.client?.business_name || 'N/A' }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-500">Nombre Comercial</label>
                  <p class="text-sm text-gray-900">{{ sale.client?.trade_name || 'N/A' }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-500">Email</label>
                  <p class="text-sm text-gray-900">{{ sale.client?.email || 'N/A' }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-500">Teléfono</label>
                  <p class="text-sm text-gray-900">{{ sale.client?.phone || 'N/A' }}</p>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Products -->
          <Card>
            <CardContent class="p-6">
              <div class="flex items-center gap-3 mb-4">
                <Package class="h-5 w-5 text-gray-600" />
                <h2 class="text-lg font-semibold text-gray-900">Productos Vendidos</h2>
              </div>
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                    <tr>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Producto
                      </th>
                      <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Cantidad
                      </th>
                      <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Precio Unit.
                      </th>
                      <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Descuento
                      </th>
                      <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Total
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="item in sale.items" :key="item.id">
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ item.product?.name || 'N/A' }}</div>
                        <div class="text-sm text-gray-500">{{ item.product?.code || 'N/A' }}</div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-center">
                        <div class="text-sm text-gray-900">{{ item.quantity }}</div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-right">
                        <div class="text-sm text-gray-900">{{ formatCurrency(item.unit_price) }}</div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-center">
                        <div class="text-sm text-gray-900">{{ item.discount || 0 }}%</div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-right">
                        <div class="text-sm font-medium text-gray-900">{{ formatCurrency(item.total) }}</div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </CardContent>
          </Card>

          <!-- Notes -->
          <Card v-if="sale.notes">
            <CardContent class="p-6">
              <div class="flex items-center gap-3 mb-4">
                <FileText class="h-5 w-5 text-gray-600" />
                <h2 class="text-lg font-semibold text-gray-900">Observaciones</h2>
              </div>
              <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ sale.notes }}</p>
            </CardContent>
          </Card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Document Details -->
          <Card>
            <CardContent class="p-6">
              <div class="flex items-center gap-3 mb-4">
                <Receipt class="h-5 w-5 text-gray-600" />
                <h2 class="text-lg font-semibold text-gray-900">Detalles del Documento</h2>
              </div>
              <div class="space-y-3">
                <div>
                  <label class="text-sm font-medium text-gray-500">Código</label>
                  <p class="text-sm text-gray-900">{{ sale.code }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-500">Fecha de Creación</label>
                  <p class="text-sm text-gray-900">{{ formatDate(sale.created_at) }}</p>
                </div>
                <div v-if="sale.complete_at">
                  <label class="text-sm font-medium text-gray-500">Fecha de Completado</label>
                  <p class="text-sm text-gray-900">{{ formatDate(sale.complete_at) }}</p>
                </div>
                <div v-if="sale.canceled_at">
                  <label class="text-sm font-medium text-gray-500">Fecha de Cancelación</label>
                  <p class="text-sm text-gray-900">{{ formatDate(sale.canceled_at) }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-500">Vendedor</label>
                  <p class="text-sm text-gray-900">{{ sale.salesperson?.name || 'N/A' }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-500">Método de Pago</label>
                  <Badge :variant="getPaymentMethodVariant(sale.payment_method)">
                    {{ getPaymentMethodText(sale.payment_method) }}
                  </Badge>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-500">Estado de Pago</label>
                  <Badge :variant="getPaymentStatusVariant(sale.payment_status)">
                    {{ getPaymentStatusText(sale.payment_status) }}
                  </Badge>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Totals -->
          <Card>
            <CardContent class="p-6">
              <div class="flex items-center gap-3 mb-4">
                <Calculator class="h-5 w-5 text-gray-600" />
                <h2 class="text-lg font-semibold text-gray-900">Totales</h2>
              </div>
              <div class="space-y-3">
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Subtotal:</span>
                  <span class="font-medium">{{ formatCurrency(sale.subtotal) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Descuento Total:</span>
                  <span class="font-medium text-red-600">-{{ formatCurrency(sale.total_discount) }}</span>
                </div>
                <div class="border-t border-gray-200 pt-3">
                  <div class="flex justify-between text-lg font-bold">
                    <span>TOTAL:</span>
                    <span>{{ formatCurrency(sale.total) }}</span>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Actions -->
          <Card>
            <CardContent class="p-6">
              <div class="flex items-center gap-3 mb-4">
                <Settings class="h-5 w-5 text-gray-600" />
                <h2 class="text-lg font-semibold text-gray-900">Acciones</h2>
              </div>
              <div class="space-y-3">

                <!-- Complete Sale -->
                <Button
                  v-if="(sale.status === 'pending' || sale.status === 'draft') && can('sales.complete')"
                  @click="completeSale"
                  class="w-full flex items-center justify-center gap-2 text-green-600 hover:text-green-700"
                  variant="outline"
                >
                  <CheckCircle class="h-4 w-4" />
                  Completar Venta
                </Button>

                <!-- Cancel Sale -->
                <Button
                  v-if="sale.status !== 'cancelled' && sale.status !== 'canceled' && can('sales.cancel')"
                  @click="cancelSale"
                  class="w-full flex items-center justify-center gap-2 text-red-600 hover:text-red-700"
                  variant="outline"
                >
                  <XCircle class="h-4 w-4" />
                  Cancelar Venta
                </Button>

                <!-- Delete Sale -->
                <Button
                  v-if="(sale.status === 'pending' || sale.status === 'draft') && can('sales.delete')"
                  @click="deleteSale"
                  class="w-full flex items-center justify-center gap-2 text-red-600 hover:text-red-700"
                  variant="outline"
                >
                  <Trash2 class="h-4 w-4" />
                  Eliminar Venta
                </Button>

                <!-- Generate Invoice -->
                <Button
                  v-if="canGenerateInvoice && can('sales.invoice')"
                  @click="generateInvoice"
                  class="w-full flex items-center justify-center gap-2"
                  variant="outline"
                >
                  <FileText class="h-4 w-4" />
                  Generar Factura
                </Button>
              </div>
            </CardContent>
          </Card>
        </div>
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
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Card from '@/Components/ui/Card.vue'
import CardContent from '@/Components/ui/CardContent.vue'
import Button from '@/Components/ui/Button.vue'
import Badge from '@/Components/ui/Badge.vue'
import AlertDialog from '@/Components/ui/AlertDialog.vue'
import { usePermissions } from '@/composables/usePermissions'
import { useAlert } from '@/composables/useAlert'
import {
  ArrowLeft,
  Printer,
  Edit,
  User,
  Package,
  FileText,
  Receipt,
  Calculator,
  Settings,
  CheckCircle,
  XCircle,
  Trash2,
  Clock,
  CheckCircle2,
  X,
  DollarSign
} from 'lucide-vue-next'

const props = defineProps({
  sale: {
    type: Object,
    required: true
  }
})

const { can } = usePermissions()
const { alertState, showAlert, showConfirm, hideAlert, handleConfirm } = useAlert()

// Computed: Determinar si se puede generar factura
const canGenerateInvoice = computed(() => {
  // Si ya tiene factura, no se puede generar otra
  if (props.sale.invoice) {
    return false
  }
  
  // Se puede generar si:
  // 1. La venta está completada, O
  // 2. La venta es a crédito, O
  // 3. La venta tiene pago parcial/pendiente
  const isCompleted = props.sale.status === 'completed' || props.sale.status === 'complete'
  const isCredit = props.sale.payment_method === 'credit'
  const hasPendingPayment = ['partial', 'pending', 'unpaid'].includes(props.sale.payment_status)
  
  return isCompleted || isCredit || hasPendingPayment
})

// Methods
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
  return new Date(date).toLocaleDateString('es-BO', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getStatusBannerClass = (status) => {
  const classes = {
    draft: 'bg-gray-50 border-gray-200 text-gray-800',
    pending: 'bg-yellow-50 border-yellow-200 text-yellow-800',
    completed: 'bg-green-50 border-green-200 text-green-800',
    complete: 'bg-green-50 border-green-200 text-green-800',
    cancelled: 'bg-red-50 border-red-200 text-red-800',
    canceled: 'bg-red-50 border-red-200 text-red-800'
  }
  return classes[status] || 'bg-gray-50 border-gray-200 text-gray-800'
}

const getStatusIcon = (status) => {
  const icons = {
    draft: Clock,
    pending: Clock,
    completed: CheckCircle2,
    complete: CheckCircle2,
    cancelled: X,
    canceled: X
  }
  return icons[status] || Clock
}

const getStatusTitle = (status) => {
  const titles = {
    draft: 'Venta en Borrador',
    pending: 'Venta Pendiente',
    completed: 'Venta Completada',
    complete: 'Venta Completada',
    cancelled: 'Venta Cancelada',
    canceled: 'Venta Cancelada'
  }
  return titles[status] || 'Estado Desconocido'
}

const getStatusDescription = (status) => {
  const descriptions = {
    draft: 'Esta venta está en borrador.',
    pending: 'Esta venta está pendiente de completar.',
    completed: 'Esta venta ha sido completada exitosamente.',
    complete: 'Esta venta ha sido completada exitosamente.',
    cancelled: 'Esta venta ha sido cancelada.',
    canceled: 'Esta venta ha sido cancelada.'
  }
  return descriptions[status] || 'Estado no definido.'
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
const printSale = () => {
  window.open(`/ventas/${props.sale.id}/imprimir`, '_blank')
}

const editSale = () => {
  router.visit(`/ventas/${props.sale.id}/editar`)
}

const completeSale = () => {
  showConfirm({
    title: 'Completar Venta',
    message: '¿Estás seguro de que quieres completar esta venta?',
    confirmText: 'Sí, completar',
    onConfirm: () => {
      router.post(`/ventas/${props.sale.id}/completar`, {}, {
        preserveScroll: true,
        preserveState: false,
        onSuccess: () => {
          router.reload({ only: ['sale'] })
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

const cancelSale = () => {
  showConfirm({
    title: 'Cancelar Venta',
    message: '¿Estás seguro de que quieres cancelar esta venta?',
    confirmText: 'Sí, cancelar',
    onConfirm: () => {
      router.post(`/ventas/${props.sale.id}/cancelar`, {}, {
        preserveScroll: true,
        preserveState: false,
        onSuccess: () => {
          router.reload({ only: ['sale'] })
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

const deleteSale = () => {
  showConfirm({
    title: 'Eliminar Venta',
    message: '¿Estás seguro de que quieres eliminar esta venta? Esta acción no se puede deshacer.',
    confirmText: 'Sí, eliminar',
    onConfirm: () => {
      router.delete(`/ventas/${props.sale.id}`, {
        preserveScroll: true,
        preserveState: false,
        onSuccess: () => {
          router.visit('/ventas')
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

const generateInvoice = () => {
  showConfirm({
    title: 'Generar Factura',
    message: '¿Estás seguro de que quieres generar una factura para esta venta?',
    confirmText: 'Sí, generar',
    onConfirm: () => {
      router.post(`/ventas/${props.sale.id}/factura`, {}, {
        preserveScroll: true,
        preserveState: false,
        onSuccess: () => {
          router.reload({ only: ['sale'] })
          showAlert({
            type: 'success',
            title: 'Éxito',
            message: 'La factura ha sido generada exitosamente.'
          })
        },
        onError: (errors) => {
          showAlert({
            type: 'error',
            title: 'Error',
            message: errors?.message || 'No se pudo generar la factura. Por favor, intente nuevamente.'
          })
        }
      })
    }
  })
}

</script>