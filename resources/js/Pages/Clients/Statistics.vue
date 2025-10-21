<template>
  <AdminLayout>
    <!-- Page Header -->
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Estadísticas del Cliente</h1>
          <p class="text-sm text-gray-600 mt-1">{{ client.business_name || client.trade_name }}</p>
        </div>
        <div class="flex gap-2">
          <Link
            :href="`/clientes/${client.id}`"
            class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors"
          >
            Ver Cliente
          </Link>
          <Link
            :href="`/clientes/${client.id}/editar`"
            class="px-4 py-2 bg-primary-700 text-white rounded-md hover:bg-primary-800 transition-colors"
          >
            Editar Cliente
          </Link>
        </div>
      </div>
    </template>

    <!-- Client Info Card -->
    <Card class="mb-6">
      <CardContent class="p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0 h-16 w-16">
            <div class="h-16 w-16 rounded-full bg-blue-100 flex items-center justify-center">
              <Building2 class="h-8 w-8 text-blue-600" />
            </div>
          </div>
          <div class="ml-6">
            <h2 class="text-xl font-semibold text-gray-900">
              {{ client.business_name || client.trade_name }}
            </h2>
            <p class="text-sm text-gray-500">{{ client.email || 'Sin email' }}</p>
            <p class="text-sm text-gray-500">{{ client.phone || 'Sin teléfono' }}</p>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
      <Card>
        <CardContent class="p-6">
          <div class="flex items-center">
            <ShoppingCart class="h-8 w-8 text-blue-500" />
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Total Ventas</p>
              <p class="text-2xl font-bold">{{ stats.total_sales || 0 }}</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardContent class="p-6">
          <div class="flex items-center">
            <DollarSign class="h-8 w-8 text-green-500" />
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Ingresos Totales</p>
              <p class="text-2xl font-bold">{{ formatCurrency(stats.total_revenue || 0) }}</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardContent class="p-6">
          <div class="flex items-center">
            <FileText class="h-8 w-8 text-purple-500" />
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Preventas</p>
              <p class="text-2xl font-bold">{{ stats.total_presales || 0 }}</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardContent class="p-6">
          <div class="flex items-center">
            <TrendingUp class="h-8 w-8 text-orange-500" />
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Promedio por Venta</p>
              <p class="text-2xl font-bold">{{ formatCurrency(stats.average_sale || 0) }}</p>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Monthly Sales Chart -->
    <Card class="mb-6">
      <CardContent class="p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Ventas por Mes (Últimos 6 meses)</h3>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mes</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ventas</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ingresos</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Preventas</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="month in monthlyStats" :key="month.month">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  {{ month.month }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ month.sales }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatCurrency(month.revenue) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ month.presales }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </CardContent>
    </Card>

    <!-- Recent Activity -->
    <Card>
      <CardContent class="p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Actividad Reciente</h3>
        <div v-if="recentActivity && recentActivity.length > 0" class="space-y-4">
          <div v-for="activity in recentActivity" :key="activity.id" class="flex items-center space-x-4">
            <div class="flex-shrink-0">
              <div class="h-8 w-8 rounded-full bg-gray-100 flex items-center justify-center">
                <component :is="getActivityIcon(activity.type)" class="h-4 w-4 text-gray-600" />
              </div>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-900">{{ activity.description }}</p>
              <p class="text-sm text-gray-500">{{ formatDate(activity.created_at) }}</p>
            </div>
            <div class="flex-shrink-0">
              <Badge :variant="getActivityVariant(activity.type)">
                {{ getActivityType(activity.type) }}
              </Badge>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-8">
          <Activity class="mx-auto h-12 w-12 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900">Sin actividad reciente</h3>
          <p class="mt-1 text-sm text-gray-500">
            No hay actividad registrada para este cliente.
          </p>
        </div>
      </CardContent>
    </Card>
  </AdminLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Card from '@/Components/ui/Card.vue'
import CardContent from '@/Components/ui/CardContent.vue'
import Badge from '@/Components/ui/Badge.vue'
import { 
  Building2, 
  ShoppingCart, 
  DollarSign, 
  FileText, 
  TrendingUp, 
  Activity,
  Receipt,
  CreditCard,
  Package
} from 'lucide-vue-next'

const props = defineProps({
  client: Object,
  stats: Object,
  monthlyStats: Array,
  recentActivity: Array
})

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('es-BO', {
    style: 'currency',
    currency: 'BOB'
  }).format(amount)
}

const formatDate = (date) => {
  if (!date) return 'Sin fecha'
  return new Date(date).toLocaleDateString('es-BO')
}

const getActivityIcon = (type) => {
  const icons = {
    'sale': ShoppingCart,
    'presale': FileText,
    'payment': CreditCard,
    'order': Package,
    'default': Activity
  }
  return icons[type] || icons.default
}

const getActivityVariant = (type) => {
  const variants = {
    'sale': 'success',
    'presale': 'info',
    'payment': 'primary',
    'order': 'warning',
    'default': 'secondary'
  }
  return variants[type] || variants.default
}

const getActivityType = (type) => {
  const types = {
    'sale': 'Venta',
    'presale': 'Preventa',
    'payment': 'Pago',
    'order': 'Pedido',
    'default': 'Actividad'
  }
  return types[type] || types.default
}
</script>
