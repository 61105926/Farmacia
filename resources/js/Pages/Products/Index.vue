<template>
  <AdminLayout>
    <!-- Page Header -->
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Productos</h1>
          <p class="text-sm text-gray-600 mt-1">Gestión de inventario de productos</p>
        </div>
        <div class="flex gap-2">
          <Link
            v-if="can('products.create')"
            href="/productos/crear"
            class="px-4 py-2 bg-primary-700 text-white rounded-md hover:bg-primary-800 transition-colors"
          >
            Nuevo Producto
          </Link>
          <Link
            href="/productos/stock-bajo"
            class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 transition-colors"
          >
            Stock Bajo
          </Link>
          <Link
            href="/productos/sin-stock"
            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors"
          >
            Sin Stock
          </Link>
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
            <CheckCircle class="h-8 w-8 text-green-500" />
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Activos</p>
              <p class="text-2xl font-bold">{{ stats.active_products || 0 }}</p>
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
        <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
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
            <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
            <select
              v-model="filters.status"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
              <option value="">Todos</option>
              <option value="active">Activos</option>
              <option value="inactive">Inactivos</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Stock</label>
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
          
          <div class="md:col-span-4 flex gap-2">
            <button
              type="submit"
              class="px-4 py-2 bg-primary-700 text-white rounded-md hover:bg-primary-800 transition-colors"
            >
              Filtrar
            </button>
            <button
              type="button"
              @click="clearFilters"
              class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors"
            >
              Limpiar
            </button>
          </div>
        </form>
      </CardContent>
    </Card>

    <!-- Products Table -->
    <Card>
      <CardContent class="p-0">
        <div v-if="products.data && products.data.length > 0" class="overflow-x-auto">
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
                  Precios
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Stock
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Estado
                </th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Acciones
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="product in products.data" :key="product.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div>
                    <div class="text-sm font-medium text-gray-900">{{ product.name }}</div>
                    <div class="text-sm text-gray-500">{{ product.code }}</div>
                    <div v-if="product.description" class="text-xs text-gray-400 truncate max-w-xs">
                      {{ product.description }}
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span v-if="product.category" class="text-sm text-gray-900">
                    {{ product.category.name }}
                  </span>
                  <span v-else class="text-sm text-gray-400">Sin categoría</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">
                    <div>Compra: {{ formatCurrency(product.purchase_price) }}</div>
                    <div>Venta: {{ formatCurrency(product.sale_price) }}</div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm">
                    <div class="font-medium" :class="getStockColor(product.stock_quantity, product.min_stock)">
                      {{ product.stock_quantity }} {{ product.unit }}
                    </div>
                    <div class="text-xs text-gray-500">
                      Mín: {{ product.min_stock }} {{ product.unit }}
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex flex-col gap-1">
                    <Badge :variant="product.is_active ? 'success' : 'danger'">
                      {{ product.is_active ? 'Activo' : 'Inactivo' }}
                    </Badge>
                    <Badge :variant="getStockStatusVariant(product.stock_quantity, product.min_stock)">
                      {{ getStockStatusText(product.stock_quantity, product.min_stock) }}
                    </Badge>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end gap-1">
                    <!-- Ver -->
                    <Link
                      :href="`/productos/${product.id}`"
                      class="inline-flex items-center justify-center w-8 h-8 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition-colors"
                      title="Ver detalles"
                    >
                      <Eye class="w-4 h-4" />
                    </Link>

                    <!-- Editar -->
                    <Link
                      v-if="can('products.update')"
                      :href="`/productos/${product.id}/editar`"
                      class="inline-flex items-center justify-center w-8 h-8 rounded-md text-blue-600 hover:text-blue-900 hover:bg-blue-50 transition-colors"
                      title="Editar producto"
                    >
                      <Edit class="w-4 h-4" />
                    </Link>

                    <!-- Activar/Desactivar -->
                    <button
                      v-if="can('products.update')"
                      @click="toggleStatus(product)"
                      :class="[
                        'inline-flex items-center justify-center w-8 h-8 rounded-md transition-colors',
                        product.is_active
                          ? 'text-yellow-600 hover:text-yellow-900 hover:bg-yellow-50'
                          : 'text-green-600 hover:text-green-900 hover:bg-green-50'
                      ]"
                      :title="product.is_active ? 'Desactivar' : 'Activar'"
                    >
                      <Power v-if="product.is_active" class="w-4 h-4" />
                      <CheckCircle v-else class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <div v-else class="text-center py-12">
          <Package class="mx-auto h-12 w-12 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900">No hay productos</h3>
          <p class="mt-1 text-sm text-gray-500">
            {{ error ? 'Error al cargar productos' : 'Comienza creando un nuevo producto.' }}
          </p>
          <div v-if="!error" class="mt-6">
            <Link
              href="/productos/crear"
              class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-700 hover:bg-primary-800"
            >
              Nuevo Producto
            </Link>
          </div>
        </div>
      </CardContent>
      
      <!-- Pagination -->
      <div v-if="products.data && products.data.length > 0" class="px-6 py-3 border-t border-gray-200">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Mostrando {{ products.from }} a {{ products.to }} de {{ products.total }} resultados
          </div>
          <div class="flex items-center space-x-2">
            <Link
              v-for="link in products.links"
              :key="link.label"
              :href="link.url"
              v-html="link.label"
              :class="[
                'px-3 py-2 text-sm rounded-md',
                link.active 
                  ? 'bg-primary-700 text-white' 
                  : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50'
              ]"
            />
          </div>
        </div>
      </div>
    </Card>
  </AdminLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Card from '@/Components/ui/Card.vue'
import CardContent from '@/Components/ui/CardContent.vue'
import Badge from '@/Components/ui/Badge.vue'
import Alert from '@/Components/ui/Alert.vue'
import AlertTitle from '@/Components/ui/AlertTitle.vue'
import AlertDescription from '@/Components/ui/AlertDescription.vue'
import {
  Package,
  AlertCircle,
  CheckCircle,
  AlertTriangle,
  XCircle,
  DollarSign,
  Eye,
  Edit,
  Power
} from 'lucide-vue-next'
import { usePermissions } from '@/composables/usePermissions'

const props = defineProps({
  products: Object,
  categories: Array,
  filters: Object,
  stats: Object,
  error: String
})

const { can } = usePermissions()

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('es-BO', {
    style: 'currency',
    currency: 'BOB',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(amount || 0)
}

const formatDate = (date) => {
  if (!date) return 'Sin fecha'
  return new Date(date).toLocaleDateString('es-BO')
}

const filters = reactive({
  search: props.filters?.search || '',
  category: props.filters?.category || '',
  status: props.filters?.status || '',
  stock_status: props.filters?.stock_status || '',
})

const getStockColor = (stock, minStock) => {
  if (stock <= 0) return 'text-red-600'
  if (stock <= minStock) return 'text-yellow-600'
  return 'text-green-600'
}

const getStockStatusVariant = (stock, minStock) => {
  if (stock <= 0) return 'danger'
  if (stock <= minStock) return 'warning'
  return 'success'
}

const getStockStatusText = (stock, minStock) => {
  if (stock <= 0) return 'Sin Stock'
  if (stock <= minStock) return 'Stock Bajo'
  return 'En Stock'
}

const applyFilters = () => {
  router.get('/productos', filters, {
    preserveState: true,
    preserveScroll: true,
  })
}

const clearFilters = () => {
  Object.keys(filters).forEach(key => {
    filters[key] = ''
  })
  applyFilters()
}

const toggleStatus = (product) => {
  const action = product.is_active ? 'desactivar' : 'activar'

  if (confirm(`¿Estás seguro de ${action} este producto?`)) {
    router.post(`/productos/${product.id}/toggle-status`, {}, {
      preserveState: true,
      preserveScroll: true,
      onSuccess: () => {
        const actionPast = product.is_active ? 'activado' : 'desactivado'
        window.$notify?.success(
          'Producto actualizado',
          `El producto "${product.name}" ha sido ${actionPast} exitosamente.`
        )
      },
      onError: () => {
        window.$notify?.error(
          'Error',
          `No se pudo ${action} el producto. Por favor, inténtalo de nuevo.`
        )
      }
    })
  }
}
</script>