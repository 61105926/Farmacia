<template>
  <AdminLayout>
    <div class="p-6">
      <!-- Header -->
      <div class="mb-6 flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Inventario</h1>
          <p class="text-sm text-gray-600 mt-1">Gestión de movimientos y stock</p>
        </div>
        <div class="flex items-center gap-2">
          <Link
            v-if="can('inventory.create')"
            href="/inventario/crear"
            class="px-4 py-2 bg-primary-700 text-white rounded-md hover:bg-primary-800 transition-colors"
          >
            Nuevo Movimiento
          </Link>
          <Link
            href="/inventario/stock"
            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors"
          >
            Ver Stock
          </Link>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <Card>
          <CardContent class="p-4">
            <div class="flex items-center">
              <div class="p-2 bg-green-100 rounded-lg">
                <Package class="w-6 h-6 text-green-600" />
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-500">Total Productos</p>
                <p class="text-2xl font-bold text-gray-900">{{ stats.totalProducts || 0 }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-4">
            <div class="flex items-center">
              <div class="p-2 bg-blue-100 rounded-lg">
                <TrendingUp class="w-6 h-6 text-blue-600" />
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-500">Entradas Hoy</p>
                <p class="text-2xl font-bold text-gray-900">{{ stats.entriesToday || 0 }}</p>
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
                <p class="text-sm font-medium text-gray-500">Salidas Hoy</p>
                <p class="text-2xl font-bold text-gray-900">{{ stats.exitsToday || 0 }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-4">
            <div class="flex items-center">
              <div class="p-2 bg-orange-100 rounded-lg">
                <AlertTriangle class="w-6 h-6 text-orange-600" />
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-500">Stock Bajo</p>
                <p class="text-2xl font-bold text-gray-900">{{ stats.lowStock || 0 }}</p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Filters -->
      <Card class="mb-6">
        <CardContent class="p-4">
          <div class="grid grid-cols-1 md:grid-cols-7 gap-4">
            <!-- Search -->
            <div class="md:col-span-2">
              <input
                v-model="filters.search"
                type="text"
                placeholder="Buscar por producto..."
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                @input="applyFilters"
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
                  {{ product.name }} ({{ product.code }})
                </option>
              </select>
            </div>

            <!-- Movement Type -->
            <div>
              <select
                v-model="filters.movement_type"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                @change="applyFilters"
              >
                <option value="">Todos los tipos</option>
                <option v-for="(label, value) in movementTypes" :key="value" :value="value">
                  {{ label }}
                </option>
              </select>
            </div>

            <!-- Transaction Type -->
            <div>
              <select
                v-model="filters.transaction_type"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                @change="applyFilters"
              >
                <option value="">Todas las transacciones</option>
                <option v-for="(label, value) in transactionTypes" :key="value" :value="value">
                  {{ label }}
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

            <!-- Filter Actions -->
            <div class="flex gap-2">
              <button
                @click="clearFilters"
                class="px-3 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors"
                title="Limpiar filtros"
              >
                🧹
              </button>
              <button
                @click="applyFilters"
                class="px-3 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 transition-colors"
                title="Aplicar filtros"
              >
                🔍
              </button>
            </div>
          </div>
        </CardContent>
      </Card>

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
                    Tipo Movimiento
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Entrada/Salida
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
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Acciones
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="movement in movements.data" :key="movement.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ formatDate(movement.movement_date) }}</div>
                  </td>
                  <td class="px-6 py-4">
                    <div>
                      <div class="text-sm font-medium text-gray-900">{{ movement.product.name }}</div>
                      <div class="text-sm text-gray-500">{{ movement.product.code }}</div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                      {{ movement.movement_type_label || getMovementTypeLabel(movement.movement_type) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      class="px-2 py-1 text-xs font-medium rounded-full"
                      :class="{
                        'bg-green-100 text-green-800': movement.transaction_type === 'in',
                        'bg-red-100 text-red-800': movement.transaction_type === 'out',
                      }"
                    >
                      {{ movement.transaction_type === 'in' ? 'Entrada' : 'Salida' }}
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
                  <td class="px-6 py-4 whitespace-nowrap text-right">
                    <Button
                      @click="$inertia.visit(`/inventario/${movement.id}`)"
                      variant="outline"
                      size="sm"
                      class="h-8 w-8 p-0"
                      title="Ver Detalles"
                    >
                      <Eye class="h-4 w-4" />
                    </Button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Empty State -->
          <div v-if="movements.data.length === 0" class="text-center py-12">
            <Package class="mx-auto h-12 w-12 text-gray-400" />
            <h3 class="mt-2 text-sm font-medium text-gray-900">No hay movimientos</h3>
            <p class="mt-1 text-sm text-gray-500">Comienza registrando un movimiento de inventario.</p>
            <div class="mt-6">
              <Link
                v-if="can('inventory.create')"
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
import { reactive, watch } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardContent } from '@/Components/ui'
import Button from '@/Components/ui/Button.vue'
import Pagination from '@/Components/Pagination.vue'
import { usePermissions } from '@/composables/usePermissions'
import { Package, TrendingUp, TrendingDown, AlertTriangle, Eye } from 'lucide-vue-next'

const { can } = usePermissions()

const props = defineProps({
  movements: Object,
  products: Array,
  movementTypes: Object,
  transactionTypes: Object,
  filters: Object,
  stats: {
    type: Object,
    default: () => ({})
  }
})

// Initialize filters with reactive
const filters = reactive({
  search: props.filters?.search || '',
  product_id: props.filters?.product_id || '',
  movement_type: props.filters?.movement_type || '',
  transaction_type: props.filters?.transaction_type || '',
  date_from: props.filters?.date_from || '',
  date_to: props.filters?.date_to || '',
})

console.log('Initial filters:', props.filters)
console.log('Reactive filters:', filters)

// Watch for props changes to sync filters
watch(() => props.filters, (newFilters) => {
  if (newFilters) {
    Object.keys(filters).forEach(key => {
      if (newFilters[key] !== undefined) {
        filters[key] = newFilters[key]
      }
    })
  }
}, { deep: true, immediate: true })

const applyFilters = () => {
  console.log('Applying filters:', filters)
  const params = {}

  // Only include non-empty filters
  Object.keys(filters).forEach(key => {
    if (filters[key] && filters[key] !== '') {
      params[key] = filters[key]
    }
  })

  router.get('/inventario', params, {
    preserveState: true,
    preserveScroll: true,
  })
}

const clearFilters = () => {
  console.log('Clearing filters')
  Object.keys(filters).forEach(key => {
    filters[key] = ''
  })

  router.get('/inventario', {}, {
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

// Función auxiliar para obtener el label del tipo de movimiento
const getMovementTypeLabel = (type) => {
  const labels = {
    'purchase': 'Compra',
    'sale': 'Venta',
    'return': 'Devolución',
    'adjustment': 'Ajuste',
    'transfer': 'Transferencia',
    'damage': 'Daño',
    'expiry': 'Vencimiento',
  }
  return labels[type] || type
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
