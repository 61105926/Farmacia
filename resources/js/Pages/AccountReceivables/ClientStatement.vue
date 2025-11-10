<template>
  <AdminLayout>
    <div class="p-6">
      <!-- Header -->
      <div class="mb-6 flex items-center justify-between">
        <div>
          <Link href="/cuentas-por-cobrar" class="text-sm text-primary-700 hover:text-primary-800 mb-2 inline-block">
            ← Volver a cuentas por cobrar
          </Link>
          <h1 class="text-2xl font-bold text-gray-900">Estado de Cuenta</h1>
          <p class="text-sm text-gray-600 mt-1">{{ client.business_name }} - {{ client.trade_name }}</p>
        </div>
      </div>

      <!-- Totals -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <Card>
          <CardContent class="p-4">
            <p class="text-sm font-medium text-gray-500">Total Facturado</p>
            <p class="text-2xl font-bold text-gray-900">{{ formatPrice(totals.totalInvoiced || 0) }}</p>
          </CardContent>
        </Card>
        <Card>
          <CardContent class="p-4">
            <p class="text-sm font-medium text-gray-500">Total Pagado</p>
            <p class="text-2xl font-bold text-green-600">{{ formatPrice(totals.totalPaid || 0) }}</p>
          </CardContent>
        </Card>
        <Card>
          <CardContent class="p-4">
            <p class="text-sm font-medium text-gray-500">Saldo Pendiente</p>
            <p class="text-2xl font-bold text-orange-600">{{ formatPrice(totals.totalBalance || 0) }}</p>
          </CardContent>
        </Card>
        <Card>
          <CardContent class="p-4">
            <p class="text-sm font-medium text-gray-500">Vencido</p>
            <p class="text-2xl font-bold text-red-600">{{ formatPrice(totals.overdueAmount || 0) }}</p>
          </CardContent>
        </Card>
      </div>

      <!-- Filters -->
      <Card class="mb-6">
        <CardContent class="p-4">
          <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
              <input
                v-model="dateFrom"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
              <input
                v-model="dateTo"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
              />
            </div>
            <div class="flex items-end gap-2">
              <button
                type="submit"
                class="px-4 py-2 bg-primary-700 text-white rounded-md hover:bg-primary-800 transition-colors"
              >
                Filtrar
              </button>
              <button
                type="button"
                @click="clearFilters"
                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors"
              >
                Limpiar
              </button>
            </div>
          </form>
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
                <tr v-for="invoice in invoices" :key="invoice.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ invoice.invoice_number }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ formatDate(invoice.invoice_date) }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm" :class="getDueDateClass(invoice.due_date)">
                      {{ invoice.due_date ? formatDate(invoice.due_date) : 'N/A' }}
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
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      class="px-2 py-1 text-xs font-medium rounded-full"
                      :class="getPaymentStatusColorClass(invoice.payment_status)"
                    >
                      {{ getPaymentStatusLabel(invoice.payment_status) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <Link
                      :href="`/cuentas-por-cobrar/${invoice.id}`"
                      class="text-primary-700 hover:text-primary-900"
                    >
                      Ver
                    </Link>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Empty State -->
          <div v-if="invoices.length === 0" class="text-center py-12">
            <Receipt class="mx-auto h-12 w-12 text-gray-400" />
            <h3 class="mt-2 text-sm font-medium text-gray-900">No hay facturas</h3>
            <p class="mt-1 text-sm text-gray-500">No se encontraron facturas para este cliente.</p>
          </div>
        </CardContent>
      </Card>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardContent } from '@/Components/ui'
import { Receipt } from 'lucide-vue-next'

const props = defineProps({
  client: Object,
  invoices: Array,
  totals: Object,
  filters: Object,
})

const dateFrom = ref(props.filters?.date_from || '')
const dateTo = ref(props.filters?.date_to || '')

const applyFilters = () => {
  router.get(`/cuentas-por-cobrar/cliente/${props.client.id}/estado`, {
    date_from: dateFrom.value,
    date_to: dateTo.value,
  })
}

const clearFilters = () => {
  dateFrom.value = ''
  dateTo.value = ''
  router.get(`/cuentas-por-cobrar/cliente/${props.client.id}/estado`)
}

const formatDate = (date) => {
  if (!date) return 'N/A'
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

const getDueDateClass = (dueDate) => {
  if (!dueDate) return 'text-gray-500'
  const due = new Date(dueDate)
  const today = new Date()
  if (due < today) return 'text-red-600'
  if (due <= new Date(today.getTime() + 7 * 24 * 60 * 60 * 1000)) return 'text-orange-600'
  return 'text-gray-900'
}

const getBalanceClass = (invoice) => {
  if (invoice.balance === 0) return 'text-green-600'
  if (invoice.balance < invoice.total) return 'text-orange-600'
  return 'text-gray-900'
}

const getPaymentStatusColorClass = (status) => {
  const classes = {
    'unpaid': 'bg-red-100 text-red-800',
    'partial': 'bg-yellow-100 text-yellow-800',
    'paid': 'bg-green-100 text-green-800',
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getPaymentStatusLabel = (status) => {
  const labels = {
    'unpaid': 'Sin Pagar',
    'partial': 'Parcial',
    'paid': 'Pagado',
  }
  return labels[status] || status
}
</script>

