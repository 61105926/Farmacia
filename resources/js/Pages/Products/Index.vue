<template>
  <AdminLayout>
    <!-- Page Header -->
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Gestión de Productos</h1>
          <p class="text-sm text-gray-600 mt-1">Administra tu inventario de productos</p>
        </div>
        <div class="flex gap-2">
          <Button
            v-if="can('products.create')"
            @click="$inertia.visit('/productos/crear')"
            class="bg-primary-600 hover:bg-primary-700"
          >
            <Plus class="h-4 w-4 mr-2" />
            Nuevo Producto
          </Button>
          <Button
            @click="openStockAdjustmentModal"
            variant="outline"
            class="border-orange-500 text-orange-600 hover:bg-orange-50"
          >
            <Package class="h-4 w-4 mr-2" />
            Ajustar Stock
          </Button>
        </div>
      </div>
    </template>

    <!-- Error Message -->
    <Alert v-if="error" variant="destructive" class="mb-6">
      <AlertCircle class="h-4 w-4" />
      <AlertTitle>Error</AlertTitle>
      <AlertDescription>{{ error }}</AlertDescription>
    </Alert>

    <!-- Statistics Cards -->
    <div v-if="stats" class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-6">
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
              <p class="text-sm font-medium text-gray-500">En Stock</p>
              <p class="text-2xl font-bold">{{ stats.in_stock_products || 0 }}</p>
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
              <p class="text-2xl font-bold">{{ stats.low_stock_products || 0 }}</p>
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
              <p class="text-2xl font-bold">{{ stats.out_of_stock_products || 0 }}</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardContent class="p-6">
          <div class="flex items-center">
            <DollarSign class="h-8 w-8 text-purple-500" />
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Valor Total</p>
              <p class="text-2xl font-bold">{{ formatCurrency(stats.total_value || 0) }}</p>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Filters -->
    <Card class="mb-6">
      <CardContent class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
          <div>
            <Label for="search">Buscar</Label>
            <Input
              id="search"
              v-model="filters.search"
              placeholder="Nombre, código o descripción..."
              @input="debouncedSearch"
            />
          </div>
          <div>
            <Label for="category">Categoría</Label>
            <select
              id="category"
              v-model="filters.category"
              @change="applyFilters"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
            >
              <option value="">Todas las categorías</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>
          </div>
          <div>
            <Label for="status">Estado</Label>
            <select
              id="status"
              v-model="filters.status"
              @change="applyFilters"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
            >
              <option value="">Todos</option>
              <option value="active">Activo</option>
              <option value="inactive">Inactivo</option>
            </select>
          </div>
          <div>
            <Label for="stock_status">Stock</Label>
            <select
              id="stock_status"
              v-model="filters.stock_status"
              @change="applyFilters"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
            >
              <option value="">Todos</option>
              <option value="in_stock">En Stock</option>
              <option value="low_stock">Stock Bajo</option>
              <option value="out_of_stock">Sin Stock</option>
            </select>
          </div>
          <div class="flex items-end gap-2">
            <Button
              @click="clearFilters"
              :disabled="isFiltering"
              variant="outline"
              class="flex-1 disabled:opacity-50"
            >
              <X class="h-4 w-4 mr-2" />
              {{ isFiltering ? 'Limpiando...' : 'Limpiar' }}
            </Button>
            <Button
              @click="applyFilters"
              :disabled="isFiltering"
              class="flex-1 bg-primary-600 hover:bg-primary-700 disabled:opacity-50"
            >
              <Search class="h-4 w-4 mr-2" />
              {{ isFiltering ? 'Aplicando...' : 'Filtrar' }}
            </Button>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Products Table -->
    <Card>
      <CardHeader>
        <CardTitle>Lista de Productos</CardTitle>
        <p class="text-sm text-gray-600">Gestiona tu inventario de productos</p>
      </CardHeader>
      <CardContent>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b">
                <th class="text-left py-3 px-4 font-medium text-gray-500">Producto</th>
                <th class="text-left py-3 px-4 font-medium text-gray-500">Código</th>
                <th class="text-left py-3 px-4 font-medium text-gray-500">Categoría</th>
                <th class="text-right py-3 px-4 font-medium text-gray-500">Stock</th>
                <th class="text-right py-3 px-4 font-medium text-gray-500">Precio</th>
                <th class="text-center py-3 px-4 font-medium text-gray-500">Estado</th>
                <th class="text-center py-3 px-4 font-medium text-gray-500">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="product in products.data" :key="product.id" class="border-b hover:bg-gray-50">
                <td class="py-3 px-4">
                  <div>
                    <div class="font-medium text-gray-900">{{ product.name }}</div>
                    <div class="text-sm text-gray-500">{{ product.brand || 'Sin marca' }}</div>
                  </div>
                </td>
                <td class="py-3 px-4">
                  <Badge variant="outline">{{ product.code }}</Badge>
                </td>
                <td class="py-3 px-4">
                  <span class="text-sm text-gray-600">{{ product.category?.name || 'Sin categoría' }}</span>
                </td>
                <td class="py-3 px-4 text-right">
                  <div class="flex items-center justify-end">
                    <span :class="getStockClass(product.stock_quantity, product.min_stock)">
                      {{ product.stock_quantity }}
                    </span>
                    <span class="text-xs text-gray-500 ml-1">/ {{ product.min_stock }}</span>
                  </div>
                </td>
                <td class="py-3 px-4 text-right">
                  <div class="text-sm font-medium">{{ formatCurrency(product.sale_price) }}</div>
                  <div class="text-xs text-gray-500">{{ formatCurrency(product.cost_price) }}</div>
                </td>
                <td class="py-3 px-4 text-center">
                  <Badge :variant="product.is_active ? 'default' : 'secondary'">
                    {{ product.is_active ? 'Activo' : 'Inactivo' }}
                  </Badge>
                </td>
                <td class="py-3 px-4 text-center">
                  <div class="flex items-center justify-center gap-2">
                    <Button
                      @click="adjustStock(product)"
                      variant="outline"
                      size="sm"
                      class="h-8 w-8 p-0"
                      title="Ajustar Stock"
                    >
                      <Package class="h-4 w-4" />
                    </Button>
                    <Button
                      @click="$inertia.visit(`/productos/${product.id}/historial-stock`)"
                      variant="outline"
                      size="sm"
                      class="h-8 w-8 p-0"
                      title="Historial de Stock"
                    >
                      <History class="h-4 w-4" />
                    </Button>
                    <Button
                      @click="$inertia.visit(`/productos/${product.id}`)"
                      variant="outline"
                      size="sm"
                      class="h-8 w-8 p-0"
                      title="Ver Detalles"
                    >
                      <Eye class="h-4 w-4" />
                    </Button>
                    <Button
                      v-if="can('products.edit')"
                      @click="$inertia.visit(`/productos/${product.id}/editar`)"
                      variant="outline"
                      size="sm"
                      class="h-8 w-8 p-0"
                      title="Editar"
                    >
                      <Edit class="h-4 w-4" />
                    </Button>
                    <Button
                      v-if="can('products.delete')"
                      @click="deleteProduct(product)"
                      variant="outline"
                      size="sm"
                      class="h-8 w-8 p-0 text-red-600 hover:text-red-700"
                      title="Eliminar"
                    >
                      <Trash2 class="h-4 w-4" />
                    </Button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="products.links" class="mt-6">
          <Pagination :links="products.links" />
        </div>
      </CardContent>
    </Card>

    <!-- Stock Adjustment Modal -->
    <div v-if="showStockAdjustment" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
        <h3 class="text-lg font-semibold mb-4">Ajustar Stock</h3>
        <p class="text-sm text-gray-600 mb-4">
          Selecciona el producto y el tipo de ajuste de stock.
        </p>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Producto</label>
            <select
              v-model="stockAdjustment.product_id"
              :disabled="stockAdjustment.productLocked"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
            >
              <option value="">Seleccionar producto</option>
              <option v-for="product in products.data" :key="product.id" :value="product.id">
                {{ product.name }} ({{ product.code }}) - Stock: {{ product.stock_quantity }}
              </option>
            </select>
            <p v-if="stockAdjustment.productLocked" class="text-xs text-gray-500 mt-1">
              Producto seleccionado: {{ getProductName(stockAdjustment.product_id) }}
            </p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Ajuste</label>
            <select
              v-model="stockAdjustment.type"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
            >
              <option value="">Seleccionar tipo</option>
              <option value="in">Entrada de Stock</option>
              <option value="out">Salida de Stock</option>
              <option value="adjustment">Ajuste Manual</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Cantidad</label>
            <input
              v-model.number="stockAdjustment.quantity"
              type="number"
              min="1"
              placeholder="Cantidad a ajustar"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Motivo</label>
            <textarea
              v-model="stockAdjustment.reason"
              placeholder="Motivo del ajuste de stock..."
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
            ></textarea>
          </div>
        </div>
        <div class="flex justify-end gap-2 mt-6">
          <button
            @click="showStockAdjustment = false"
            class="px-4 py-2 text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50"
          >
            Cancelar
          </button>
          <button
            @click="submitStockAdjustment"
            :disabled="!canSubmitStockAdjustment"
            class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 disabled:opacity-50"
          >
            Ajustar Stock
          </button>
        </div>
      </div>
    </div>

    <!-- Alert Dialog -->
    <AlertDialog
      :show="alertState.show"
      :type="alertState.type"
      :title="alertState.title"
      :message="alertState.message"
      :confirm-text="alertState.confirmText"
      @confirm="handleConfirm"
      @close="hideAlert"
    />
  </AdminLayout>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Card from '@/Components/ui/Card.vue'
import CardContent from '@/Components/ui/CardContent.vue'
import CardHeader from '@/Components/ui/CardHeader.vue'
import CardTitle from '@/Components/ui/CardTitle.vue'
import Button from '@/Components/ui/Button.vue'
import Input from '@/Components/ui/Input.vue'
import Label from '@/Components/ui/Label.vue'
import Badge from '@/Components/ui/Badge.vue'
import Alert from '@/Components/ui/Alert.vue'
import AlertDescription from '@/Components/ui/AlertDescription.vue'
import AlertTitle from '@/Components/ui/AlertTitle.vue'
import Pagination from '@/Components/ui/Pagination.vue'
import {
  Package,
  Plus,
  Eye,
  Edit,
  Trash2,
  AlertCircle,
  TrendingUp,
  AlertTriangle,
  XCircle,
  DollarSign,
  History,
  Search,
  X
} from 'lucide-vue-next'
import { usePermissions } from '@/composables/usePermissions'
import { useAlert } from '@/composables/useAlert'
import AlertDialog from '@/Components/ui/AlertDialog.vue'
import { debounce } from 'lodash-es'

const props = defineProps({
  products: Object,
  categories: Array,
  stats: Object,
  filters: Object,
  error: String
})

const { can } = usePermissions()
const { alertState, showConfirm, hideAlert, handleConfirm } = useAlert()

// Reactive data
const showStockAdjustment = ref(false)
const isFiltering = ref(false)
const stockAdjustment = reactive({
  product_id: '',
  type: '',
  quantity: 1,
  reason: '',
  productLocked: false
})

// Initialize filters from props or empty values
const filters = reactive({
  search: props.filters?.search || '',
  category: props.filters?.category || '',
  status: props.filters?.status || '',
  stock_status: props.filters?.stock_status || ''
})

// Log initial filters for debugging
console.log('Props recibidas:', props)
console.log('Filtros iniciales desde props:', props.filters)
console.log('Estado inicial de filtros reactive:', filters)

// Watch for props changes to sync filters
watch(() => props.filters, (newFilters) => {
  console.log('Props filters changed:', newFilters)
  if (newFilters !== undefined) {
    console.log('Updating reactive filters with:', newFilters)
    filters.search = newFilters.search || ''
    filters.category = newFilters.category || ''
    filters.status = newFilters.status || ''
    filters.stock_status = newFilters.stock_status || ''
    console.log('Reactive filters after update:', filters)
  }
}, { deep: true, immediate: true })

// Computed
const canSubmitStockAdjustment = computed(() => {
  return stockAdjustment.product_id && 
         stockAdjustment.type && 
         stockAdjustment.quantity > 0 && 
         stockAdjustment.reason.trim()
})

// Methods
const formatCurrency = (amount) => {
  return new Intl.NumberFormat('es-BO', {
    style: 'currency',
    currency: 'BOB'
  }).format(amount)
}

const getStockClass = (stock, minStock) => {
  if (stock <= 0) return 'text-red-600 font-medium'
  if (stock <= minStock) return 'text-yellow-600 font-medium'
  return 'text-green-600 font-medium'
}

const openStockAdjustmentModal = () => {
  stockAdjustment.product_id = ''
  stockAdjustment.type = ''
  stockAdjustment.quantity = 1
  stockAdjustment.reason = ''
  stockAdjustment.productLocked = false // NO bloquear producto, permitir selección
  showStockAdjustment.value = true
}

const adjustStock = (product) => {
  stockAdjustment.product_id = product.id
  stockAdjustment.type = ''
  stockAdjustment.quantity = 1
  stockAdjustment.reason = ''
  stockAdjustment.productLocked = true // Bloquear producto cuando se selecciona desde la lista
  showStockAdjustment.value = true
}

const getProductName = (productId) => {
  const product = props.products.data.find(p => p.id === productId)
  return product ? `${product.name} (${product.code})` : ''
}

const submitStockAdjustment = () => {
  if (!canSubmitStockAdjustment.value) return

  router.post(`/productos/${stockAdjustment.product_id}/ajustar-stock`, stockAdjustment, {
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => {
      showStockAdjustment.value = false
      // Recargar productos para actualizar UI en tiempo real
      router.reload({ only: ['products', 'stats'] })
      // Flash message will be handled by watcher
    },
    onError: (errors) => {
      console.error('Error:', errors)
      // Flash message will be handled by watcher
    }
  })
}

const deleteProduct = (product) => {
  showConfirm({
    title: 'Eliminar Producto',
    message: `¿Estás seguro de que quieres eliminar el producto "${product.name}"?`,
    confirmText: 'Eliminar',
    onConfirm: () => {
      router.delete(`/productos/${product.id}`, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
          // Flash message will be handled by watcher
        },
        onError: (errors) => {
          console.error('Error:', errors)
          // Flash message will be handled by watcher
        }
      })
    }
  })
}

// Debounced search
const debouncedSearch = debounce(() => {
  const searchParams = {}
  if (filters.search) searchParams.search = filters.search
  if (filters.category) searchParams.category = filters.category
  if (filters.status) searchParams.status = filters.status
  if (filters.stock_status) searchParams.stock_status = filters.stock_status

  console.log('Enviando búsqueda con parámetros:', searchParams)

  router.get('/productos', searchParams, {
    preserveState: true,
    replace: true
  })
}, 300)

// Apply filters immediately (manual filtering)
const applyFilters = () => {
  console.log('Aplicando filtros manualmente:', filters)
  
  isFiltering.value = true

  const currentFilters = {}
  
  // Incluir filtros solo si tienen valor
  if (filters.search && filters.search.trim()) {
    currentFilters.search = filters.search.trim()
  }
  // Para category, convertir a número si existe
  if (filters.category && filters.category !== '') {
    const categoryNum = parseInt(filters.category, 10)
    if (!isNaN(categoryNum)) {
      currentFilters.category = categoryNum
    }
  }
  // Para status y stock_status, enviar como string
  if (filters.status && filters.status !== '') {
    currentFilters.status = String(filters.status)
  }
  if (filters.stock_status && filters.stock_status !== '') {
    currentFilters.stock_status = String(filters.stock_status)
  }

  console.log('Filtros a enviar manualmente:', currentFilters)

  router.get('/productos', currentFilters, {
    preserveState: true,
    replace: true,
    onFinish: () => {
      isFiltering.value = false
    }
  })
}

// Clear all filters
const clearFilters = () => {
  console.log('Limpiando filtros')

  // Reset all filters
  filters.search = ''
  filters.category = ''
  filters.status = ''
  filters.stock_status = ''

  // Apply empty filters to show all products
  router.get('/productos', {}, {
    preserveState: true,
    replace: true
  })
}

// Removed automatic filter watcher - filters now only apply when:
// 1. Button "Filtrar" is clicked
// 2. Select dropdowns change (@change event)
// 3. Search input changes (debounced)

// Watch for flash messages
const page = usePage()
let lastFlashSuccess = null
let lastFlashError = null

watch(
  () => page.props.flash,
  (flash) => {
    if (flash?.success && flash.success !== lastFlashSuccess && flash.success.trim() !== '') {
      lastFlashSuccess = flash.success
      window.$notify?.success('Éxito', flash.success)
    }

    // Filtrar errores vacíos, arrays vacíos, objetos vacíos, y strings vacíos
    const hasValidError = flash?.error
      && flash.error !== lastFlashError
      && flash.error !== '[]'
      && flash.error !== '{}'
      && typeof flash.error === 'string'
      && flash.error.trim() !== ''

    if (hasValidError) {
      lastFlashError = flash.error
      window.$notify?.error('Error', flash.error)
    }
  },
  { deep: true }
)
</script>