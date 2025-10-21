<template>
  <AdminLayout>
    <div class="p-6">
      <!-- Header -->
      <div class="mb-6 flex items-center justify-between">
        <div>
          <Link href="/cuentas-por-cobrar" class="text-sm text-primary-700 hover:text-primary-800 mb-2 inline-block">
            ← Volver a cuentas por cobrar
          </Link>
          <h1 class="text-2xl font-bold text-gray-900">Historial de Pagos</h1>
          <p class="text-sm text-gray-600 mt-1">Gestión y seguimiento de todos los pagos</p>
        </div>
        <div class="flex items-center gap-2">
          <button
            @click="exportPayments"
            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors"
          >
            Exportar
          </button>
        </div>
      </div>

      <!-- Filters -->
      <Card class="mb-6">
        <CardContent class="p-4">
          <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <!-- Search -->
            <div class="md:col-span-2">
              <input
                v-model="filters.search"
                type="text"
                placeholder="Buscar por número, cliente, factura..."
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                @input="debouncedSearch"
              />
            </div>

            <!-- Status Filter -->
            <div>
              <select
                v-model="filters.status"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                @change="applyFilters"
              >
                <option value="">Todos los estados</option>
                <option v-for="(label, value) in statuses" :key="value" :value="value">
                  {{ label }}
                </option>
              </select>
            </div>

            <!-- Payment Method Filter -->
            <div>
              <select
                v-model="filters.payment_method"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                @change="applyFilters"
              >
                <option value="">Todos los métodos</option>
                <option v-for="(label, value) in paymentMethods" :key="value" :value="value">
                  {{ label }}
                </option>
              </select>
            </div>

            <!-- Date From -->
            <div>
              <input
                v-model="filters.date_from"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                @change="applyFilters"
              />
            </div>

            <!-- Date To -->
            <div>
              <input
                v-model="filters.date_to"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                @change="applyFilters"
              />
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Payments Table -->
      <Card>
        <CardContent class="p-0">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Número
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Cliente
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Factura
                  </th>
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
                    Estado
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Acciones
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="payment in payments.data" :key="payment.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ payment.payment_number }}</div>
                    <div v-if="payment.payment_reference" class="text-xs text-gray-500">
                      Ref: {{ payment.payment_reference }}
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div>
                      <div class="text-sm font-medium text-gray-900">{{ payment.client?.business_name }}</div>
                      <div class="text-sm text-gray-500">{{ payment.client?.trade_name }}</div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div v-if="payment.invoice" class="text-sm text-gray-900">
                      {{ payment.invoice.invoice_number }}
                    </div>
                    <div v-else class="text-sm text-gray-400">Sin factura</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ formatDate(payment.payment_date) }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">${{ formatPrice(payment.amount) }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ payment.payment_method_label }}</div>
                    <div v-if="payment.bank_name" class="text-xs text-gray-500">{{ payment.bank_name }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      class="px-2 py-1 text-xs font-medium rounded-full"
                      :class="getStatusColorClass(payment.status)"
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

          <!-- Empty State -->
          <div v-if="payments.data.length === 0" class="text-center py-12">
            <CreditCard class="mx-auto h-12 w-12 text-gray-400" />
            <h3 class="mt-2 text-sm font-medium text-gray-900">No hay pagos</h3>
            <p class="mt-1 text-sm text-gray-500">No se encontraron pagos con los filtros aplicados.</p>
          </div>
        </CardContent>
      </Card>

      <!-- Pagination -->
      <div v-if="payments.data.length > 0" class="mt-6">
        <Pagination :links="payments.links" />
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
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardContent } from '@/Components/ui'
import Pagination from '@/Components/Pagination.vue'
import { usePermissions } from '@/Composables/usePermissions'
import { useDebouncedRef } from '@/Composables/useDebouncedRef'
import { CreditCard } from 'lucide-vue-next'

const { can } = usePermissions()

const props = defineProps({
  payments: Object,
  clients: Array,
  statuses: Object,
  paymentMethods: Object,
  filters: Object,
})

const filters = ref({
  search: props.filters.search || '',
  status: props.filters.status || '',
  payment_method: props.filters.payment_method || '',
  date_from: props.filters.date_from || '',
  date_to: props.filters.date_to || '',
})

const showCancelModal = ref(false)
const selectedPayment = ref(null)

const cancelForm = useForm({
  reason: '',
})

const debouncedSearch = useDebouncedRef(() => {
  applyFilters()
}, 500)

const applyFilters = () => {
  router.get('/cuentas-por-cobrar/pagos/listado', filters.value, {
    preserveState: true,
    preserveScroll: true,
  })
}

const getStatusColorClass = (status) => {
  const classes = {
    'pending': 'bg-yellow-100 text-yellow-800',
    'completed': 'bg-green-100 text-green-800',
    'cancelled': 'bg-gray-100 text-gray-800',
    'rejected': 'bg-red-100 text-red-800',
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const canApprovePayment = (payment) => {
  return can('payments.approve') && payment.status === 'pending'
}

const canCancelPayment = (payment) => {
  return can('payments.cancel') && ['pending', 'completed'].includes(payment.status)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
  })
}

const formatPrice = (price) => {
  return new Intl.NumberFormat('es-CO', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(price)
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

const closeCancelModal = () => {
  showCancelModal.value = false
  selectedPayment.value = null
  cancelForm.reset()
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

const exportPayments = () => {
  const params = new URLSearchParams(filters.value)
  window.open(`/cuentas-por-cobrar/pagos/export?${params.toString()}`, '_blank')
}
</script>
