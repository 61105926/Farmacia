<template>
  <AdminLayout>
    <!-- Page Header -->
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
          <p class="text-gray-600 mt-1">Panel de control - Farmacia Pando</p>
        </div>
        <div class="flex items-center space-x-4">
          <Badge variant="success" size="md">
            <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
            Sistema Activo
          </Badge>
          <div class="text-sm text-gray-500">
            {{ new Date().toLocaleDateString('es-BO') }}
          </div>
        </div>
      </div>
    </template>

    <!-- Dashboard Content -->
    <div class="space-y-6">
      <!-- Error Message -->
      <div v-if="error" class="bg-red-50 border border-red-200 rounded-md p-4">
        <div class="flex">
          <AlertCircle class="h-5 w-5 text-red-400" />
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">Error</h3>
            <div class="mt-2 text-sm text-red-700">{{ error }}</div>
          </div>
        </div>
      </div>

      <!-- Welcome Message -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center">
            <User class="h-5 w-5 mr-2" />
            Bienvenido, {{ user.name }}
          </CardTitle>
        </CardHeader>
        <CardContent>
          <p class="text-gray-600 mb-4">
            Aquí tienes un resumen completo de la actividad del sistema:
          </p>
          <div class="flex items-center space-x-4 text-sm text-gray-500">
            <span>Rol: {{ user.roles?.[0]?.name || 'Sin rol asignado' }}</span>
            <span>•</span>
            <span>Último acceso: {{ formatDate(user.last_login_at) }}</span>
          </div>
        </CardContent>
      </Card>

      <!-- Alerts -->
      <div v-if="alerts && alerts.length > 0" class="space-y-3">
        <h3 class="text-lg font-semibold text-gray-900">Alertas del Sistema</h3>
        <div v-for="alert in alerts" :key="alert.title" 
             :class="getAlertClasses(alert.type)"
             class="p-4 rounded-md border">
          <div class="flex items-center">
            <AlertTriangle v-if="alert.type === 'error'" class="h-5 w-5 mr-3 text-red-500" />
            <AlertCircle v-else-if="alert.type === 'warning'" class="h-5 w-5 mr-3 text-yellow-500" />
            <Info v-else class="h-5 w-5 mr-3 text-blue-500" />
            <div class="flex-1">
              <h4 class="font-medium">{{ alert.title }}</h4>
              <p class="text-sm mt-1">{{ alert.message }}</p>
            </div>
            <Link v-if="alert.action" :href="alert.action" 
                  class="text-sm font-medium underline">
              Ver detalles
            </Link>
          </div>
        </div>
      </div>

      <!-- Performance Metrics -->
      <div v-if="performanceMetrics" class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <Card>
          <CardContent class="p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-500">Crecimiento de Ingresos</p>
                <p class="text-2xl font-bold" :class="(performanceMetrics?.revenue_growth || 0) >= 0 ? 'text-green-600' : 'text-red-600'">
                  {{ (performanceMetrics?.revenue_growth || 0) >= 0 ? '+' : '' }}{{ performanceMetrics?.revenue_growth || 0 }}%
                </p>
              </div>
              <TrendingUp v-if="(performanceMetrics?.revenue_growth || 0) >= 0" class="h-8 w-8 text-green-500" />
              <TrendingDown v-else class="h-8 w-8 text-red-500" />
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-500">Crecimiento de Órdenes</p>
                <p class="text-2xl font-bold" :class="(performanceMetrics?.order_growth || 0) >= 0 ? 'text-green-600' : 'text-red-600'">
                  {{ (performanceMetrics?.order_growth || 0) >= 0 ? '+' : '' }}{{ performanceMetrics?.order_growth || 0 }}%
                </p>
              </div>
              <TrendingUp v-if="(performanceMetrics?.order_growth || 0) >= 0" class="h-8 w-8 text-green-500" />
              <TrendingDown v-else class="h-8 w-8 text-red-500" />
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-500">Crecimiento de Clientes</p>
                <p class="text-2xl font-bold" :class="(performanceMetrics?.client_growth || 0) >= 0 ? 'text-green-600' : 'text-red-600'">
                  {{ (performanceMetrics?.client_growth || 0) >= 0 ? '+' : '' }}{{ performanceMetrics?.client_growth || 0 }}%
                </p>
              </div>
              <TrendingUp v-if="(performanceMetrics?.client_growth || 0) >= 0" class="h-8 w-8 text-green-500" />
              <TrendingDown v-else class="h-8 w-8 text-red-500" />
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Quick Stats -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <Card>
          <CardContent class="p-6">
            <div class="flex items-center">
              <Users class="h-8 w-8 text-blue-500" />
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Usuarios</p>
                <p class="text-2xl font-bold">{{ stats?.users || 0 }}</p>
                <p class="text-xs text-gray-400">Total registrados</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-6">
            <div class="flex items-center">
              <Building2 class="h-8 w-8 text-green-500" />
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Clientes</p>
                <p class="text-2xl font-bold">{{ stats?.clients || 0 }}</p>
                <p class="text-xs text-gray-400">Total registrados</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-6">
            <div class="flex items-center">
              <Package class="h-8 w-8 text-purple-500" />
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Productos</p>
                <p class="text-2xl font-bold">{{ stats?.products || 0 }}</p>
                <p class="text-xs text-gray-400">Activos</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-6">
            <div class="flex items-center">
              <ShoppingCart class="h-8 w-8 text-orange-500" />
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Ventas</p>
                <p class="text-2xl font-bold">{{ stats?.sales || 0 }}</p>
                <p class="text-xs text-gray-400">Total realizadas</p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Monthly Stats -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <Card>
          <CardHeader>
            <CardTitle class="text-lg">Este Mes</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="space-y-4">
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Preventas:</span>
                <span class="font-semibold">{{ monthlyStats?.presales || 0 }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Ventas:</span>
                <span class="font-semibold">{{ monthlyStats?.sales || 0 }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Ingresos:</span>
                <span class="font-semibold">{{ formatCurrency(monthlyStats?.revenue || 0) }}</span>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle class="text-lg">Estado de Clientes</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="space-y-4">
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Activos:</span>
                <span class="font-semibold text-green-600">{{ clientStats?.active || 0 }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Inactivos:</span>
                <span class="font-semibold text-yellow-600">{{ clientStats?.inactive || 0 }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Bloqueados:</span>
                <span class="font-semibold text-red-600">{{ clientStats?.blocked || 0 }}</span>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle class="text-lg">Estado de Productos</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="space-y-4">
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Total:</span>
                <span class="font-semibold">{{ productStats?.total || 0 }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Stock Bajo:</span>
                <span class="font-semibold text-yellow-600">{{ productStats?.low_stock || 0 }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Sin Stock:</span>
                <span class="font-semibold text-red-600">{{ productStats?.out_of_stock || 0 }}</span>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Sales Data Table -->
      <Card>
        <CardHeader>
          <CardTitle>Tendencias de Ventas (6 meses)</CardTitle>
        </CardHeader>
        <CardContent>
          <div v-if="salesChart && salesChart.length > 0" class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mes</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Preventas</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ventas</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ingresos</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="item in salesChart" :key="item.month" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ item.month }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ item.presales || 0 }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ item.sales || 0 }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatCurrency(item.revenue || 0) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-else class="text-center py-8 text-gray-500">
            No hay datos de ventas disponibles
          </div>
        </CardContent>
      </Card>

      <!-- Order Status Summary -->
      <Card>
        <CardHeader>
          <CardTitle>Estado de Órdenes</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="text-center p-4 bg-yellow-50 rounded-lg">
              <div class="text-2xl font-bold text-yellow-600">{{ orderStats?.pending || 0 }}</div>
              <div class="text-sm text-yellow-800">Pendientes</div>
            </div>
            <div class="text-center p-4 bg-blue-50 rounded-lg">
              <div class="text-2xl font-bold text-blue-600">{{ orderStats?.confirmed || 0 }}</div>
              <div class="text-sm text-blue-800">Confirmadas</div>
            </div>
            <div class="text-center p-4 bg-purple-50 rounded-lg">
              <div class="text-2xl font-bold text-purple-600">{{ orderStats?.processing || 0 }}</div>
              <div class="text-sm text-purple-800">Procesando</div>
            </div>
            <div class="text-center p-4 bg-green-50 rounded-lg">
              <div class="text-2xl font-bold text-green-600">{{ orderStats?.delivered || 0 }}</div>
              <div class="text-sm text-green-800">Entregadas</div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Recent Activity -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Orders -->
        <Card>
          <CardHeader>
            <CardTitle>Órdenes Recientes</CardTitle>
          </CardHeader>
          <CardContent>
            <div v-if="recentOrders && recentOrders.length > 0" class="space-y-3">
              <div v-for="order in recentOrders" :key="order.id" 
                   class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
                <div>
                  <p class="font-medium">{{ order.client?.business_name || 'Cliente' }}</p>
                  <p class="text-sm text-gray-600">{{ order.salesperson?.name || 'Sin vendedor' }}</p>
                </div>
                <div class="text-right">
                  <Badge :variant="getOrderStatusVariant(order.status)">
                    {{ getOrderStatusText(order.status) }}
                  </Badge>
                  <p class="text-sm text-gray-500 mt-1">{{ formatDate(order.created_at) }}</p>
                </div>
              </div>
            </div>
            <div v-else class="text-center text-gray-500 py-8">
              No hay órdenes recientes
            </div>
          </CardContent>
        </Card>

        <!-- Top Clients -->
        <Card>
          <CardHeader>
            <CardTitle>Clientes Principales</CardTitle>
          </CardHeader>
          <CardContent>
            <div v-if="topClients && topClients.length > 0" class="space-y-3">
              <div v-for="client in topClients" :key="client.id" 
                   class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
                <div>
                  <p class="font-medium">{{ client.business_name }}</p>
                  <p class="text-sm text-gray-600">{{ client.trade_name }}</p>
                </div>
                <div class="text-right">
                  <p class="font-semibold">{{ client.orders_count }} órdenes</p>
                  <p class="text-sm text-gray-500">{{ client.invoices_count }} facturas</p>
                </div>
              </div>
            </div>
            <div v-else class="text-center text-gray-500 py-8">
              No hay datos de clientes
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Quick Actions -->
      <Card>
        <CardHeader>
          <CardTitle>Acciones Rápidas</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <Link href="/preventas/crear" 
                  class="flex flex-col items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
              <FileText class="h-8 w-8 text-blue-600 mb-2" />
              <span class="text-sm font-medium text-blue-900">Nueva Preventa</span>
            </Link>
            
            <Link href="/ventas/crear" 
                  class="flex flex-col items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
              <ShoppingCart class="h-8 w-8 text-green-600 mb-2" />
              <span class="text-sm font-medium text-green-900">Nueva Venta</span>
            </Link>
            
            <Link href="/clientes/crear" 
                  class="flex flex-col items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
              <Building2 class="h-8 w-8 text-purple-600 mb-2" />
              <span class="text-sm font-medium text-purple-900">Nuevo Cliente</span>
            </Link>
            
            <Link href="/usuarios/crear" 
                  class="flex flex-col items-center p-4 bg-orange-50 rounded-lg hover:bg-orange-100 transition-colors">
              <Users class="h-8 w-8 text-orange-600 mb-2" />
              <span class="text-sm font-medium text-orange-900">Nuevo Usuario</span>
            </Link>
          </div>
        </CardContent>
      </Card>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue'
import { Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Card from '@/Components/ui/Card.vue'
import CardHeader from '@/Components/ui/CardHeader.vue'
import CardTitle from '@/Components/ui/CardTitle.vue'
import CardContent from '@/Components/ui/CardContent.vue'
import Badge from '@/Components/ui/Badge.vue'
import { 
  Users, Building2, Package, ShoppingCart, User, 
  AlertCircle, AlertTriangle, Info, TrendingUp, TrendingDown,
  FileText
} from 'lucide-vue-next'

const props = defineProps({
  user: Object,
  stats: Object,
  monthlyStats: Object,
  orderStats: Object,
  clientStats: Object,
  salesChart: Array,
  recentOrders: Array,
  topClients: Array,
  productStats: Object,
  userStats: Object,
  alerts: Array,
  performanceMetrics: Object,
  error: String
})

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('es-BO', {
    style: 'currency',
    currency: 'BOB',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(amount || 0)
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('es-BO')
}

const getAlertClasses = (type) => {
  const classes = {
    error: 'bg-red-50 border-red-200',
    warning: 'bg-yellow-50 border-yellow-200',
    info: 'bg-blue-50 border-blue-200'
  }
  return classes[type] || classes.info
}

const getOrderStatusVariant = (status) => {
  const variants = {
    pending: 'warning',
    confirmed: 'info',
    processing: 'primary',
    delivered: 'success',
    cancelled: 'danger'
  }
  return variants[status] || 'secondary'
}

const getOrderStatusText = (status) => {
  const texts = {
    pending: 'Pendiente',
    confirmed: 'Confirmada',
    processing: 'Procesando',
    delivered: 'Entregada',
    cancelled: 'Cancelada'
  }
  return texts[status] || status
}
</script>