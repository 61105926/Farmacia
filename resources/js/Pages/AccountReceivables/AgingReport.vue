<template>
  <AdminLayout>
    <div class="p-6">
      <!-- Header -->
      <div class="mb-6 flex items-center justify-between">
        <div>
          <Link href="/cuentas-por-cobrar" class="text-sm text-primary-700 hover:text-primary-800 mb-2 inline-block">
            ← Volver a cuentas por cobrar
          </Link>
          <h1 class="text-2xl font-bold text-gray-900">Reporte de Antigüedad</h1>
          <p class="text-sm text-gray-600 mt-1">Análisis de saldos por antigüedad</p>
        </div>
        <button
          @click="exportData"
          class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors"
        >
          Exportar
        </button>
      </div>

      <!-- Filters -->
      <Card class="mb-6">
        <CardContent class="p-4">
          <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Cliente</label>
              <select
                v-model="clientId"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
              >
                <option value="">Todos los clientes</option>
                <option v-for="client in clients" :key="client.id" :value="client.id">
                  {{ client.business_name }} - {{ client.trade_name }}
                </option>
              </select>
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

      <!-- Aging Summary -->
      <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <Card>
          <CardContent class="p-4">
            <p class="text-sm font-medium text-gray-500">Al Día</p>
            <p class="text-2xl font-bold text-green-600">{{ formatPrice(aging.current || 0) }}</p>
          </CardContent>
        </Card>
        <Card>
          <CardContent class="p-4">
            <p class="text-sm font-medium text-gray-500">1-30 Días</p>
            <p class="text-2xl font-bold text-yellow-600">{{ formatPrice(aging['1-30'] || 0) }}</p>
          </CardContent>
        </Card>
        <Card>
          <CardContent class="p-4">
            <p class="text-sm font-medium text-gray-500">31-60 Días</p>
            <p class="text-2xl font-bold text-orange-600">{{ formatPrice(aging['31-60'] || 0) }}</p>
          </CardContent>
        </Card>
        <Card>
          <CardContent class="p-4">
            <p class="text-sm font-medium text-gray-500">61-90 Días</p>
            <p class="text-2xl font-bold text-red-600">{{ formatPrice(aging['61-90'] || 0) }}</p>
          </CardContent>
        </Card>
        <Card>
          <CardContent class="p-4">
            <p class="text-sm font-medium text-gray-500">Más de 90 Días</p>
            <p class="text-2xl font-bold text-red-800">{{ formatPrice(aging.over_90 || 0) }}</p>
          </CardContent>
        </Card>
      </div>

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
                    Fecha Vencimiento
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Días Vencidos
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Saldo
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
                  <td class="px-6 py-4">
                    <div class="text-sm font-medium text-gray-900">{{ invoice.client_name }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ formatDate(invoice.due_date) }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium" :class="getDaysOverdueClass(invoice.due_date)">
                      {{ getDaysOverdue(invoice.due_date) }} días
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ formatPrice(invoice.balance) }}</div>
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
            <p class="mt-1 text-sm text-gray-500">No se encontraron facturas con saldo pendiente.</p>
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
  invoices: Array,
  aging: Object,
  clients: Array,
  filters: Object,
})

const clientId = ref(props.filters?.client_id || '')

const applyFilters = () => {
  router.get('/cuentas-por-cobrar/reporte-antiguedad', {
    client_id: clientId.value,
  })
}

const clearFilters = () => {
  clientId.value = ''
  router.get('/cuentas-por-cobrar/reporte-antiguedad')
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

const getDaysOverdue = (dueDate) => {
  if (!dueDate) return 0
  const due = new Date(dueDate)
  const today = new Date()
  if (due >= today) return 0
  const diffTime = today - due
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  return diffDays
}

const getDaysOverdueClass = (dueDate) => {
  const days = getDaysOverdue(dueDate)
  if (days === 0) return 'text-green-600'
  if (days <= 30) return 'text-yellow-600'
  if (days <= 60) return 'text-orange-600'
  if (days <= 90) return 'text-red-600'
  return 'text-red-800'
}

const exportData = () => {
  const params = new URLSearchParams()
  if (clientId.value) {
    params.append('client_id', clientId.value)
  }
  window.open(`/cuentas-por-cobrar/export?${params.toString()}`, '_blank')
}
</script>

