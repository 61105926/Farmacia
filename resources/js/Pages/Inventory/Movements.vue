<template>
  <AdminLayout>
    <div class="p-6">
      <!-- Header -->
      <div class="mb-6 flex items-center justify-between">
        <div>
          <Link href="/inventario" class="text-sm text-primary-700 hover:text-primary-800 mb-2 inline-block">
            ← Volver a inventario
          </Link>
          <h1 class="text-2xl font-bold text-gray-900">Movimientos de Inventario</h1>
          <p class="text-sm text-gray-600 mt-1">Historial completo de movimientos de stock</p>
        </div>
        <div class="flex items-center gap-2">
          <Link
            href="/inventario/crear"
            class="px-4 py-2 bg-primary-700 text-white rounded-md hover:bg-primary-800 transition-colors"
          >
            Nuevo Movimiento
          </Link>
          <button
            @click="exportMovements"
            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors"
          >
            Exportar
          </button>
        </div>
      </div>

      <!-- Filters -->
      <Card class="mb-6">
        <CardContent class="p-4">
          <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <!-- Search -->
            <div class="md:col-span-2">
              <input
                v-model="filters.search"
                type="text"
                placeholder="Buscar por producto..."
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                @input="debouncedSearch"
              />
            </div>

            <!-- Product Filter -->
            <div>
              <select
                v-model="filters.product_id"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                @change="applyFilters"
              >
                <option value="">Todos los productos</option>
                <option v-for="product in products" :key="product.id" :value="product.id">
                  {{ product.description || product.name || 'N/A' }} ({{ product.code }})
                </option>
              </select>
            </div>

            <!-- Date From -->
            <div>
              <input
                v-model="filters.date_from"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                @change="applyFilters"
              />
            </div>

            <!-- Date To -->
            <div>
              <input
                v-model="filters.date_to"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                @change="applyFilters"
              />
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <Card>
          <CardContent class="p-4">
            <div class="flex items-center">
              <div class="p-2 bg-blue-100 rounded-lg">
                <TrendingUp class="w-6 h-6 text-blue-600" />
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-500">Total Entradas</p>
                <p class="text-2xl font-bold text-gray-900">{{ summary.totalEntries || 0 }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-4">
            <div class="flex items-center">
              <div class="p-2 bg-red-100 rounded-lg">
                <TrendingDown class="w-6 h-6 text-red-600" />
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-500">Total Salidas</p>
                <p class="text-2xl font-bold text-gray-900">{{ summary.totalExits || 0 }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-4">
            <div class="flex items-center">
              <div class="p-2 bg-green-100 rounded-lg">
                <Package class="w-6 h-6 text-green-600" />
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-500">Productos Únicos</p>
                <p class="text-2xl font-bold text-gray-900">{{ summary.uniqueProducts || 0 }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-4">
            <div class="flex items-center">
              <div class="p-2 bg-purple-100 rounded-lg">
                <Activity class="w-6 h-6 text-purple-600" />
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-500">Total Movimientos</p>
                <p class="text-2xl font-bold text-gray-900">{{ summary.totalMovements || 0 }}</p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Movements Table -->
      <Card>
        <CardContent class="p-0">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Fecha
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Producto
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Tipo
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Cantidad
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Stock Anterior
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Stock Nuevo
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Usuario
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Referencia
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Acciones
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="movement in movements.data" :key="movement.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ formatDate(movement.movement_date) }}</div>
                    <div class="text-xs text-gray-500">{{ formatTime(movement.created_at) }}</div>
                  </td>
                  <td class="px-6 py-4">
                    <div>
                      <div class="text-sm font-medium text-gray-900">{{ movement.product.description || movement.product.name || 'N/A' }}</div>
                      <div class="text-sm text-gray-500">{{ movement.product.code }}</div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      class="px-2 py-1 text-xs font-medium rounded-full"
                      :class="{
                        'bg-green-100 text-green-800': movement.transaction_type === 'in',
                        'bg-red-100 text-red-800': movement.transaction_type === 'out',
                      }"
                    >
                      {{ movement.movement_type_label }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      class="text-sm font-medium"
                      :class="{
                        'text-green-600': movement.transaction_type === 'in',
                        'text-red-600': movement.transaction_type === 'out',
                      }"
                    >
                      {{ movement.transaction_type === 'in' ? '+' : '-' }}{{ movement.quantity }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ movement.previous_stock }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ movement.new_stock }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ movement.creator.name }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <div v-if="movement.reference_number">
                      {{ movement.reference_number }}
                    </div>
                    <div v-else class="text-gray-400">—</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <Link
                      :href="`/inventario/${movement.id}`"
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
          <div v-if="movements.data.length === 0" class="text-center py-12">
            <Activity class="mx-auto h-12 w-12 text-gray-400" />
            <h3 class="mt-2 text-sm font-medium text-gray-900">No hay movimientos</h3>
            <p class="mt-1 text-sm text-gray-500">No se encontraron movimientos con los filtros aplicados.</p>
            <div class="mt-6">
              <Link
                href="/inventario/crear"
                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-700 hover:bg-primary-800"
              >
                Nuevo Movimiento
              </Link>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Pagination -->
      <div v-if="movements.data.length > 0" class="mt-6">
        <Pagination :links="movements.links" />
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, watch } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardContent } from '@/Components/ui'
import Pagination from '@/Components/Pagination.vue'
import { useDebouncedRef } from '@/composables/useDebouncedRef'
import { Package, TrendingUp, TrendingDown, Activity } from 'lucide-vue-next'

const props = defineProps({
  movements: Object,
  products: Array,
  filters: Object,
  summary: {
    type: Object,
    default: () => ({})
  }
})

const filters = ref({
  search: props.filters.search || '',
  product_id: props.filters.product_id || '',
  date_from: props.filters.date_from || '',
  date_to: props.filters.date_to || '',
})

const debouncedSearch = useDebouncedRef(() => {
  applyFilters()
}, 500)

const applyFilters = () => {
  router.get('/inventario/movimientos', filters.value, {
    preserveState: true,
    preserveScroll: true,
  })
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
  })
}

const formatTime = (date) => {
  return new Date(date).toLocaleTimeString('es-ES', {
    hour: '2-digit',
    minute: '2-digit',
  })
}

const exportMovements = () => {
  // Implementar exportación de movimientos
  const params = new URLSearchParams(filters.value)
  window.open(`/inventario/movimientos/export?${params.toString()}`, '_blank')
}

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
