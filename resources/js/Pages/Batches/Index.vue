<template>
  <AdminLayout>
    <div class="p-6">
      <!-- Header -->
      <div class="mb-6 flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Lotes</h1>
          <p class="text-sm text-gray-600 mt-1">Control de lotes por producto (PEPS)</p>
        </div>
        <Link
          href="/inventario/crear"
          class="px-4 py-2 bg-primary-700 text-white rounded-md hover:bg-primary-800 transition-colors"
        >
          + Nuevo Ingreso
        </Link>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <Card>
          <CardContent class="p-4">
            <p class="text-sm text-gray-500">Total Lotes</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.total }}</p>
          </CardContent>
        </Card>
        <Card>
          <CardContent class="p-4">
            <p class="text-sm text-gray-500">Activos</p>
            <p class="text-2xl font-bold text-green-600">{{ stats.active }}</p>
          </CardContent>
        </Card>
        <Card>
          <CardContent class="p-4">
            <p class="text-sm text-gray-500">Agotados</p>
            <p class="text-2xl font-bold text-gray-400">{{ stats.depleted }}</p>
          </CardContent>
        </Card>
        <Card>
          <CardContent class="p-4">
            <p class="text-sm text-gray-500">Por Vencer (30d)</p>
            <p class="text-2xl font-bold text-orange-500">{{ stats.expiringSoon }}</p>
          </CardContent>
        </Card>
      </div>

      <!-- Filtros -->
      <Card class="mb-6">
        <CardContent class="p-4">
          <div class="flex flex-wrap gap-3">
            <input
              v-model="filters.search"
              @keyup.enter="applyFilters"
              type="text"
              placeholder="Buscar lote o producto..."
              class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
            />
            <div class="w-64">
              <ProductSelect
                v-model="filters.product_id"
                :products="products"
                placeholder="Todos los productos"
                @change="applyFilters"
              />
            </div>
            <select
              v-model="filters.status"
              @change="applyFilters"
              class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
              <option value="">Todos los estados</option>
              <option value="active">Activo</option>
              <option value="depleted">Agotado</option>
              <option value="expired">Vencido</option>
            </select>
            <button
              @click="applyFilters"
              class="px-4 py-2 bg-primary-600 text-white rounded-md text-sm hover:bg-primary-700"
            >
              Buscar
            </button>
            <button
              v-if="hasFilters"
              @click="clearFilters"
              class="px-4 py-2 border border-gray-300 text-gray-600 rounded-md text-sm hover:bg-gray-50"
            >
              Limpiar
            </button>
          </div>
        </CardContent>
      </Card>

      <!-- Tabla -->
      <Card>
        <CardContent class="p-0">
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                  <th class="text-left px-4 py-3 font-semibold text-gray-600">Lote</th>
                  <th class="text-left px-4 py-3 font-semibold text-gray-600">Producto</th>
                  <th class="text-left px-4 py-3 font-semibold text-gray-600">Proveedor</th>
                  <th class="text-center px-4 py-3 font-semibold text-gray-600">Ingreso</th>
                  <th class="text-center px-4 py-3 font-semibold text-gray-600">Vencimiento</th>
                  <th class="text-center px-4 py-3 font-semibold text-gray-600">Inicial</th>
                  <th class="text-center px-4 py-3 font-semibold text-gray-600">Restante</th>
                  <th class="text-center px-4 py-3 font-semibold text-gray-600">Estado</th>
                  <th class="text-center px-4 py-3 font-semibold text-gray-600">Acciones</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-if="!batches.data || batches.data.length === 0">
                  <td colspan="9" class="text-center py-12 text-gray-400">No hay lotes registrados</td>
                </tr>
                <tr
                  v-for="batch in batches.data"
                  :key="batch.id"
                  class="hover:bg-gray-50 transition-colors"
                  :class="{ 'bg-orange-50': isExpiringSoon(batch.expiry_date), 'bg-red-50': isExpired(batch.expiry_date) }"
                >
                  <td class="px-4 py-3 font-mono font-semibold text-gray-800">{{ batch.batch_number }}</td>
                  <td class="px-4 py-3">
                    <div class="font-medium text-gray-900">{{ batch.product?.description || batch.product?.name }}</div>
                    <div v-if="batch.product?.description" class="text-xs text-gray-500">{{ batch.product?.name }}</div>
                    <div class="text-xs text-gray-400 mt-0.5">Lote: {{ batch.batch_number }}</div>
                  </td>
                  <td class="px-4 py-3 text-gray-600">{{ batch.supplier || '—' }}</td>
                  <td class="px-4 py-3 text-center text-gray-600">{{ formatDate(batch.entry_date) }}</td>
                  <td class="px-4 py-3 text-center">
                    <span v-if="!batch.expiry_date" class="text-gray-400">—</span>
                    <span
                      v-else
                      :class="expiryClass(batch.expiry_date)"
                    >
                      {{ formatDate(batch.expiry_date) }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-center text-gray-600">{{ batch.initial_quantity }}</td>
                  <td class="px-4 py-3 text-center">
                    <span class="font-bold" :class="batch.remaining_quantity === 0 ? 'text-gray-400' : 'text-gray-900'">
                      {{ batch.remaining_quantity }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-center">
                    <span
                      class="px-2 py-1 rounded-full text-xs font-semibold"
                      :class="statusClass(batch.status)"
                    >
                      {{ statusLabel(batch.status) }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-center">
                    <Link
                      :href="`/lotes/producto/${batch.product_id}`"
                      class="text-primary-600 hover:underline text-xs"
                    >
                      Ver lotes
                    </Link>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Paginación -->
          <div v-if="batches.last_page > 1" class="flex justify-between items-center px-4 py-3 border-t border-gray-200">
            <p class="text-sm text-gray-500">
              Mostrando {{ batches.from }}–{{ batches.to }} de {{ batches.total }} lotes
            </p>
            <div class="flex gap-1">
              <a
                v-for="link in batches.links"
                :key="link.label"
                :href="link.url || '#'"
                @click.prevent="link.url && router.get(link.url)"
                class="px-3 py-1 rounded text-sm border"
                :class="link.active ? 'bg-primary-600 text-white border-primary-600' : 'border-gray-300 text-gray-600 hover:bg-gray-50'"
                :style="!link.url ? 'pointer-events:none;opacity:0.4' : ''"
              >{{ stripTags(link.label) }}</a>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </AdminLayout>
</template>

<script setup>
import { reactive, computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Card from '@/Components/ui/Card.vue'
import CardContent from '@/Components/ui/CardContent.vue'
import ProductSelect from '@/Components/ui/ProductSelect.vue'

const props = defineProps({
  batches:  { type: Object, default: () => ({ data: [], total: 0 }) },
  products: { type: Array,  default: () => [] },
  stats:    { type: Object, default: () => ({ total: 0, active: 0, depleted: 0, expiringSoon: 0 }) },
  filters:  { type: Object, default: () => ({}) },
})

const filters = reactive({
  search:     props.filters?.search     || '',
  product_id: props.filters?.product_id || '',
  status:     props.filters?.status     || '',
})

const hasFilters = computed(() => filters.search || filters.product_id || filters.status)

function applyFilters() {
  router.get('/lotes', filters, { preserveState: true, replace: true })
}

function clearFilters() {
  filters.search = ''
  filters.product_id = ''
  filters.status = ''
  applyFilters()
}

function formatDate(d) {
  if (!d) return '—'
  try {
    return new Date(d).toLocaleDateString('es-BO')
  } catch {
    return d
  }
}

function isExpiringSoon(expiry) {
  if (!expiry) return false
  const d = new Date(expiry)
  const now = new Date()
  const diff = (d - now) / (1000 * 60 * 60 * 24)
  return diff > 0 && diff <= 30
}

function isExpired(expiry) {
  if (!expiry) return false
  return new Date(expiry) < new Date()
}

function expiryClass(expiry) {
  if (isExpired(expiry)) return 'text-red-600 font-semibold'
  if (isExpiringSoon(expiry)) return 'text-orange-600 font-semibold'
  return 'text-gray-600'
}

function statusClass(status) {
  return {
    active:   'bg-green-100 text-green-700',
    depleted: 'bg-gray-100 text-gray-500',
    expired:  'bg-red-100 text-red-600',
  }[status] || 'bg-gray-100 text-gray-500'
}

function statusLabel(status) {
  return { active: 'Activo', depleted: 'Agotado', expired: 'Vencido' }[status] || status
}

function stripTags(str) {
  if (!str) return ''
  return str.replace(/<[^>]*>/g, '')
}
</script>
