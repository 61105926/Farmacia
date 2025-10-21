<template>
  <AdminLayout>
    <div class="p-6">
      <!-- Header -->
      <div class="mb-6 flex items-center justify-between">
        <div>
          <Link href="/inventario" class="text-sm text-primary-700 hover:text-primary-800 mb-2 inline-block">
            ← Volver a inventario
          </Link>
          <h1 class="text-2xl font-bold text-gray-900">Gestión de Stock</h1>
          <p class="text-sm text-gray-600 mt-1">Control de inventario por producto</p>
        </div>
        <div class="flex items-center gap-2">
          <Link
            href="/inventario/stock-bajo"
            class="px-4 py-2 border border-orange-300 text-orange-700 rounded-md hover:bg-orange-50 transition-colors"
          >
            Stock Bajo
          </Link>
          <Link
            href="/inventario/por-vencer"
            class="px-4 py-2 border border-red-300 text-red-700 rounded-md hover:bg-red-50 transition-colors"
          >
            Por Vencer
          </Link>
        </div>
      </div>

      <!-- Filters -->
      <Card class="mb-6">
        <CardContent class="p-4">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div class="md:col-span-2">
              <input
                v-model="filters.search"
                type="text"
                placeholder="Buscar por nombre, código..."
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                @input="debouncedSearch"
              />
            </div>

            <!-- Category Filter -->
            <div>
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
              <select
                v-model="filters.stock_status"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                @change="applyFilters"
              >
                <option value="">Todos los estados</option>
                <option value="low">Stock bajo</option>
                <option value="out">Sin stock</option>
                <option value="normal">Stock normal</option>
              </select>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Stock Table -->
      <Card>
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
                    Stock Actual
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Stock Mínimo
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Estado
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Último Movimiento
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Acciones
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="product in products.data" :key="product.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4">
                    <div>
                      <div class="text-sm font-medium text-gray-900">{{ product.name }}</div>
                      <div class="text-sm text-gray-500">{{ product.code }}</div>
                      <div v-if="product.active_ingredient" class="text-xs text-gray-400">
                        {{ product.active_ingredient }}
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
                    <div class="flex items-center">
                      <span
                        class="text-sm font-medium"
                        :class="{
                          'text-red-600': product.stock_quantity === 0,
                          'text-orange-600': product.stock_quantity > 0 && product.stock_quantity <= product.min_stock,
                          'text-gray-900': product.stock_quantity > product.min_stock,
                        }"
                      >
                        {{ product.stock_quantity }}
                      </span>
                      <span v-if="product.stock_quantity === 0" class="ml-2 text-xs text-red-600">
                        Sin stock
                      </span>
                      <span v-else-if="product.stock_quantity <= product.min_stock" class="ml-2 text-xs text-orange-600">
                        Bajo
                      </span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ product.min_stock }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      class="px-2 py-1 text-xs font-medium rounded-full"
                      :class="{
                        'bg-red-100 text-red-800': product.stock_quantity === 0,
                        'bg-orange-100 text-orange-800': product.stock_quantity > 0 && product.stock_quantity <= product.min_stock,
                        'bg-green-100 text-green-800': product.stock_quantity > product.min_stock,
                      }"
                    >
                      {{ getStockStatus(product) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ formatDate(product.updated_at) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex items-center justify-end gap-2">
                      <Link
                        :href="`/productos/${product.id}`"
                        class="text-primary-700 hover:text-primary-900"
                      >
                        Ver
                      </Link>
                      <Link
                        :href="`/inventario/crear?product=${product.id}`"
                        class="text-blue-600 hover:text-blue-900"
                      >
                        Movimiento
                      </Link>
                      <button
                        @click="adjustStock(product)"
                        class="text-green-600 hover:text-green-900"
                      >
                        Ajustar
                      </button>
                    </div>
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

      <!-- Adjust Stock Modal -->
      <div v-if="showAdjustModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
          <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Ajustar Stock</h3>
            <div class="mb-4">
              <div class="text-sm text-gray-600">Producto: {{ selectedProduct?.name }}</div>
              <div class="text-sm text-gray-600">Stock actual: {{ selectedProduct?.stock_quantity }}</div>
            </div>
            <form @submit.prevent="submitAdjustment">
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Nuevo Stock <span class="text-red-500">*</span>
                </label>
                <input
                  v-model.number="adjustForm.new_quantity"
                  type="number"
                  min="0"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                />
              </div>
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Motivo <span class="text-red-500">*</span>
                </label>
                <textarea
                  v-model="adjustForm.reason"
                  rows="3"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                  placeholder="Motivo del ajuste..."
                ></textarea>
              </div>
              <div class="flex justify-end gap-2">
                <button
                  type="button"
                  @click="closeAdjustModal"
                  class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50"
                >
                  Cancelar
                </button>
                <button
                  type="submit"
                  :disabled="adjustForm.processing"
                  class="px-4 py-2 bg-primary-700 text-white rounded-md hover:bg-primary-800 disabled:opacity-50"
                >
                  {{ adjustForm.processing ? 'Ajustando...' : 'Ajustar' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardContent } from '@/Components/ui'
import Pagination from '@/Components/Pagination.vue'
import { useDebouncedRef } from '@/Composables/useDebouncedRef'
import { Package } from 'lucide-vue-next'

const props = defineProps({
  products: Object,
  categories: Array,
  filters: Object,
})

const filters = ref({
  search: props.filters.search || '',
  category_id: props.filters.category_id || '',
  stock_status: props.filters.stock_status || '',
})

const showAdjustModal = ref(false)
const selectedProduct = ref(null)

const adjustForm = useForm({
  product_id: null,
  new_quantity: 0,
  reason: '',
  movement_date: new Date().toISOString().split('T')[0],
})

const debouncedSearch = useDebouncedRef(() => {
  applyFilters()
}, 500)

const applyFilters = () => {
  router.get('/inventario/stock', filters.value, {
    preserveState: true,
    preserveScroll: true,
  })
}

const getStockStatus = (product) => {
  if (product.stock_quantity === 0) return 'Sin Stock'
  if (product.stock_quantity <= product.min_stock) return 'Stock Bajo'
  return 'Normal'
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
  })
}

const adjustStock = (product) => {
  selectedProduct.value = product
  adjustForm.product_id = product.id
  adjustForm.new_quantity = product.stock_quantity
  adjustForm.reason = ''
  showAdjustModal.value = true
}

const closeAdjustModal = () => {
  showAdjustModal.value = false
  selectedProduct.value = null
  adjustForm.reset()
}

const submitAdjustment = () => {
  adjustForm.post('/inventario/ajustar', {
    onSuccess: () => {
      closeAdjustModal()
      router.reload()
    }
  })
}
</script>
