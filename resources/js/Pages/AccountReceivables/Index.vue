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
                <p class="text-2xl font-bold text-gray-900">${{ formatPrice(stats.totalReceivable || 0) }}</p>
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
                <p class="text-2xl font-bold text-gray-900">${{ formatPrice(stats.overdueAmount || 0) }}</p>
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
                <p class="text-2xl font-bold text-gray-900">${{ formatPrice(stats.totalPaid || 0) }}</p>
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
                <tr v-for="invoice in invoices.data" :key="invoice.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ invoice.invoice_number }}</div>
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
                    <div class="text-sm font-medium text-gray-900">${{ formatPrice(invoice.total) }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-green-600">${{ formatPrice(invoice.paid_amount) }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium" :class="getBalanceClass(invoice)">
                      ${{ formatPrice(invoice.balance) }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      class="px-2 py-1 text-xs font-medium rounded-full"
                      :class="getPaymentStatusColorClass(invoice.payment_status)"
                    >
                      {{ invoice.payment_status_label }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex items-center justify-end gap-2">
                      <Link
                        :href="`/cuentas-por-cobrar/${invoice.id}`"
                        class="text-primary-700 hover:text-primary-900"
                      >
                        Ver
                      </Link>
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
      <div v-if="invoices.data.length > 0" class="mt-6">
        <Pagination :links="invoices.links" />
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardContent } from '@/Components/ui'
import Pagination from '@/Components/Pagination.vue'
import { usePermissions } from '@/composables/usePermissions'
import { useDebouncedRef } from '@/composables/useDebouncedRef'
import { DollarSign, AlertTriangle, Clock, CheckCircle, Receipt } from 'lucide-vue-next'

const { can } = usePermissions()

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

const exportData = () => {
  const params = new URLSearchParams(filters.value)
  window.open(`/cuentas-por-cobrar/export?${params.toString()}`, '_blank')
}
</script>
