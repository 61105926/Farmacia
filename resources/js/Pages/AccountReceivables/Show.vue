<template>
  <AdminLayout>
    <div class="p-6">
      <!-- Error State -->
      <div v-if="!invoice" class="text-center py-12">
        <Receipt class="mx-auto h-12 w-12 text-gray-400" />
        <h3 class="mt-2 text-sm font-medium text-gray-900">Factura no encontrada</h3>
        <p class="mt-1 text-sm text-gray-500">La factura que buscas no existe o ha sido eliminada.</p>
        <Link
          href="/cuentas-por-cobrar"
          class="mt-4 inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-700 hover:bg-primary-800"
        >
          Volver a Cuentas por Cobrar
        </Link>
      </div>

      <!-- Invoice Content -->
      <div v-else>
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
          <div>
            <Link href="/cuentas-por-cobrar" class="text-sm text-primary-700 hover:text-primary-800 mb-2 inline-block">
              ← Volver a cuentas por cobrar
            </Link>
            <h1 class="text-2xl font-bold text-gray-900">Detalle de Factura</h1>
            <p class="text-sm text-gray-600 mt-1">{{ invoice.sale?.invoice_number || invoice.invoice_number }}</p>
            <p v-if="invoice.sale?.invoice_number && invoice.invoice_number" class="text-xs text-gray-500 mt-1">
              Referencia: {{ invoice.invoice_number }}
            </p>
          </div>
          <div class="flex items-center gap-2">
            <button
              v-if="canAddPayment"
              @click="showPaymentModal = true"
              class="px-4 py-2 bg-primary-700 text-white rounded-md hover:bg-primary-800 transition-colors"
            >
              Agregar Pago
            </button>
            <Link
              :href="`/ventas/${invoice.id}`"
              class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors"
            >
              Ver Factura
            </Link>
          </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Detalle principal -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Información de la Factura -->
          <Card>
            <CardHeader>
              <CardTitle>Información de la Factura</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-500">Número de Factura</label>
                  <div class="text-sm text-gray-900">{{ invoice.sale?.invoice_number || invoice.invoice_number }}</div>
                  <div v-if="invoice.sale?.invoice_number && invoice.invoice_number" class="text-xs text-gray-500 mt-1">
                    Referencia: {{ invoice.invoice_number }}
                  </div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500">Estado</label>
                  <span
                    class="px-2 py-1 text-xs font-medium rounded-full"
                    :class="getStatusColorClass(invoice.status)"
                  >
                    {{ invoice.status_label }}
                  </span>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500">Fecha de Factura</label>
                  <div class="text-sm text-gray-900">{{ formatDate(invoice.invoice_date) }}</div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500">Fecha de Vencimiento</label>
                  <div class="text-sm" :class="getDueDateClass(invoice)">
                    {{ invoice.due_date ? formatDate(invoice.due_date) : 'N/A' }}
                  </div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500">Método de Pago</label>
                  <div class="text-sm text-gray-900">{{ invoice.payment_method || 'N/A' }}</div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500">Términos de Pago</label>
                  <div class="text-sm text-gray-900">{{ invoice.payment_terms || 'N/A' }}</div>
                </div>
              </div>

              <div v-if="invoice.order_id" class="mt-4">
                <label class="block text-sm font-medium text-gray-500">Pedido Origen</label>
                <div class="text-sm text-gray-900">{{ invoice.order?.order_number }}</div>
              </div>

              <div v-if="invoice.notes" class="mt-6">
                <label class="block text-sm font-medium text-gray-500">Observaciones</label>
                <div class="text-sm text-gray-900 whitespace-pre-line">{{ invoice.notes }}</div>
              </div>
            </CardContent>
          </Card>

          <!-- Información del Cliente -->
          <Card>
            <CardHeader>
              <CardTitle>Información del Cliente</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-500">Razón Social</label>
                  <div class="text-sm text-gray-900">{{ invoice.client_name }}</div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500">NIT</label>
                  <div class="text-sm text-gray-900">{{ invoice.client_tax_id || 'N/A' }}</div>
                </div>
              </div>

              <div v-if="invoice.client_address" class="mt-4">
                <label class="block text-sm font-medium text-gray-500">Dirección</label>
                <div class="text-sm text-gray-900">{{ invoice.client_address }}</div>
              </div>

              <div class="mt-4">
                <Link
                  :href="`/cuentas-por-cobrar/cliente/${invoice.client_id}/estado`"
                  class="text-sm text-primary-700 hover:text-primary-800"
                >
                  Ver estado completo del cliente →
                </Link>
              </div>
            </CardContent>
          </Card>

          <!-- Historial de Pagos -->
          <Card>
            <CardHeader>
              <CardTitle>Historial de Pagos</CardTitle>
            </CardHeader>
            <CardContent class="p-0">
              <div v-if="invoice.payments && invoice.payments.length > 0" class="overflow-x-auto">
                <table class="w-full">
                  <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Fecha
                      </th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Monto
                      </th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Método
                      </th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Referencia
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
                    <tr v-for="payment in invoice.payments" :key="payment.id" class="hover:bg-gray-50">
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ formatDate(payment.payment_date) }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ formatPrice(payment.amount) }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ payment.payment_method_label }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ payment.payment_reference || '—' }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <span
                          class="px-2 py-1 text-xs font-medium rounded-full"
                          :class="getPaymentStatusColorClass(payment.status)"
                        >
                          {{ payment.status_label }}
                        </span>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex items-center justify-end gap-2">
                          <Link
                            :href="`/cuentas-por-cobrar/pagos/${payment.id}`"
                            class="text-primary-700 hover:text-primary-900"
                          >
                            Ver
                          </Link>
                          <button
                            v-if="canApprovePayment(payment)"
                            @click="approvePayment(payment)"
                            class="text-green-600 hover:text-green-900"
                          >
                            Aprobar
                          </button>
                          <button
                            v-if="canCancelPayment(payment)"
                            @click="cancelPayment(payment)"
                            class="text-red-600 hover:text-red-900"
                          >
                            Cancelar
                          </button>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <div v-else class="text-center py-8 text-gray-500">
                <CreditCard class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-medium text-gray-900">No hay pagos registrados</h3>
                <p class="mt-1 text-sm text-gray-500">Esta factura no tiene pagos registrados.</p>
                <div v-if="canAddPayment" class="mt-6">
                  <button
                    @click="showPaymentModal = true"
                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-700 hover:bg-primary-800"
                  >
                    Agregar Primer Pago
                  </button>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Resumen Financiero -->
          <Card>
            <CardHeader>
              <CardTitle>Resumen Financiero</CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">Subtotal</span>
                <span class="text-sm text-gray-900">{{ formatPrice(invoice.subtotal) }}</span>
              </div>
              <div v-if="invoice.discount_amount > 0" class="flex items-center justify-between">
                <span class="text-sm text-gray-600">Descuento</span>
                <span class="text-sm text-green-600">-{{ formatPrice(invoice.discount_amount) }}</span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">IVA (19%)</span>
                <span class="text-sm text-gray-900">{{ formatPrice(invoice.tax_amount) }}</span>
              </div>
              <div class="flex items-center justify-between border-t pt-2">
                <span class="text-sm font-medium text-gray-900">Total</span>
                <span class="text-sm font-medium text-gray-900">{{ formatPrice(invoice.total) }}</span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">Pagado</span>
                <span class="text-sm text-green-600">{{ formatPrice(invoice.paid_amount) }}</span>
              </div>
              <div class="flex items-center justify-between border-t pt-2">
                <span class="text-sm font-medium text-gray-900">Saldo</span>
                <span class="text-sm font-medium" :class="getBalanceClass(invoice)">
                  {{ formatPrice(invoice.balance) }}
                </span>
              </div>
            </CardContent>
          </Card>

          <!-- Estado de Pago -->
          <Card>
            <CardHeader>
              <CardTitle>Estado de Pago</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="text-center">
                <span
                  class="px-3 py-1 text-sm font-medium rounded-full"
                  :class="getPaymentStatusColorClass(invoice.payment_status)"
                >
                  {{ invoice.payment_status_label }}
                </span>
                <div class="mt-2 text-xs text-gray-500">
                  {{ getPaymentStatusDescription(invoice) }}
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Acciones -->
          <div class="flex flex-col space-y-2">
            <button
              v-if="canAddPayment"
              @click="showPaymentModal = true"
              class="w-full px-4 py-2 bg-primary-700 text-white rounded-md hover:bg-primary-800 text-center transition-colors"
            >
              Agregar Pago
            </button>
            <Link
              :href="`/ventas/${invoice.id}`"
              class="w-full px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-center transition-colors"
            >
              Ver Factura Completa
            </Link>
            <Link
              :href="`/cuentas-por-cobrar/cliente/${invoice.client_id}/estado`"
              class="w-full px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-center transition-colors"
            >
              Estado del Cliente
            </Link>
          </div>
        </div>
      </div>

      <!-- Payment Modal -->
      <div v-if="showPaymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
          <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Agregar Pago</h3>
            <form @submit.prevent="submitPayment">
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Monto <span class="text-red-500">*</span>
                </label>
                <input
                  v-model.number="paymentForm.amount"
                  type="number"
                  min="0.01"
                  :max="invoice.balance"
                  step="0.01"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                />
                <div class="text-xs text-gray-500 mt-1">
                  Saldo disponible: {{ formatPrice(invoice.balance) }}
                </div>
              </div>
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Fecha de Pago <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="paymentForm.payment_date"
                  type="date"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                />
              </div>
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Método de Pago <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="paymentForm.payment_method"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                >
                  <option value="">Seleccionar método</option>
                  <option value="cash">Efectivo</option>
                  <option value="transfer">Transferencia</option>
                  <option value="check">Cheque</option>
                  <option value="credit_card">Tarjeta de Crédito</option>
                  <option value="debit_card">Tarjeta de Débito</option>
                  <option value="other">Otro</option>
                </select>
              </div>
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Referencia
                </label>
                <input
                  v-model="paymentForm.payment_reference"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                  placeholder="Número de referencia/transacción"
                />
              </div>
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Observaciones
                </label>
                <textarea
                  v-model="paymentForm.notes"
                  rows="3"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                  placeholder="Observaciones del pago..."
                ></textarea>
              </div>
              <div class="flex justify-end gap-2">
                <button
                  type="button"
                  @click="closePaymentModal"
                  class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50"
                >
                  Cancelar
                </button>
                <button
                  type="submit"
                  :disabled="paymentForm.processing"
                  class="px-4 py-2 bg-primary-700 text-white rounded-md hover:bg-primary-800 disabled:opacity-50"
                >
                  {{ paymentForm.processing ? 'Registrando...' : 'Registrar Pago' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Cancel Payment Modal -->
      <div v-if="showCancelModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
          <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Cancelar Pago</h3>
            <form @submit.prevent="submitCancellation">
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Motivo de cancelación <span class="text-red-500">*</span>
                </label>
                <textarea
                  v-model="cancelForm.reason"
                  rows="3"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                  placeholder="Motivo de la cancelación..."
                ></textarea>
              </div>
              <div class="flex justify-end gap-2">
                <button
                  type="button"
                  @click="closeCancelModal"
                  class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50"
                >
                  Cancelar
                </button>
                <button
                  type="submit"
                  class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                >
                  Cancelar Pago
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardHeader, CardTitle, CardContent } from '@/Components/ui'
import { usePermissions } from '@/composables/usePermissions'
import { CreditCard, Receipt } from 'lucide-vue-next'

const { can } = usePermissions()

const props = defineProps({
  invoice: {
    type: Object,
    required: false,
    default: () => null
  },
})

// Debug
console.log('AccountReceivables/Show - Componente montado')
console.log('AccountReceivables/Show - Props:', props)
console.log('AccountReceivables/Show - Invoice:', props.invoice)

const showPaymentModal = ref(false)
const showCancelModal = ref(false)
const selectedPayment = ref(null)

const paymentForm = useForm({
  amount: 0,
  payment_date: new Date().toISOString().split('T')[0],
  payment_method: '',
  payment_reference: '',
  notes: '',
})

const cancelForm = useForm({
  reason: '',
})

const canAddPayment = computed(() => {
  if (!props.invoice) return false
  return can('sales.payment') && props.invoice.balance > 0 && props.invoice.status !== 'cancelled'
})

const canApprovePayment = (payment) => {
  return can('payments.approve') && payment.status === 'pending'
}

const canCancelPayment = (payment) => {
  return can('payments.cancel') && ['pending', 'completed'].includes(payment.status)
}

const getStatusColorClass = (status) => {
  const classes = {
    'draft': 'bg-gray-100 text-gray-800',
    'pending': 'bg-yellow-100 text-yellow-800',
    'approved': 'bg-green-100 text-green-800',
    'paid': 'bg-blue-100 text-blue-800',
    'partially_paid': 'bg-purple-100 text-purple-800',
    'overdue': 'bg-red-100 text-red-800',
    'cancelled': 'bg-gray-100 text-gray-800',
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getPaymentStatusColorClass = (paymentStatus) => {
  const classes = {
    'unpaid': 'bg-red-100 text-red-800',
    'partial': 'bg-yellow-100 text-yellow-800',
    'paid': 'bg-green-100 text-green-800',
    'pending': 'bg-yellow-100 text-yellow-800',
    'completed': 'bg-green-100 text-green-800',
    'cancelled': 'bg-gray-100 text-gray-800',
    'rejected': 'bg-red-100 text-red-800',
  }
  return classes[paymentStatus] || 'bg-gray-100 text-gray-800'
}

const getDueDateClass = (invoice) => {
  if (!invoice.due_date) return 'text-gray-500'
  
  const dueDate = new Date(invoice.due_date)
  const today = new Date()
  const diffDays = Math.ceil((dueDate - today) / (1000 * 60 * 60 * 24))
  
  if (diffDays < 0) return 'text-red-600'
  if (diffDays <= 7) return 'text-orange-600'
  return 'text-gray-900'
}

const getBalanceClass = (invoice) => {
  if (invoice.balance === 0) return 'text-green-600'
  if (invoice.balance < invoice.total) return 'text-orange-600'
  return 'text-gray-900'
}

const getPaymentStatusDescription = (invoice) => {
  if (invoice.payment_status === 'paid') return 'Factura completamente pagada'
  if (invoice.payment_status === 'partial') return 'Pago parcial recibido'
  return 'Pendiente de pago'
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
  })
}

const formatPrice = (price) => {
  return new Intl.NumberFormat('es-BO', {
    style: 'currency',
    currency: 'BOB',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(price || 0)
}

const approvePayment = (payment) => {
  if (confirm(`¿Está seguro de aprobar el pago ${payment.payment_number}?`)) {
    router.post(`/cuentas-por-cobrar/pagos/${payment.id}/aprobar`)
  }
}

const cancelPayment = (payment) => {
  selectedPayment.value = payment
  cancelForm.reason = ''
  showCancelModal.value = true
}

const closePaymentModal = () => {
  showPaymentModal.value = false
  paymentForm.reset()
}

const closeCancelModal = () => {
  showCancelModal.value = false
  selectedPayment.value = null
  cancelForm.reset()
}

const submitPayment = () => {
  paymentForm.post('/cuentas-por-cobrar/pagos', {
    data: {
      ...paymentForm.data(),
      client_id: props.invoice.client_id,
      invoice_id: props.invoice.id,
    },
    onSuccess: () => {
      closePaymentModal()
    }
  })
}

const submitCancellation = () => {
  if (selectedPayment.value) {
    cancelForm.post(`/cuentas-por-cobrar/pagos/${selectedPayment.value.id}/cancelar`, {
      onSuccess: () => {
        closeCancelModal()
      }
    })
  }
}
</script>
