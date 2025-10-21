<template>
  <AdminLayout>
    <div class="p-6">
      <!-- Header -->
      <div class="mb-6 flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Reportes y Análisis</h1>
          <p class="text-sm text-gray-600 mt-1">Dashboard de métricas y análisis del negocio</p>
        </div>
        <div class="flex items-center gap-2">
          <button
            @click="exportAll"
            class="px-4 py-2 bg-primary-700 text-white rounded-md hover:bg-primary-800 transition-colors"
          >
            Exportar Todo
          </button>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
        <Card>
          <CardContent class="p-4">
            <div class="flex items-center">
              <div class="p-2 bg-blue-100 rounded-lg">
                <DollarSign class="w-6 h-6 text-blue-600" />
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-500">Total Ventas</p>
                <p class="text-xl font-bold text-gray-900">${{ formatPrice(stats.totalSales || 0) }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-4">
            <div class="flex items-center">
              <div class="p-2 bg-green-100 rounded-lg">
                <ShoppingCart class="w-6 h-6 text-green-600" />
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-500">Total Pedidos</p>
                <p class="text-xl font-bold text-gray-900">{{ stats.totalOrders || 0 }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-4">
            <div class="flex items-center">
              <div class="p-2 bg-purple-100 rounded-lg">
                <Users class="w-6 h-6 text-purple-600" />
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-500">Total Clientes</p>
                <p class="text-xl font-bold text-gray-900">{{ stats.totalClients || 0 }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-4">
            <div class="flex items-center">
              <div class="p-2 bg-orange-100 rounded-lg">
                <Package class="w-6 h-6 text-orange-600" />
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-500">Total Productos</p>
                <p class="text-xl font-bold text-gray-900">{{ stats.totalProducts || 0 }}</p>
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
                <p class="text-sm font-medium text-gray-500">Pagos Pendientes</p>
                <p class="text-xl font-bold text-gray-900">${{ formatPrice(stats.pendingPayments || 0) }}</p>
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
                <p class="text-sm font-medium text-gray-500">Facturas Vencidas</p>
                <p class="text-xl font-bold text-gray-900">{{ stats.overdueInvoices || 0 }}</p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Quick Actions -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <Link
          href="/reportes/ventas"
          class="block p-6 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
        >
          <div class="flex items-center">
            <div class="p-2 bg-blue-100 rounded-lg">
              <TrendingUp class="w-6 h-6 text-blue-600" />
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-gray-900">Reporte de Ventas</h3>
              <p class="text-xs text-gray-500">Análisis de ventas y facturación</p>
            </div>
          </div>
        </Link>

        <Link
          href="/reportes/inventario"
          class="block p-6 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
        >
          <div class="flex items-center">
            <div class="p-2 bg-green-100 rounded-lg">
              <Package class="w-6 h-6 text-green-600" />
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-gray-900">Reporte de Inventario</h3>
              <p class="text-xs text-gray-500">Stock y movimientos de productos</p>
            </div>
          </div>
        </Link>

        <Link
          href="/reportes/financiero"
          class="block p-6 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
        >
          <div class="flex items-center">
            <div class="p-2 bg-purple-100 rounded-lg">
              <BarChart3 class="w-6 h-6 text-purple-600" />
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-gray-900">Reporte Financiero</h3>
              <p class="text-xs text-gray-500">Flujo de caja y análisis financiero</p>
            </div>
          </div>
        </Link>

        <Link
          href="/reportes/clientes"
          class="block p-6 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
        >
          <div class="flex items-center">
            <div class="p-2 bg-orange-100 rounded-lg">
              <Users class="w-6 h-6 text-orange-600" />
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-gray-900">Reporte de Clientes</h3>
              <p class="text-xs text-gray-500">Análisis de clientes y cartera</p>
            </div>
          </div>
        </Link>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Ventas por Mes -->
        <Card>
          <CardHeader>
            <CardTitle>Ventas por Mes (Últimos 12 meses)</CardTitle>
          </CardHeader>
          <CardContent>
            <div v-if="monthlySales.length > 0" class="space-y-3">
              <div
                v-for="sale in monthlySales.slice(0, 6)"
                :key="`${sale.year}-${sale.month}`"
                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
              >
                <div>
                  <div class="text-sm font-medium text-gray-900">
                    {{ getMonthName(sale.month) }} {{ sale.year }}
                  </div>
                  <div class="text-xs text-gray-500">{{ sale.count }} facturas</div>
                </div>
                <div class="text-right">
                  <div class="text-sm font-medium text-gray-900">
                    ${{ formatPrice(sale.total) }}
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-8 text-gray-500">
              <TrendingUp class="mx-auto h-12 w-12 text-gray-400" />
              <p class="mt-2 text-sm">No hay datos de ventas</p>
            </div>
          </CardContent>
        </Card>

        <!-- Top Productos -->
        <Card>
          <CardHeader>
            <CardTitle>Top Productos Más Vendidos</CardTitle>
          </CardHeader>
          <CardContent>
            <div v-if="topProducts.length > 0" class="space-y-3">
              <div
                v-for="(product, index) in topProducts.slice(0, 5)"
                :key="product.code"
                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
              >
                <div class="flex items-center">
                  <div class="w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center">
                    <span class="text-xs font-medium text-primary-700">{{ index + 1 }}</span>
                  </div>
                  <div class="ml-3">
                    <div class="text-sm font-medium text-gray-900">{{ product.name }}</div>
                    <div class="text-xs text-gray-500">{{ product.code }}</div>
                  </div>
                </div>
                <div class="text-right">
                  <div class="text-sm font-medium text-gray-900">
                    {{ product.total_quantity }} unidades
                  </div>
                  <div class="text-xs text-gray-500">
                    ${{ formatPrice(product.total_amount) }}
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-8 text-gray-500">
              <Package class="mx-auto h-12 w-12 text-gray-400" />
              <p class="mt-2 text-sm">No hay datos de productos</p>
            </div>
          </CardContent>
        </Card>

        <!-- Top Clientes -->
        <Card>
          <CardHeader>
            <CardTitle>Top Clientes por Ventas</CardTitle>
          </CardHeader>
          <CardContent>
            <div v-if="topClients.length > 0" class="space-y-3">
              <div
                v-for="(client, index) in topClients.slice(0, 5)"
                :key="client.client_name"
                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
              >
                <div class="flex items-center">
                  <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                    <span class="text-xs font-medium text-green-700">{{ index + 1 }}</span>
                  </div>
                  <div class="ml-3">
                    <div class="text-sm font-medium text-gray-900">{{ client.client_name }}</div>
                    <div class="text-xs text-gray-500">{{ client.invoice_count }} facturas</div>
                  </div>
                </div>
                <div class="text-right">
                  <div class="text-sm font-medium text-gray-900">
                    ${{ formatPrice(client.total_amount) }}
                  </div>
                  <div class="text-xs text-gray-500">
                    Saldo: ${{ formatPrice(client.balance_amount) }}
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-8 text-gray-500">
              <Users class="mx-auto h-12 w-12 text-gray-400" />
              <p class="mt-2 text-sm">No hay datos de clientes</p>
            </div>
          </CardContent>
        </Card>

        <!-- Alertas y Notificaciones -->
        <Card>
          <CardHeader>
            <CardTitle>Alertas y Notificaciones</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="space-y-3">
              <div v-if="stats.overdueInvoices > 0" class="flex items-center p-3 bg-red-50 border border-red-200 rounded-lg">
                <AlertTriangle class="w-5 h-5 text-red-600" />
                <div class="ml-3">
                  <div class="text-sm font-medium text-red-900">
                    {{ stats.overdueInvoices }} facturas vencidas
                  </div>
                  <div class="text-xs text-red-600">
                    Requieren seguimiento inmediato
                  </div>
                </div>
              </div>

              <div v-if="stats.pendingPayments > 0" class="flex items-center p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                <Clock class="w-5 h-5 text-yellow-600" />
                <div class="ml-3">
                  <div class="text-sm font-medium text-yellow-900">
                    ${{ formatPrice(stats.pendingPayments) }} en pagos pendientes
                  </div>
                  <div class="text-xs text-yellow-600">
                    Seguimiento de cartera requerido
                  </div>
                </div>
              </div>

              <div class="flex items-center p-3 bg-blue-50 border border-blue-200 rounded-lg">
                <Info class="w-5 h-5 text-blue-600" />
                <div class="ml-3">
                  <div class="text-sm font-medium text-blue-900">
                    Sistema funcionando correctamente
                  </div>
                  <div class="text-xs text-blue-600">
                    Todos los módulos operativos
                  </div>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardHeader, CardTitle, CardContent } from '@/Components/ui'
import {
  DollarSign,
  ShoppingCart,
  Users,
  Package,
  Clock,
  AlertTriangle,
  TrendingUp,
  BarChart3,
  Info,
} from 'lucide-vue-next'

const props = defineProps({
  stats: Object,
  monthlySales: Array,
  topProducts: Array,
  topClients: Array,
})

const formatPrice = (price) => {
  return new Intl.NumberFormat('es-CO', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(price)
}

const getMonthName = (month) => {
  const months = [
    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
  ]
  return months[month - 1] || 'Mes'
}

const exportAll = () => {
  // Implementar exportación de todos los reportes
  const params = new URLSearchParams({
    type: 'all',
    format: 'excel'
  })
  window.open(`/reportes/exportar?${params.toString()}`, '_blank')
}
</script>
