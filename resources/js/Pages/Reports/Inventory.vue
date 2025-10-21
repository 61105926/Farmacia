<template>
  <AdminLayout>
    <div class="p-6">
      <!-- Header -->
      <div class="mb-6 flex items-center justify-between">
        <div>
          <Link href="/reportes" class="text-sm text-primary-700 hover:text-primary-800 mb-2 inline-block">
            ← Volver a reportes
          </Link>
          <h1 class="text-2xl font-bold text-gray-900">Reporte de Inventario</h1>
          <p class="text-sm text-gray-600 mt-1">Análisis de stock y movimientos de productos</p>
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
              <div class="p-2 bg-blue-100 rounded-lg">
                <Package class="w-6 h-6 text-blue-600" />
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-500">Total Productos</p>
                <p class="text-xl font-bold text-gray-900">{{ inventoryStats.totalProducts || 0 }}</p>
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
                <p class="text-sm font-medium text-gray-500">Productos Activos</p>
                <p class="text-xl font-bold text-gray-900">{{ inventoryStats.activeProducts || 0 }}</p>
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
                <p class="text-sm font-medium text-gray-500">Stock Bajo</p>
                <p class="text-xl font-bold text-gray-900">{{ inventoryStats.lowStockProducts || 0 }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-4">
            <div class="flex items-center">
              <div class="p-2 bg-red-100 rounded-lg">
                <XCircle class="w-6 h-6 text-red-600" />
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-500">Sin Stock</p>
                <p class="text-xl font-bold text-gray-900">{{ inventoryStats.outOfStockProducts || 0 }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-4">
            <div class="flex items-center">
              <div class="p-2 bg-purple-100 rounded-lg">
                <DollarSign class="w-6 h-6 text-purple-600" />
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-500">Valor Total Stock</p>
                <p class="text-xl font-bold text-gray-900">${{ formatPrice(inventoryStats.totalStockValue || 0) }}</p>
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
              <label class="block text-sm font-medium text-gray-700 mb-1">Buscar Producto</label>
              <input
                v-model="filters.search"
                type="text"
                placeholder="Nombre o código..."
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                @input="debouncedSearch"
              />
            </div>

            <!-- Category Filter -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
              <select
                v-model="filters.category_id"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                @change="applyFilters"
              >
                <option value="">Todas las categorías</option>
                <option v-for="category in categories" :key="category.id" :value="category.id">
                  {{ category.name }}
                </option>
              </select>
            </div>

            <!-- Stock Status Filter -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Estado Stock</label>
              <select
                v-model="filters.stock_status"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                @change="applyFilters"
              >
                <option value="">Todos</option>
                <option value="low">Stock Bajo</option>
                <option value="out">Sin Stock</option>
                <option value="available">Con Stock</option>
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
        <!-- Products Table -->
        <div class="lg:col-span-2">
          <Card>
            <CardHeader>
              <CardTitle>Productos</CardTitle>
            </CardHeader>
            <CardContent class="p-0">
              <div class="overflow-x-auto">
                <table class="w-full">
                  <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Producto
                      </th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Categoría
                      </th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Stock
                      </th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Precio Costo
                      </th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Valor Stock
                      </th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Estado
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="product in products.data" :key="product.id" class="hover:bg-gray-50">
                      <td class="px-6 py-4">
                        <div>
                          <div class="text-sm font-medium text-gray-900">{{ product.name }}</div>
                          <div class="text-sm text-gray-500">{{ product.code }}</div>
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ product.category?.name || 'N/A' }}</div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ product.stock_quantity }}</div>
                        <div class="text-xs text-gray-500">Reorden: {{ product.reorder_point }}</div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">${{ formatPrice(product.cost_price) }}</div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">
                          ${{ formatPrice(product.stock_quantity * product.cost_price) }}
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <span
                          class="px-2 py-1 text-xs font-medium rounded-full"
                          :class="getStockStatusClass(product)"
                        >
                          {{ getStockStatusLabel(product) }}
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Empty State -->
              <div v-if="products.data.length === 0" class="text-center py-12">
                <Package class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-medium text-gray-900">No hay productos</h3>
                <p class="mt-1 text-sm text-gray-500">No se encontraron productos con los filtros aplicados.</p>
              </div>
            </CardContent>
          </Card>

          <!-- Pagination -->
          <div v-if="products.data.length > 0" class="mt-6">
            <Pagination :links="products.links" />
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Low Stock Products -->
          <Card>
            <CardHeader>
              <CardTitle>Productos con Stock Bajo</CardTitle>
            </CardHeader>
            <CardContent>
              <div v-if="lowStockProducts.length > 0" class="space-y-2">
                <div
                  v-for="product in lowStockProducts.slice(0, 5)"
                  :key="product.id"
                  class="flex items-center justify-between p-2 bg-yellow-50 border border-yellow-200 rounded"
                >
                  <div>
                    <div class="text-sm font-medium text-gray-900">{{ product.name }}</div>
                    <div class="text-xs text-gray-500">{{ product.code }}</div>
                  </div>
                  <div class="text-right">
                    <div class="text-sm font-medium text-gray-900">{{ product.stock_quantity }}</div>
                    <div class="text-xs text-gray-500">Reorden: {{ product.reorder_point }}</div>
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-8 text-gray-500">
                <CheckCircle class="mx-auto h-12 w-12 text-gray-400" />
                <p class="mt-2 text-sm">No hay productos con stock bajo</p>
              </div>
            </CardContent>
          </Card>

          <!-- Expiring Products -->
          <Card>
            <CardHeader>
              <CardTitle>Productos por Vencer</CardTitle>
            </CardHeader>
            <CardContent>
              <div v-if="expiringProducts.length > 0" class="space-y-2">
                <div
                  v-for="movement in expiringProducts.slice(0, 5)"
                  :key="movement.id"
                  class="flex items-center justify-between p-2 bg-orange-50 border border-orange-200 rounded"
                >
                  <div>
                    <div class="text-sm font-medium text-gray-900">{{ movement.product?.name }}</div>
                    <div class="text-xs text-gray-500">{{ movement.batch_number }}</div>
                  </div>
                  <div class="text-right">
                    <div class="text-sm font-medium text-gray-900">
                      {{ formatDate(movement.expiry_date) }}
                    </div>
                    <div class="text-xs text-gray-500">
                      {{ getDaysUntilExpiry(movement.expiry_date) }} días
                    </div>
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-8 text-gray-500">
                <Calendar class="mx-auto h-12 w-12 text-gray-400" />
                <p class="mt-2 text-sm">No hay productos próximos a vencer</p>
              </div>
            </CardContent>
          </Card>

          <!-- Recent Movements -->
          <Card>
            <CardHeader>
              <CardTitle>Movimientos Recientes</CardTitle>
            </CardHeader>
            <CardContent>
              <div v-if="recentMovements.length > 0" class="space-y-2">
                <div
                  v-for="movement in recentMovements.slice(0, 5)"
                  :key="movement.id"
                  class="flex items-center justify-between p-2 bg-gray-50 rounded"
                >
                  <div>
                    <div class="text-sm font-medium text-gray-900">{{ movement.product?.name }}</div>
                    <div class="text-xs text-gray-500">{{ movement.movement_type_label }}</div>
                  </div>
                  <div class="text-right">
                    <div class="text-sm font-medium text-gray-900">
                      {{ movement.quantity > 0 ? '+' : '' }}{{ movement.quantity }}
                    </div>
                    <div class="text-xs text-gray-500">
                      {{ formatDate(movement.movement_date) }}
                    </div>
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-8 text-gray-500">
                <Activity class="mx-auto h-12 w-12 text-gray-400" />
                <p class="mt-2 text-sm">No hay movimientos recientes</p>
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
                @click="exportCSV"
                class="w-full px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-center transition-colors"
              >
                Exportar CSV
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
import { useDebouncedRef } from '@/Composables/useDebouncedRef'
import {
  Package,
  CheckCircle,
  AlertTriangle,
  XCircle,
  DollarSign,
  Calendar,
  Activity,
} from 'lucide-vue-next'

const props = defineProps({
  products: Object,
  categories: Array,
  inventoryStats: Object,
  recentMovements: Array,
  lowStockProducts: Array,
  expiringProducts: Array,
  filters: Object,
})

const filters = ref({
  search: props.filters.search || '',
  category_id: props.filters.category_id || '',
  stock_status: props.filters.stock_status || '',
})

const debouncedSearch = useDebouncedRef(() => {
  applyFilters()
}, 500)

const applyFilters = () => {
  router.get('/reportes/inventario', filters.value, {
    preserveState: true,
    preserveScroll: true,
  })
}

const resetFilters = () => {
  filters.value = {
    search: '',
    category_id: '',
    stock_status: '',
  }
  applyFilters()
}

const getStockStatusClass = (product) => {
  if (product.stock_quantity === 0) return 'bg-red-100 text-red-800'
  if (product.stock_quantity <= product.reorder_point) return 'bg-yellow-100 text-yellow-800'
  return 'bg-green-100 text-green-800'
}

const getStockStatusLabel = (product) => {
  if (product.stock_quantity === 0) return 'Sin Stock'
  if (product.stock_quantity <= product.reorder_point) return 'Stock Bajo'
  return 'Disponible'
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

const getDaysUntilExpiry = (expiryDate) => {
  const today = new Date()
  const expiry = new Date(expiryDate)
  const diffTime = expiry - today
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  return diffDays
}

const exportReport = () => {
  const params = new URLSearchParams({
    type: 'inventory',
    format: 'excel',
    ...filters.value
  })
  window.open(`/reportes/exportar?${params.toString()}`, '_blank')
}

const exportCSV = () => {
  const params = new URLSearchParams({
    type: 'inventory',
    format: 'csv',
    ...filters.value
  })
  window.open(`/reportes/exportar?${params.toString()}`, '_blank')
}
</script>
