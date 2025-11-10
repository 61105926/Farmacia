<template>
  <AdminLayout>
    <div class="p-6">
      <!-- Header -->
      <div class="mb-6 flex items-center justify-between">
        <div>
          <Link href="/reportes" class="text-sm text-primary-700 hover:text-primary-800 mb-2 inline-block">
            ← Volver a reportes
          </Link>
          <h1 class="text-2xl font-bold text-gray-900">Reporte de Clientes</h1>
          <p class="text-sm text-gray-600 mt-1">Análisis de clientes y gestión de cartera</p>
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
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <Card>
          <CardContent class="p-4">
            <div class="flex items-center">
              <div class="p-2 bg-blue-100 rounded-lg">
                <Users class="w-6 h-6 text-blue-600" />
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-500">Total Clientes</p>
                <p class="text-xl font-bold text-gray-900">{{ clientStats.totalClients || 0 }}</p>
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
                <p class="text-sm font-medium text-gray-500">Clientes Activos</p>
                <p class="text-xl font-bold text-gray-900">{{ clientStats.activeClients || 0 }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-4">
            <div class="flex items-center">
              <div class="p-2 bg-gray-100 rounded-lg">
                <XCircle class="w-6 h-6 text-gray-600" />
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-500">Clientes Inactivos</p>
                <p class="text-xl font-bold text-gray-900">{{ clientStats.inactiveClients || 0 }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-4">
            <div class="flex items-center">
              <div class="p-2 bg-yellow-100 rounded-lg">
                <AlertTriangle class="w-6 h-6 text-yellow-600" />
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-500">Con Deuda</p>
                <p class="text-xl font-bold text-gray-900">{{ clientStats.clientsWithDebt || 0 }}</p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Filters -->
      <Card class="mb-6">
        <CardContent class="p-4">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Buscar Cliente</label>
              <input
                v-model="filters.search"
                type="text"
                placeholder="Nombre, NIT..."
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                @input="debouncedSearch"
              />
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
                <option value="active">Activo</option>
                <option value="inactive">Inactivo</option>
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
                <option value="paid">Pagado</option>
                <option value="partial">Pago Parcial</option>
                <option value="unpaid">Sin Pagar</option>
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
        <!-- Clients Table -->
        <div class="lg:col-span-2">
          <Card>
            <CardHeader>
              <CardTitle>Clientes</CardTitle>
            </CardHeader>
            <CardContent class="p-0">
              <div class="overflow-x-auto">
                <table class="w-full">
                  <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Cliente
                      </th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Contacto
                      </th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Total Ventas
                      </th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Saldo Pendiente
                      </th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Estado
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="client in clients.data" :key="client.id" class="hover:bg-gray-50">
                      <td class="px-6 py-4">
                        <div>
                          <div class="text-sm font-medium text-gray-900">{{ client.business_name }}</div>
                          <div class="text-sm text-gray-500">{{ client.trade_name }}</div>
                          <div class="text-xs text-gray-400">{{ client.tax_id }}</div>
                        </div>
                      </td>
                      <td class="px-6 py-4">
                        <div>
                          <div class="text-sm text-gray-900">{{ client.email }}</div>
                          <div class="text-sm text-gray-500">{{ client.phone }}</div>
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">
                          {{ formatPrice(getClientTotalSales(client)) }}
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium" :class="getBalanceClass(getClientBalance(client))">
                          {{ formatPrice(getClientBalance(client)) }}
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <span
                          class="px-2 py-1 text-xs font-medium rounded-full"
                          :class="getStatusColorClass(client.status)"
                        >
                          {{ client.status === 'active' ? 'Activo' : 'Inactivo' }}
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Empty State -->
              <div v-if="clients.data.length === 0" class="text-center py-12">
                <Users class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-medium text-gray-900">No hay clientes</h3>
                <p class="mt-1 text-sm text-gray-500">No se encontraron clientes con los filtros aplicados.</p>
              </div>
            </CardContent>
          </Card>

          <!-- Pagination -->
          <div v-if="clients.data.length > 0" class="mt-6">
            <Pagination :links="clients.links" />
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Top Clients by Sales -->
          <Card>
            <CardHeader>
              <CardTitle>Top Clientes por Ventas</CardTitle>
            </CardHeader>
            <CardContent>
              <div v-if="topClientsBySales.length > 0" class="space-y-2">
                <div
                  v-for="(client, index) in topClientsBySales.slice(0, 5)"
                  :key="client.id"
                  class="flex items-center justify-between p-2 bg-gray-50 rounded"
                >
                  <div class="flex items-center">
                    <div class="w-6 h-6 bg-primary-100 rounded-full flex items-center justify-center">
                      <span class="text-xs font-medium text-primary-700">{{ index + 1 }}</span>
                    </div>
                    <div class="ml-2">
                      <div class="text-sm font-medium text-gray-900">{{ client.business_name }}</div>
                      <div class="text-xs text-gray-500">{{ client.invoice_count }} facturas</div>
                    </div>
                  </div>
                  <div class="text-right">
                    <div class="text-sm font-medium text-gray-900">
                      {{ formatPrice(client.total_sales) }}
                    </div>
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-8 text-gray-500">
                <TrendingUp class="mx-auto h-12 w-12 text-gray-400" />
                <p class="mt-2 text-sm">No hay datos</p>
              </div>
            </CardContent>
          </Card>

          <!-- Clients with Highest Balance -->
          <Card>
            <CardHeader>
              <CardTitle>Clientes con Mayor Saldo</CardTitle>
            </CardHeader>
            <CardContent>
              <div v-if="clientsWithHighestBalance.length > 0" class="space-y-2">
                <div
                  v-for="(client, index) in clientsWithHighestBalance.slice(0, 5)"
                  :key="client.id"
                  class="flex items-center justify-between p-2 bg-red-50 border border-red-200 rounded"
                >
                  <div class="flex items-center">
                    <div class="w-6 h-6 bg-red-100 rounded-full flex items-center justify-center">
                      <span class="text-xs font-medium text-red-700">{{ index + 1 }}</span>
                    </div>
                    <div class="ml-2">
                      <div class="text-sm font-medium text-gray-900">{{ client.business_name }}</div>
                    </div>
                  </div>
                  <div class="text-right">
                    <div class="text-sm font-medium text-gray-900">
                      {{ formatPrice(client.total_balance) }}
                    </div>
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-8 text-gray-500">
                <CheckCircle class="mx-auto h-12 w-12 text-gray-400" />
                <p class="mt-2 text-sm">No hay clientes con saldo pendiente</p>
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
import { useDebouncedRef } from '@/composables/useDebouncedRef'
import {
  Users,
  CheckCircle,
  XCircle,
  AlertTriangle,
  TrendingUp,
} from 'lucide-vue-next'

const props = defineProps({
  clients: Object,
  clientStats: Object,
  topClientsBySales: Array,
  clientsWithHighestBalance: Array,
  filters: Object,
})

const filters = ref({
  search: props.filters.search || '',
  status: props.filters.status || '',
  payment_status: props.filters.payment_status || '',
})

const debouncedSearch = useDebouncedRef(() => {
  applyFilters()
}, 500)

const applyFilters = () => {
  router.get('/reportes/clientes', filters.value, {
    preserveState: true,
    preserveScroll: true,
  })
}

const resetFilters = () => {
  filters.value = {
    search: '',
    status: '',
    payment_status: '',
  }
  applyFilters()
}

const getClientTotalSales = (client) => {
  return client.invoices?.reduce((sum, invoice) => {
    return invoice.status !== 'cancelled' ? sum + invoice.total : sum
  }, 0) || 0
}

const getClientBalance = (client) => {
  return client.invoices?.reduce((sum, invoice) => {
    return invoice.status !== 'cancelled' ? sum + invoice.balance : sum
  }, 0) || 0
}

const getStatusColorClass = (status) => {
  return status === 'active' 
    ? 'bg-green-100 text-green-800'
    : 'bg-gray-100 text-gray-800'
}

const getBalanceClass = (balance) => {
  if (balance === 0) return 'text-green-600'
  if (balance > 0) return 'text-red-600'
  return 'text-gray-900'
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
    type: 'clients',
    format: 'excel',
    ...filters.value
  })
  window.open(`/reportes/exportar?${params.toString()}`, '_blank')
}

</script>
