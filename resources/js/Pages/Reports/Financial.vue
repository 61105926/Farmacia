<template>
  <AdminLayout>
    <div class="p-6">
      <!-- Header -->
      <div class="mb-6 flex items-center justify-between">
        <div>
          <Link href="/reportes" class="text-sm text-primary-700 hover:text-primary-800 mb-2 inline-block">
            ← Volver a reportes
          </Link>
          <h1 class="text-2xl font-bold text-gray-900">Reporte Financiero</h1>
          <p class="text-sm text-gray-600 mt-1">Análisis de flujo de caja y situación financiera</p>
        </div>
        <div class="flex items-center gap-2">
          <button
            @click="exportReport"
            class="px-4 py-2 bg-primary-700 text-white rounded-md hover:bg-primary-800 transition-colors"
          >
            Exportar Excel
          </button>
          <button
            @click="exportCSV"
            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors"
          >
            Exportar CSV
          </button>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <Card>
          <CardContent class="p-4">
            <div class="flex items-center">
              <div class="p-2 bg-green-100 rounded-lg">
                <TrendingUp class="w-6 h-6 text-green-600" />
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-500">Ingresos Totales</p>
                <p class="text-xl font-bold text-gray-900">${{ formatPrice(financialStats.totalRevenue || 0) }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-4">
            <div class="flex items-center">
              <div class="p-2 bg-blue-100 rounded-lg">
                <CreditCard class="w-6 h-6 text-blue-600" />
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-500">Total Pagado</p>
                <p class="text-xl font-bold text-gray-900">${{ formatPrice(financialStats.totalPaid || 0) }}</p>
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
                <p class="text-xl font-bold text-gray-900">${{ formatPrice(financialStats.totalOutstanding || 0) }}</p>
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
                <p class="text-xl font-bold text-gray-900">${{ formatPrice(financialStats.overdueAmount || 0) }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-4">
            <div class="flex items-center">
              <div class="p-2 bg-purple-100 rounded-lg">
                <Calendar class="w-6 h-6 text-purple-600" />
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-500">Promedio Pago</p>
                <p class="text-xl font-bold text-gray-900">{{ financialStats.averagePaymentTime || 0 }} días</p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Filters -->
      <Card class="mb-6">
        <CardContent class="p-4">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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

            <!-- Quick Actions -->
            <div class="flex items-end">
              <button
                @click="resetFilters"
                class="w-full px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors"
              >
                Limpiar Filtros
              </button>
            </div>
          </div>
        </CardContent>
      </Card>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Cash Flow Chart -->
        <Card>
          <CardHeader>
            <CardTitle>Flujo de Caja por Mes</CardTitle>
          </CardHeader>
          <CardContent>
            <div v-if="cashFlow.length > 0" class="space-y-3">
              <div
                v-for="flow in cashFlow.slice(0, 6)"
                :key="`${flow.year}-${flow.month}`"
                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
              >
                <div>
                  <div class="text-sm font-medium text-gray-900">
                    {{ getMonthName(flow.month) }} {{ flow.year }}
                  </div>
                  <div class="text-xs text-gray-500">{{ flow.payment_count }} pagos</div>
                </div>
                <div class="text-right">
                  <div class="text-sm font-medium text-gray-900">
                    ${{ formatPrice(flow.total_amount) }}
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-8 text-gray-500">
              <TrendingUp class="mx-auto h-12 w-12 text-gray-400" />
              <p class="mt-2 text-sm">No hay datos de flujo de caja</p>
            </div>
          </CardContent>
        </Card>

        <!-- Aging Report -->
        <Card>
          <CardHeader>
            <CardTitle>Antigüedad de Saldos</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="space-y-3">
              <div class="flex items-center justify-between p-3 bg-green-50 border border-green-200 rounded-lg">
                <div class="text-sm font-medium text-gray-900">Al día</div>
                <div class="text-sm font-medium text-gray-900">${{ formatPrice(agingReport.current || 0) }}</div>
              </div>
              <div class="flex items-center justify-between p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                <div class="text-sm font-medium text-gray-900">1-30 días</div>
                <div class="text-sm font-medium text-gray-900">${{ formatPrice(agingReport['1-30'] || 0) }}</div>
              </div>
              <div class="flex items-center justify-between p-3 bg-orange-50 border border-orange-200 rounded-lg">
                <div class="text-sm font-medium text-gray-900">31-60 días</div>
                <div class="text-sm font-medium text-gray-900">${{ formatPrice(agingReport['31-60'] || 0) }}</div>
              </div>
              <div class="flex items-center justify-between p-3 bg-red-50 border border-red-200 rounded-lg">
                <div class="text-sm font-medium text-gray-900">61-90 días</div>
                <div class="text-sm font-medium text-gray-900">${{ formatPrice(agingReport['61-90'] || 0) }}</div>
              </div>
              <div class="flex items-center justify-between p-3 bg-red-100 border border-red-300 rounded-lg">
                <div class="text-sm font-medium text-gray-900">Más de 90 días</div>
                <div class="text-sm font-medium text-gray-900">${{ formatPrice(agingReport.over_90 || 0) }}</div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Payment Methods -->
        <Card>
          <CardHeader>
            <CardTitle>Métodos de Pago Más Utilizados</CardTitle>
          </CardHeader>
          <CardContent>
            <div v-if="paymentMethods.length > 0" class="space-y-3">
              <div
                v-for="method in paymentMethods.slice(0, 5)"
                :key="method.payment_method"
                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
              >
                <div>
                  <div class="text-sm font-medium text-gray-900">{{ getPaymentMethodLabel(method.payment_method) }}</div>
                  <div class="text-xs text-gray-500">{{ method.count }} pagos</div>
                </div>
                <div class="text-right">
                  <div class="text-sm font-medium text-gray-900">
                    ${{ formatPrice(method.total_amount) }}
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-8 text-gray-500">
              <CreditCard class="mx-auto h-12 w-12 text-gray-400" />
              <p class="mt-2 text-sm">No hay datos de métodos de pago</p>
            </div>
          </CardContent>
        </Card>

        <!-- Overdue Invoices -->
        <Card>
          <CardHeader>
            <CardTitle>Facturas Vencidas</CardTitle>
          </CardHeader>
          <CardContent>
            <div v-if="overdueInvoices.length > 0" class="space-y-2">
              <div
                v-for="invoice in overdueInvoices.slice(0, 5)"
                :key="invoice.id"
                class="flex items-center justify-between p-2 bg-red-50 border border-red-200 rounded"
              >
                <div>
                  <div class="text-sm font-medium text-gray-900">{{ invoice.invoice_number }}</div>
                  <div class="text-xs text-gray-500">{{ invoice.client_name }}</div>
                </div>
                <div class="text-right">
                  <div class="text-sm font-medium text-gray-900">${{ formatPrice(invoice.balance) }}</div>
                  <div class="text-xs text-gray-500">
                    {{ getDaysOverdue(invoice.due_date) }} días
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-8 text-gray-500">
              <CheckCircle class="mx-auto h-12 w-12 text-gray-400" />
              <p class="mt-2 text-sm">No hay facturas vencidas</p>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Export Options -->
      <Card class="mt-6">
        <CardHeader>
          <CardTitle>Opciones de Exportación</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="flex items-center gap-4">
            <button
              @click="exportReport"
              class="px-4 py-2 bg-primary-700 text-white rounded-md hover:bg-primary-800 transition-colors"
            >
              Exportar Excel
            </button>
            <button
              @click="exportCSV"
              class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors"
            >
              Exportar CSV
            </button>
            <button
              @click="printReport"
              class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors"
            >
              Imprimir Reporte
            </button>
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
import { Card, CardHeader, CardTitle, CardContent } from '@/Components/ui'
import {
  TrendingUp,
  CreditCard,
  Clock,
  AlertTriangle,
  Calendar,
  CheckCircle,
} from 'lucide-vue-next'

const props = defineProps({
  financialStats: Object,
  cashFlow: Array,
  agingReport: Object,
  paymentMethods: Array,
  overdueInvoices: Array,
  filters: Object,
})

const filters = ref({
  date_from: props.filters.date_from || '',
  date_to: props.filters.date_to || '',
})

const applyFilters = () => {
  router.get('/reportes/financiero', filters.value, {
    preserveState: true,
    preserveScroll: true,
  })
}

const resetFilters = () => {
  filters.value = {
    date_from: '',
    date_to: '',
  }
  applyFilters()
}

const getMonthName = (month) => {
  const months = [
    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
  ]
  return months[month - 1] || 'Mes'
}

const getPaymentMethodLabel = (method) => {
  const labels = {
    'cash': 'Efectivo',
    'transfer': 'Transferencia',
    'check': 'Cheque',
    'credit_card': 'Tarjeta de Crédito',
    'debit_card': 'Tarjeta de Débito',
    'other': 'Otro',
  }
  return labels[method] || method
}

const getDaysOverdue = (dueDate) => {
  const today = new Date()
  const due = new Date(dueDate)
  const diffTime = today - due
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  return Math.max(0, diffDays)
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

const exportReport = () => {
  const params = new URLSearchParams({
    type: 'financial',
    format: 'excel',
    ...filters.value
  })
  window.open(`/reportes/exportar?${params.toString()}`, '_blank')
}

const exportCSV = () => {
  const params = new URLSearchParams({
    type: 'financial',
    format: 'csv',
    ...filters.value
  })
  window.open(`/reportes/exportar?${params.toString()}`, '_blank')
}

const printReport = () => {
  window.print()
}
</script>
