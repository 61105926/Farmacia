<template>
  <AdminLayout>
    <div class="p-6">
      <!-- Header -->
      <div class="mb-6 flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Cuentas por Cobrar</h1>
          <p class="text-sm text-gray-600 mt-1">Gestión de cartera y seguimiento de pagos</p>
        </div>
        <div class="flex items-center gap-2">
          <button
            @click="openCreatePaymentModal"
            type="button"
            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors font-medium"
          >
            💰 Registrar Pago
          </button>
          <Link
            href="/cuentas-por-cobrar/pagos/listado"
            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors"
          >
            Ver Pagos
          </Link>
          <Link
            href="/cuentas-por-cobrar/vencidas"
            class="px-4 py-2 border border-red-300 text-red-700 rounded-md hover:bg-red-50 transition-colors"
          >
            Facturas Vencidas
          </Link>
          <Link
            href="/cuentas-por-cobrar/reporte-antiguedad"
            class="px-4 py-2 border border-blue-300 text-blue-700 rounded-md hover:bg-blue-50 transition-colors"
          >
            Reporte Antigüedad
          </Link>
          <button
            @click="exportData"
            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors"
          >
            Exportar
          </button>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <Card>
          <CardContent class="p-4">
            <div class="flex items-center">
              <div class="p-2 bg-blue-100 rounded-lg">
                <DollarSign class="w-6 h-6 text-blue-600" />
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-500">Total por Cobrar</p>
                <p class="text-2xl font-bold text-gray-900">{{ formatPrice(stats.totalReceivable || 0) }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-4">
            <div class="flex items-center">
              <div class="p-2 bg-red-100 rounded-lg">
                <AlertTriangle class="w-6 h-6 text-red-600" />
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-500">Vencido</p>
                <p class="text-2xl font-bold text-gray-900">{{ formatPrice(stats.overdueAmount || 0) }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-4">
            <div class="flex items-center">
              <div class="p-2 bg-yellow-100 rounded-lg">
                <Clock class="w-6 h-6 text-yellow-600" />
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-500">Facturas Sin Pagar</p>
                <p class="text-2xl font-bold text-gray-900">{{ stats.unpaidInvoices || 0 }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-4">
            <div class="flex items-center">
              <div class="p-2 bg-green-100 rounded-lg">
                <CheckCircle class="w-6 h-6 text-green-600" />
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-500">Total Pagado</p>
                <p class="text-2xl font-bold text-gray-900">{{ formatPrice(stats.totalPaid || 0) }}</p>
              </div>
            </div>
          </CardContent>
        </Card>
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
                placeholder="Buscar por número, cliente..."
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                @input="debouncedSearch"
              />
            </div>

            <!-- Payment Status Filter -->
            <div>
              <select
                v-model="filters.payment_status"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                @change="applyFilters"
              >
                <option value="">Todos los pagos</option>
                <option v-for="(label, value) in paymentStatuses" :key="value" :value="value">
                  {{ label }}
                </option>
              </select>
            </div>

            <!-- Client Filter -->
            <div>
              <select
                v-model="filters.client_id"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                @change="applyFilters"
              >
                <option value="">Todos los clientes</option>
                <option v-for="client in clients" :key="client.id" :value="client.id">
                  {{ client.business_name }}
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

          <!-- Quick Filters -->
          <div class="mt-4 flex items-center gap-4">
            <label class="flex items-center">
              <input
                v-model="filters.overdue"
                type="checkbox"
                class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                @change="applyFilters"
              />
              <span class="ml-2 text-sm text-gray-700">Solo vencidas</span>
            </label>
            <label class="flex items-center">
              <input
                v-model="filters.unpaid"
                type="checkbox"
                class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                @change="applyFilters"
              />
              <span class="ml-2 text-sm text-gray-700">Solo sin pagar</span>
            </label>
          </div>
        </CardContent>
      </Card>

      <!-- Invoices Table -->
      <Card>
        <CardContent class="p-0">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Factura
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Cliente
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Fecha
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Vencimiento
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Días Vencidos
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Total
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Pagado
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Saldo
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
                <tr 
                  v-for="invoice in invoices.data" 
                  :key="invoice.id" 
                  class="hover:bg-gray-50"
                  :class="getDaysOverdue(invoice) > 0 ? 'bg-red-50' : ''"
                >
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">
                      {{ invoice.sale?.invoice_number || invoice.invoice_number }}
                    </div>
                    <div v-if="invoice.sale?.invoice_number && invoice.invoice_number" class="text-xs text-gray-500">
                      Referencia: {{ invoice.invoice_number }}
                    </div>
                    <div v-if="invoice.order_id" class="text-xs text-gray-500">
                      Desde pedido: {{ invoice.order?.order_number }}
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div>
                      <div class="text-sm font-medium text-gray-900">{{ invoice.client_name }}</div>
                      <div v-if="invoice.client_tax_id" class="text-sm text-gray-500">{{ invoice.client_tax_id }}</div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ formatDate(invoice.invoice_date) }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm" :class="getDueDateClass(invoice)">
                      {{ invoice.due_date ? formatDate(invoice.due_date) : 'N/A' }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div v-if="getDaysOverdue(invoice) > 0" class="text-sm font-medium" :class="getDaysOverdueClass(invoice)">
                      {{ getDaysOverdue(invoice) }} días
                      <span class="ml-1 text-xs">⚠️</span>
                    </div>
                    <div v-else-if="invoice.due_date" class="text-sm text-gray-500">
                      Al día
                    </div>
                    <div v-else class="text-sm text-gray-400">
                      —
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ formatPrice(invoice.total) }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-green-600">{{ formatPrice(invoice.paid_amount) }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium" :class="getBalanceClass(invoice)">
                      {{ formatPrice(invoice.balance) }}
                    </div>
                    <div v-if="invoice.payments && invoice.payments.length > 0" class="text-xs text-gray-500 mt-1">
                      {{ invoice.payments.length }} pago(s) registrado(s)
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      class="px-2 py-1 text-xs font-medium rounded-full"
                      :class="getPaymentStatusColorClass(invoice.payment_status)"
                    >
                      {{ invoice.payment_status_label }}
                    </span>
                    <div v-if="getDaysOverdue(invoice) > 0" class="mt-1">
                      <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">
                        ⚠️ {{ getDaysOverdue(invoice) }} días en mora
                      </span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex items-center justify-end gap-2">
                      <button
                        @click="viewInvoice(invoice.id)"
                        class="text-primary-700 hover:text-primary-900"
                      >
                        Ver
                      </button>
                      <Link
                        v-if="canAddPayment(invoice)"
                        :href="`/cuentas-por-cobrar/${invoice.id}#pagos`"
                        class="text-blue-600 hover:text-blue-900"
                      >
                        Pagar
                      </Link>
                      <Link
                        :href="`/cuentas-por-cobrar/cliente/${invoice.client_id}/estado`"
                        class="text-green-600 hover:text-green-900"
                      >
                        Estado Cliente
                      </Link>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Empty State -->
          <div v-if="invoices.data.length === 0" class="text-center py-12">
            <Receipt class="mx-auto h-12 w-12 text-gray-400" />
            <h3 class="mt-2 text-sm font-medium text-gray-900">No hay facturas</h3>
            <p class="mt-1 text-sm text-gray-500">No se encontraron facturas con los filtros aplicados.</p>
          </div>
        </CardContent>
      </Card>

      <!-- Pagination -->
      <div v-if="invoices && invoices.links && invoices.links.length > 0" class="mt-6">
        <Pagination 
          :links="invoices.links" 
          :pagination-data="{
            from: invoices.from,
            to: invoices.to,
            total: invoices.total,
            current_page: invoices.current_page,
            last_page: invoices.last_page
          }"
        />
      </div>

      <!-- Create Payment Modal -->
      <div v-if="showCreatePaymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" style="z-index: 9999;" @click.self="closeCreatePaymentModal">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white" style="z-index: 10000;">
          <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-medium text-gray-900">Registrar Pago de Cliente</h3>
              <button
                @click="closeCreatePaymentModal"
                class="text-gray-400 hover:text-gray-600"
              >
                ✕
              </button>
            </div>
            <form @submit.prevent="submitCreatePayment">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Cliente <span class="text-red-500">*</span>
                  </label>
                  <select
                    v-model="createPaymentForm.client_id"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                    @change="loadClientInvoices"
                  >
                    <option value="">Seleccionar cliente</option>
                    <option v-for="client in clients" :key="client.id" :value="client.id">
                      {{ client.business_name }} - {{ client.trade_name }}
                    </option>
                  </select>
                </div>

                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Factura (Opcional)
                  </label>
                  <select
                    v-model="createPaymentForm.invoice_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                    :disabled="!createPaymentForm.client_id"
                  >
                    <option value="">Sin factura específica</option>
                    <option v-for="invoice in availableInvoices" :key="invoice.id" :value="invoice.id">
                      {{ invoice.sale?.invoice_number || invoice.invoice_number }} - Saldo: {{ formatPrice(invoice.balance) }}
                    </option>
                  </select>
                  <div v-if="selectedInvoice" class="text-xs text-gray-500 mt-1">
                    Saldo disponible: {{ formatPrice(selectedInvoice.balance) }}
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Monto <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="createPaymentForm.amount"
                    type="number"
                    min="0.01"
                    :max="selectedInvoice ? selectedInvoice.balance : null"
                    step="0.01"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Fecha de Pago <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="createPaymentForm.payment_date"
                    type="date"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Método de Pago <span class="text-red-500">*</span>
                  </label>
                  <select
                    v-model="createPaymentForm.payment_method"
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

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Referencia
                  </label>
                  <input
                    v-model="createPaymentForm.payment_reference"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                    placeholder="Número de referencia/transacción"
                  />
                </div>

                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Observaciones
                  </label>
                  <textarea
                    v-model="createPaymentForm.notes"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                    placeholder="Observaciones del pago..."
                  ></textarea>
                </div>
              </div>

              <div class="flex justify-end gap-2 mt-6">
                <button
                  type="button"
                  @click="closeCreatePaymentModal"
                  class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50"
                >
                  Cancelar
                </button>
                <button
                  type="submit"
                  :disabled="createPaymentForm.processing"
                  class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50"
                >
                  {{ createPaymentForm.processing ? 'Registrando...' : 'Registrar Pago' }}
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
import { ref, computed } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardContent } from '@/Components/ui'
import Pagination from '@/Components/ui/Pagination.vue'
import { usePermissions } from '@/composables/usePermissions'
import { useDebouncedRef } from '@/composables/useDebouncedRef'
import { DollarSign, AlertTriangle, Clock, CheckCircle, Receipt } from 'lucide-vue-next'
import axios from 'axios'

const { can } = usePermissions()

const showCreatePaymentModal = ref(false)
const availableInvoices = ref([])

const createPaymentForm = useForm({
  client_id: '',
  invoice_id: '',
  amount: '',
  payment_date: new Date().toISOString().split('T')[0],
  payment_method: '',
  payment_reference: '',
  bank_name: '',
  account_number: '',
  check_number: '',
  notes: '',
})

const props = defineProps({
  invoices: Object,
  clients: Array,
  paymentStatuses: Object,
  filters: Object,
  stats: {
    type: Object,
    default: () => ({})
  }
})

const filters = ref({
  search: props.filters.search || '',
  payment_status: props.filters.payment_status || '',
  client_id: props.filters.client_id || '',
  date_from: props.filters.date_from || '',
  date_to: props.filters.date_to || '',
  overdue: props.filters.overdue || false,
  unpaid: props.filters.unpaid || false,
})

const debouncedSearch = useDebouncedRef(() => {
  applyFilters()
}, 500)

const applyFilters = () => {
  router.get('/cuentas-por-cobrar', filters.value, {
    preserveState: true,
    preserveScroll: true,
  })
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

const getDaysOverdue = (invoice) => {
  // Si la factura está pagada, no está en mora
  if (invoice.balance === 0 || invoice.payment_status === 'paid') {
    return 0
  }
  if (!invoice.due_date) return 0
  const due = new Date(invoice.due_date)
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  due.setHours(0, 0, 0, 0)
  const diffTime = today - due
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  return diffDays > 0 ? diffDays : 0
}

const getDaysOverdueClass = (invoice) => {
  const days = getDaysOverdue(invoice)
  if (days === 0) return 'text-gray-500'
  if (days <= 30) return 'text-yellow-600'
  if (days <= 60) return 'text-orange-600'
  if (days <= 90) return 'text-red-600'
  return 'text-red-800 font-bold'
}

const getPaymentStatusColorClass = (paymentStatus) => {
  const classes = {
    'unpaid': 'bg-red-100 text-red-800',
    'partial': 'bg-yellow-100 text-yellow-800',
    'paid': 'bg-green-100 text-green-800',
  }
  return classes[paymentStatus] || 'bg-gray-100 text-gray-800'
}

const canAddPayment = (invoice) => {
  return can('sales.payment') && invoice.balance > 0 && invoice.status !== 'cancelled'
}

const viewInvoice = (invoiceId) => {
  router.visit(`/cuentas-por-cobrar/${invoiceId}`)
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

const exportData = () => {
  // Filtrar solo los valores que tienen contenido
  const exportFilters = {}
  if (filters.value.search) exportFilters.search = filters.value.search
  if (filters.value.payment_status) exportFilters.payment_status = filters.value.payment_status
  if (filters.value.client_id) exportFilters.client_id = filters.value.client_id
  if (filters.value.date_from) exportFilters.date_from = filters.value.date_from
  if (filters.value.date_to) exportFilters.date_to = filters.value.date_to
  if (filters.value.overdue === true) exportFilters.overdue = '1'
  if (filters.value.unpaid === true) exportFilters.unpaid = '1'
  
  const params = new URLSearchParams(exportFilters)
  window.open(`/cuentas-por-cobrar/export?${params.toString()}`, '_blank')
}

const loadClientInvoices = async () => {
  if (!createPaymentForm.client_id) {
    availableInvoices.value = []
    createPaymentForm.invoice_id = ''
    return
  }

  try {
    const response = await axios.get(`/cuentas-por-cobrar/api/cliente/${createPaymentForm.client_id}/facturas`)
    availableInvoices.value = response.data
  } catch (error) {
    console.error('Error cargando facturas:', error)
    availableInvoices.value = []
  }
}

const selectedInvoice = computed(() => {
  if (!createPaymentForm.invoice_id) return null
  return availableInvoices.value.find(inv => inv.id === parseInt(createPaymentForm.invoice_id))
})

const openCreatePaymentModal = () => {
  console.log('openCreatePaymentModal llamado')
  console.log('showCreatePaymentModal antes:', showCreatePaymentModal.value)
  showCreatePaymentModal.value = true
  console.log('showCreatePaymentModal después:', showCreatePaymentModal.value)
  createPaymentForm.reset()
  createPaymentForm.payment_date = new Date().toISOString().split('T')[0]
  availableInvoices.value = []
  console.log('Modal debería estar abierto ahora')
  // Forzar actualización del DOM
  setTimeout(() => {
    console.log('showCreatePaymentModal después del timeout:', showCreatePaymentModal.value)
    const modal = document.querySelector('.fixed.inset-0')
    console.log('Modal encontrado en DOM:', modal)
  }, 100)
}

const closeCreatePaymentModal = () => {
  showCreatePaymentModal.value = false
  createPaymentForm.reset()
  createPaymentForm.payment_date = new Date().toISOString().split('T')[0]
  availableInvoices.value = []
}

const submitCreatePayment = () => {
  console.log('submitCreatePayment llamado')
  
  // Preparar datos para enviar, convirtiendo strings vacíos a null
  const dataToSend = {
    client_id: createPaymentForm.client_id,
    invoice_id: createPaymentForm.invoice_id || null,
    amount: parseFloat(createPaymentForm.amount) || 0,
    payment_date: createPaymentForm.payment_date,
    payment_method: createPaymentForm.payment_method,
    payment_reference: createPaymentForm.payment_reference?.trim() || null,
    bank_name: createPaymentForm.bank_name?.trim() || null,
    account_number: createPaymentForm.account_number?.trim() || null,
    check_number: createPaymentForm.check_number?.trim() || null,
    notes: createPaymentForm.notes?.trim() || null,
  }
  
  console.log('Datos del formulario:', dataToSend)
  
  createPaymentForm.transform(() => dataToSend).post('/cuentas-por-cobrar/pagos', {
    preserveScroll: true,
    preserveState: false,
    onSuccess: (page) => {
      console.log('Pago registrado exitosamente')
      closeCreatePaymentModal()
      router.reload({ only: ['invoices', 'stats'] })
      // Mostrar mensaje de éxito
      if (page.props.flash?.success) {
        alert(page.props.flash.success)
      } else {
        alert('Pago registrado exitosamente')
      }
    },
    onError: (errors) => {
      console.error('Error al registrar pago:', errors)
      if (errors?.error) {
        alert('Error: ' + errors.error)
      } else if (typeof errors === 'string') {
        alert('Error: ' + errors)
      } else {
        const errorMessage = Object.values(errors).flat().join(', ') || 'Error al registrar el pago. Por favor, verifique los datos e intente nuevamente.'
        alert('Error: ' + errorMessage)
      }
    }
  })
}
</script>
