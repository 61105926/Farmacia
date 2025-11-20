<template>
  <AdminLayout>
    <div class="p-6">
      <!-- Header -->
      <div class="mb-6 flex items-center justify-between">
        <div>
          <Link href="/reportes" class="text-sm text-primary-700 hover:text-primary-800 mb-2 inline-block">
            ← Volver a reportes
          </Link>
          <h1 class="text-2xl font-bold text-gray-900">Reporte de Ventas</h1>
          <p class="text-sm text-gray-600 mt-1">Análisis detallado de ventas y facturación</p>
        </div>
        <div class="flex items-center gap-2">
          <button
            @click="exportReport"
            class="px-4 py-2 bg-primary-700 text-white rounded-md hover:bg-primary-800 transition-colors"
          >
            Exportar Excel
          </button>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <Card>
          <CardContent class="p-4">
            <div class="flex items-center">
              <div class="p-2 bg-blue-100 rounded-lg">
                <Receipt class="w-6 h-6 text-blue-600" />
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-500">Total Facturas</p>
                <p class="text-xl font-bold text-gray-900">{{ periodStats.totalInvoices || 0 }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-4">
            <div class="flex items-center">
              <div class="p-2 bg-green-100 rounded-lg">
                <DollarSign class="w-6 h-6 text-green-600" />
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-500">Total Ventas</p>
                <p class="text-xl font-bold text-gray-900">{{ formatPrice(periodStats.totalAmount || 0) }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-4">
            <div class="flex items-center">
              <div class="p-2 bg-purple-100 rounded-lg">
                <CreditCard class="w-6 h-6 text-purple-600" />
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-500">Total Pagado</p>
                <p class="text-xl font-bold text-gray-900">{{ formatPrice(periodStats.totalPaid || 0) }}</p>
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
                <p class="text-sm font-medium text-gray-500">Saldo Pendiente</p>
                <p class="text-xl font-bold text-gray-900">{{ formatPrice(periodStats.totalBalance || 0) }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-4">
            <div class="flex items-center">
            <div class="p-2 bg-orange-100 rounded-lg">
                <TrendingUp class="w-6 h-6 text-orange-600" />
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-500">Promedio Factura</p>
                <p class="text-xl font-bold text-gray-900">{{ formatPrice(periodStats.averageInvoice || 0) }}</p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Filters -->
      <Card class="mb-6">
        <CardContent class="p-4">
          <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <!-- Date From -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Desde</label>
              <input
                v-model="filters.date_from"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                @change="applyFilters"
              />
            </div>

            <!-- Date To -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Hasta</label>
              <input
                v-model="filters.date_to"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                @change="applyFilters"
              />
            </div>

            <!-- Client Filter -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Cliente</label>
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

            <!-- Status Filter -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
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

            <!-- Payment Status Filter -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Estado Pago</label>
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

            <!-- Quick Actions -->
            <div class="flex items-end">
              <button
                @click="resetFilters"
                class="w-full px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors"
              >
                Limpiar
              </button>
            </div>
          </div>
        </CardContent>
      </Card>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Sales Table -->
        <div class="lg:col-span-2">
          <Card>
            <CardHeader>
              <CardTitle>Facturas del Período</CardTitle>
            </CardHeader>
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
                        Total
                      </th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Estado
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="invoice in invoices.data" :key="invoice.id" class="hover:bg-gray-50">
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">
                          {{ invoice.sale?.invoice_number || invoice.invoice_number }}
                        </div>
                        <div v-if="invoice.sale?.invoice_number && invoice.invoice_number" class="text-xs text-gray-500">
                          Ref: {{ invoice.invoice_number }}
                        </div>
                      </td>
                      <td class="px-6 py-4">
                        <div class="text-sm text-gray-900">{{ invoice.client_name }}</div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ formatDate(invoice.invoice_date) }}</div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ formatPrice(invoice.total) }}</div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <span
                          class="px-2 py-1 text-xs font-medium rounded-full"
                          :class="getStatusColorClass(invoice.status)"
                        >
                          {{ invoice.status_label }}
                        </span>
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

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Daily Sales Chart -->
          <Card>
            <CardHeader>
              <CardTitle>Ventas por Día</CardTitle>
            </CardHeader>
            <CardContent>
              <div v-if="dailySales.length > 0" class="space-y-2">
                <div
                  v-for="sale in dailySales.slice(0, 7)"
                  :key="sale.date"
                  class="flex items-center justify-between p-2 bg-gray-50 rounded"
                >
                  <div class="text-sm text-gray-900">{{ formatDate(sale.date) }}</div>
                  <div class="text-sm font-medium text-gray-900">
                    {{ formatPrice(sale.total_amount) }}
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-8 text-gray-500">
                <TrendingUp class="mx-auto h-12 w-12 text-gray-400" />
                <p class="mt-2 text-sm">No hay datos</p>
              </div>
            </CardContent>
          </Card>

          <!-- Sales by Client -->
          <Card>
            <CardHeader>
              <CardTitle>Ventas por Cliente</CardTitle>
            </CardHeader>
            <CardContent>
              <div v-if="salesByClient.length > 0" class="space-y-2">
                <div
                  v-for="client in salesByClient.slice(0, 5)"
                  :key="client.client_name"
                  class="flex items-center justify-between p-2 bg-gray-50 rounded"
                >
                  <div class="text-sm text-gray-900 truncate">{{ client.client_name }}</div>
                  <div class="text-sm font-medium text-gray-900">
                    {{ formatPrice(client.total_amount) }}
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-8 text-gray-500">
                <Users class="mx-auto h-12 w-12 text-gray-400" />
                <p class="mt-2 text-sm">No hay datos</p>
              </div>
            </CardContent>
          </Card>

          <!-- Export Options -->
          <Card>
            <CardHeader>
              <CardTitle>Opciones de Exportación</CardTitle>
            </CardHeader>
            <CardContent class="space-y-2">
              <button
                @click="exportReport"
                class="w-full px-4 py-2 bg-primary-700 text-white rounded-md hover:bg-primary-800 text-center transition-colors"
              >
                Exportar Excel
              </button>
              <button
                @click="printReport"
                class="w-full px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-center transition-colors"
              >
                Imprimir Reporte
              </button>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardHeader, CardTitle, CardContent } from '@/Components/ui'
import Pagination from '@/Components/Pagination.vue'
import {
  Receipt,
  DollarSign,
  CreditCard,
  Clock,
  TrendingUp,
  Users,
} from 'lucide-vue-next'

const props = defineProps({
  invoices: Object,
  clients: Array,
  statuses: Object,
  paymentStatuses: Object,
  periodStats: Object,
  dailySales: Array,
  salesByClient: Array,
  filters: Object,
})

const filters = ref({
  date_from: props.filters.date_from || '',
  date_to: props.filters.date_to || '',
  client_id: props.filters.client_id || '',
  status: props.filters.status || '',
  payment_status: props.filters.payment_status || '',
})

const applyFilters = () => {
  router.get('/reportes/ventas', filters.value, {
    preserveState: true,
    preserveScroll: true,
  })
}

const resetFilters = () => {
  filters.value = {
    date_from: '',
    date_to: '',
    client_id: '',
    status: '',
    payment_status: '',
  }
  applyFilters()
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

const exportReport = () => {
  const params = new URLSearchParams({
    type: 'sales',
    format: 'excel',
    ...filters.value
  })
  window.open(`/reportes/exportar?${params.toString()}`, '_blank')
}


const printReport = () => {
  window.print()
}
</script>
