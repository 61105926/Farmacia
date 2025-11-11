<template>
  <AdminLayout>
    <!-- Page Header -->
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Gestión de Inventario</h1>
          <p class="text-sm text-gray-600 mt-1">Control de stock y movimientos de productos</p>
        </div>
        <div class="flex gap-2">
          <Link
            href="/productos"
            class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors"
          >
            Volver a Productos
          </Link>
          <button
            @click="showAdjustmentModal = true"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
          >
            Ajuste de Stock
          </button>
        </div>
      </div>
    </template>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
      <Card>
        <CardContent class="p-6">
          <div class="flex items-center">
            <Package class="h-8 w-8 text-blue-500" />
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Total Productos</p>
              <p class="text-2xl font-bold">{{ stats.total_products || 0 }}</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardContent class="p-6">
          <div class="flex items-center">
            <TrendingUp class="h-8 w-8 text-green-500" />
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Valor Total</p>
              <p class="text-2xl font-bold">{{ formatCurrency(stats.total_value || 0) }}</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardContent class="p-6">
          <div class="flex items-center">
            <AlertTriangle class="h-8 w-8 text-yellow-500" />
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Stock Bajo</p>
              <p class="text-2xl font-bold">{{ stats.low_stock || 0 }}</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardContent class="p-6">
          <div class="flex items-center">
            <XCircle class="h-8 w-8 text-red-500" />
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Sin Stock</p>
              <p class="text-2xl font-bold">{{ stats.out_of_stock || 0 }}</p>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Filters -->
    <Card class="mb-6">
      <CardContent class="p-6">
        <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Buscar Producto</label>
            <input
              v-model="filters.search"
              type="text"
              placeholder="Nombre, código o descripción..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
            />
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
            <select
              v-model="filters.category"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
              <option value="">Todas las categorías</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Estado de Stock</label>
            <select
              v-model="filters.stock_status"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
              <option value="">Todos</option>
              <option value="in_stock">En Stock</option>
              <option value="low_stock">Stock Bajo</option>
              <option value="out_of_stock">Sin Stock</option>
            </select>
          </div>

          <div class="flex items-end">
            <button
              type="submit"
              class="w-full px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 transition-colors"
            >
              <Search class="h-4 w-4 inline mr-2" />
              Filtrar
            </button>
          </div>
        </form>
      </CardContent>
    </Card>

    <!-- Products Table -->
    <Card>
      <CardContent class="p-0">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Producto
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Categoría
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Stock Actual
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Stock Mínimo
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
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Últimos Movimientos
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Acciones
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="product in products.data" :key="product.id">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div>
                    <div class="text-sm font-medium text-gray-900">{{ product.name }}</div>
                    <div class="text-sm text-gray-500">{{ product.code }}</div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ product.category?.name || 'Sin categoría' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  <div class="flex items-center">
                    <Package class="h-4 w-4 mr-2 text-gray-400" />
                    {{ product.stock_quantity || 0 }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ product.min_stock || 0 }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatCurrency(product.cost_price || 0) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatCurrency((product.stock_quantity || 0) * (product.cost_price || 0)) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <Badge :variant="getStockStatusVariant(product)">
                    {{ getStockStatus(product) }}
                  </Badge>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">
                  <div v-if="getProductMovements(product.id) && getProductMovements(product.id).length > 0" class="space-y-1">
                    <div 
                      v-for="(movement, idx) in getProductMovements(product.id).slice(0, 3)" 
                      :key="idx"
                      class="flex items-center gap-2 text-xs"
                    >
                      <span 
                        class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium"
                        :class="movement.transaction_type === 'in' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                      >
                        {{ movement.transaction_type === 'in' ? '+' : '-' }}{{ movement.quantity }}
                      </span>
                      <span class="text-gray-500">{{ formatDate(movement.movement_date) }}</span>
                      <span v-if="movement.movement_type_label" class="text-gray-400">
                        ({{ movement.movement_type_label }})
                      </span>
                    </div>
                    <div v-if="getProductMovements(product.id).length > 3" class="text-xs text-gray-400 italic">
                      +{{ getProductMovements(product.id).length - 3 }} más
                    </div>
                  </div>
                  <div v-else class="text-xs text-gray-400 italic">
                    Sin movimientos
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex items-center gap-2">
                    <button
                      @click="openAdjustmentModal(product)"
                      class="text-blue-600 hover:text-blue-900"
                      title="Ajustar Stock"
                    >
                      <TrendingUp class="h-4 w-4" />
                    </button>
                    <Link
                      :href="`/productos/${product.id}`"
                      class="text-gray-600 hover:text-gray-900"
                      title="Ver Detalles"
                    >
                      <Eye class="h-4 w-4" />
                    </Link>
                    <Link
                      :href="`/productos/${product.id}/editar`"
                      class="text-green-600 hover:text-green-900"
                      title="Editar"
                    >
                      <Edit class="h-4 w-4" />
                    </Link>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="products && products.links && products.links.length > 0" class="mt-6">
          <Pagination 
            :links="products.links" 
            :pagination-data="{
              from: products.from,
              to: products.to,
              total: products.total,
              current_page: products.current_page,
              last_page: products.last_page
            }"
          />
        </div>
      </CardContent>
    </Card>

    <!-- Stock Adjustment Modal -->
    <div v-if="showAdjustmentModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">
            {{ selectedProduct ? `Ajustar Stock - ${selectedProduct.name}` : 'Ajuste de Stock' }}
          </h3>
          
          <form @submit.prevent="submitStockAdjustment" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Movimiento</label>
              <select
                v-model="adjustmentForm.type"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
              >
                <option value="in">Entrada</option>
                <option value="out">Salida</option>
                <option value="adjustment">Ajuste</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Cantidad</label>
              <input
                v-model="adjustmentForm.quantity"
                type="number"
                min="1"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Motivo</label>
              <textarea
                v-model="adjustmentForm.reason"
                rows="3"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
                placeholder="Describe el motivo del ajuste..."
              ></textarea>
            </div>

            <div class="flex justify-end space-x-3">
              <button
                type="button"
                @click="closeAdjustmentModal"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors"
              >
                Cancelar
              </button>
              <button
                type="submit"
                class="px-4 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-md transition-colors"
              >
                Aplicar Ajuste
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Card from '@/Components/ui/Card.vue'
import CardContent from '@/Components/ui/CardContent.vue'
import Badge from '@/Components/ui/Badge.vue'
import Pagination from '@/Components/ui/Pagination.vue'
import { 
  Package, 
  Search, 
  TrendingUp, 
  AlertTriangle, 
  XCircle, 
  Eye, 
  Edit 
} from 'lucide-vue-next'

const props = defineProps({
  products: Object,
  categories: Array,
  stats: Object,
  filters: Object,
  recentMovements: Object
})

const showAdjustmentModal = ref(false)
const selectedProduct = ref(null)

const adjustmentForm = reactive({
  type: 'adjustment',
  quantity: 1,
  reason: ''
})

const filters = reactive({
  search: props.filters?.search || '',
  category: props.filters?.category || '',
  stock_status: props.filters?.stock_status || ''
})

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('es-BO', {
    style: 'currency',
    currency: 'BOB'
  }).format(amount)
}

const getStockStatus = (product) => {
  const stock = product.stock_quantity || 0
  const minStock = product.min_stock || 0
  
  if (stock <= 0) return 'Sin Stock'
  if (stock <= minStock) return 'Stock Bajo'
  return 'En Stock'
}

const getStockStatusVariant = (product) => {
  const stock = product.stock_quantity || 0
  const minStock = product.min_stock || 0
  
  if (stock <= 0) return 'error'
  if (stock <= minStock) return 'warning'
  return 'success'
}

const applyFilters = () => {
  router.get('/productos/inventario', filters, {
    preserveState: true,
    preserveScroll: true
  })
}

const openAdjustmentModal = (product) => {
  selectedProduct.value = product
  adjustmentForm.quantity = 1
  adjustmentForm.reason = ''
  showAdjustmentModal.value = true
}

const closeAdjustmentModal = () => {
  showAdjustmentModal.value = false
  selectedProduct.value = null
}

const submitStockAdjustment = () => {
  if (!selectedProduct.value) return
  
  router.post(`/productos/${selectedProduct.value.id}/ajustar-stock`, adjustmentForm, {
    onSuccess: () => {
      closeAdjustmentModal()
      router.reload({ only: ['products', 'recentMovements'] })
    }
  })
}

const getProductMovements = (productId) => {
  if (!props.recentMovements || !props.recentMovements[productId]) {
    return []
  }
  return props.recentMovements[productId]
}

const formatDate = (date) => {
  if (!date) return ''
  const d = new Date(date)
  return d.toLocaleDateString('es-BO', { 
    day: '2-digit', 
    month: '2-digit', 
    year: 'numeric' 
  })
}
</script>
