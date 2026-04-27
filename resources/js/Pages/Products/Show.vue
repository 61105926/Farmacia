<template>
  <AdminLayout>
    <div class="p-6">
      <!-- Header -->
      <div class="mb-6 flex items-center justify-between">
        <div>
          <Link href="/productos" class="text-sm text-primary-700 hover:text-primary-800 mb-2 inline-block">
            ← Volver a productos
          </Link>
          <h1 class="text-2xl font-bold text-gray-900 mt-2">
            {{ product.name }}
          </h1>
          <p class="text-sm text-gray-600 mt-1">Código: {{ product.code }}</p>
        </div>
        <div class="flex items-center gap-2">
          <Link
            v-if="can('products.update')"
            :href="`/productos/${product.id}/editar`"
            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
          >
            <Edit class="w-4 h-4" />
            <span>Editar</span>
          </Link>
          <button
            v-if="can('products.update')"
            @click="toggleStatus"
            :class="[
              'inline-flex items-center gap-2 px-4 py-2 rounded-md transition-colors',
              product.is_active
                ? 'bg-yellow-600 text-white hover:bg-yellow-700'
                : 'bg-green-600 text-white hover:bg-green-700'
            ]"
          >
            <Power v-if="product.is_active" class="w-4 h-4" />
            <CheckCircle v-else class="w-4 h-4" />
            <span>{{ product.is_active ? 'Desactivar' : 'Activar' }}</span>
          </button>
        </div>
      </div>

      <!-- Tabs -->
      <div class="mb-6 border-b border-gray-200">
        <nav class="-mb-px flex space-x-6">
          <button
            v-for="tab in tabs"
            :key="tab.key"
            @click="activeTab = tab.key"
            :class="[
              'py-3 px-1 text-sm font-medium border-b-2 transition-colors inline-flex items-center gap-2',
              activeTab === tab.key
                ? 'border-primary-600 text-primary-700'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            <component :is="tab.icon" class="w-4 h-4" />
            {{ tab.label }}
            <span
              v-if="tab.badge !== undefined"
              class="ml-1 px-2 py-0.5 text-xs rounded-full"
              :class="activeTab === tab.key ? 'bg-primary-100 text-primary-700' : 'bg-gray-100 text-gray-600'"
            >
              {{ tab.badge }}
            </span>
          </button>
        </nav>
      </div>

      <!-- Tab: Información -->
      <div v-if="activeTab === 'info'">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div class="lg:col-span-2 space-y-6">
            <Card>
              <CardHeader>
                <CardTitle>Información del Producto</CardTitle>
              </CardHeader>
              <CardContent>
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-500">Lote</label>
                    <div class="text-sm text-gray-900">{{ formatField(product.sku) }}</div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-500">Marca / Laboratorio</label>
                    <div class="text-sm text-gray-900">{{ formatField(product.brand) }}</div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-500">Dosificación</label>
                    <div class="text-sm text-gray-900">{{ formatField(product.dosage) }}</div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-500">Presentación</label>
                    <div class="text-sm text-gray-900">{{ formatField(product.presentation) }}</div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-500">Código de barras</label>
                    <div class="text-sm text-gray-900">{{ formatField(product.barcode) }}</div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-500">Tipo de Unidad</label>
                    <div class="text-sm text-gray-900">{{ formatField(product.unit_type) }}</div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-500">Fecha de Vencimiento</label>
                    <template v-if="product.nearest_expiry_date || product.expiry_date">
                      <div class="text-sm" :class="getExpiryDateClass(product.nearest_expiry_date || product.expiry_date)">
                        {{ formatDate(product.nearest_expiry_date || product.expiry_date) }}
                        <span v-if="isExpired(product.nearest_expiry_date || product.expiry_date)" class="ml-2 text-red-600 font-medium">⚠ Vencido</span>
                        <span v-else-if="isExpiringSoon(product.nearest_expiry_date || product.expiry_date)" class="ml-2 text-orange-600 font-medium">⚠ Por vencer</span>
                      </div>
                    </template>
                    <div v-else class="text-sm text-gray-900">—</div>
                  </div>
                </div>
                <div class="mt-6">
                  <label class="block text-sm font-medium text-gray-500">Descripción</label>
                  <div class="text-sm text-gray-900 whitespace-pre-line">{{ formatField(product.description) }}</div>
                </div>
                <div v-if="product.notes" class="mt-6">
                  <label class="block text-sm font-medium text-gray-500">Notas</label>
                  <div class="text-sm text-gray-900 whitespace-pre-line">{{ product.notes }}</div>
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Precios</CardTitle>
              </CardHeader>
              <CardContent>
                <div class="grid grid-cols-3 gap-4">
                  <div>
                    <div class="text-xs text-gray-500">Costo</div>
                    <div class="text-sm font-medium text-gray-900">Bs. {{ formatPrice(product.cost_price) }}</div>
                  </div>
                  <div>
                    <div class="text-xs text-gray-500">Precio Base</div>
                    <div class="text-sm font-medium text-gray-900">Bs. {{ formatPrice(product.base_price) }}</div>
                  </div>
                  <div>
                    <div class="text-xs text-gray-500">Precio Venta</div>
                    <div class="text-sm font-medium text-gray-900">Bs. {{ formatPrice(product.sale_price) }}</div>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>

          <!-- Sidebar -->
          <div class="space-y-6">
            <Card>
              <CardHeader>
                <CardTitle>Inventario</CardTitle>
              </CardHeader>
              <CardContent class="space-y-3">
                <div class="flex items-center justify-between">
                  <span class="text-sm text-gray-600">Stock actual</span>
                  <span class="text-sm font-medium" :class="stockColorClass">{{ product.stock_quantity || 0 }}</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-sm text-gray-600">Stock mínimo</span>
                  <span class="text-sm text-gray-900">{{ product.min_stock || 0 }}</span>
                </div>
                <div v-if="product.max_stock" class="flex items-center justify-between">
                  <span class="text-sm text-gray-600">Stock máximo</span>
                  <span class="text-sm text-gray-900">{{ product.max_stock }}</span>
                </div>
                <div v-if="product.stock_quantity === 0" class="text-xs text-red-600 font-medium">⚠ Sin stock</div>
                <div v-else-if="product.stock_quantity <= product.min_stock" class="text-xs text-orange-600 font-medium">⚠ Stock bajo</div>
                <div v-else class="text-xs text-green-600 font-medium">✓ Stock normal</div>

                <!-- Resumen lotes activos -->
                <div v-if="activeBatchesCount > 0" class="pt-3 border-t border-gray-100">
                  <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-500">Lotes activos</span>
                    <span class="text-xs font-medium text-blue-600">{{ activeBatchesCount }}</span>
                  </div>
                  <div v-if="nearestExpiry" class="flex items-center justify-between mt-1">
                    <span class="text-xs text-gray-500">Próximo vencimiento</span>
                    <span class="text-xs font-medium" :class="getExpiryDateClass(nearestExpiry)">{{ formatDate(nearestExpiry) }}</span>
                  </div>
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Estado</CardTitle>
              </CardHeader>
              <CardContent>
                <span
                  class="px-2 py-1 text-xs font-medium rounded-full"
                  :class="{
                    'bg-green-100 text-green-800': product.is_active,
                    'bg-gray-100 text-gray-800': !product.is_active,
                  }"
                >
                  {{ product.is_active ? 'Activo' : 'Inactivo' }}
                </span>
              </CardContent>
            </Card>

            <Card v-if="stats && (stats.total_sales > 0 || stats.total_presales > 0)">
              <CardHeader>
                <CardTitle>Estadísticas</CardTitle>
              </CardHeader>
              <CardContent class="space-y-3">
                <div class="flex items-center justify-between">
                  <span class="text-sm text-gray-600">Total Ventas</span>
                  <span class="text-sm font-medium text-gray-900">{{ stats.total_sales || 0 }} uds.</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-sm text-gray-600">Total Preventas</span>
                  <span class="text-sm font-medium text-gray-900">{{ stats.total_presales || 0 }} uds.</span>
                </div>
                <div v-if="stats.total_revenue > 0" class="flex items-center justify-between">
                  <span class="text-sm text-gray-600">Ingresos</span>
                  <span class="text-sm font-medium text-green-600">{{ formatCurrency(stats.total_revenue) }}</span>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>

      <!-- Tab: Movimientos -->
      <div v-if="activeTab === 'movements'">
        <Card>
          <CardHeader>
            <CardTitle>Movimientos de Inventario</CardTitle>
          </CardHeader>
          <CardContent>
            <div v-if="movements.length === 0" class="text-center py-12 text-gray-500">
              <ArrowLeftRight class="w-12 h-12 mx-auto mb-3 text-gray-300" />
              <p class="text-sm">No hay movimientos registrados para este producto.</p>
            </div>
            <div v-else class="overflow-x-auto">
              <table class="w-full text-sm">
                <thead>
                  <tr class="border-b border-gray-200">
                    <th class="pb-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Fecha</th>
                    <th class="pb-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tipo</th>
                    <th class="pb-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Cantidad</th>
                    <th class="pb-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Stock Ant.</th>
                    <th class="pb-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Stock Res.</th>
                    <th class="pb-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Motivo</th>
                    <th class="pb-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Usuario</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                  <tr v-for="mov in movements" :key="mov.id" class="hover:bg-gray-50 transition-colors">
                    <td class="py-3 text-gray-600 whitespace-nowrap">{{ formatDateShort(mov.created_at) }}</td>
                    <td class="py-3">
                      <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium" :class="movementTypeClass(mov.transaction_type)">
                        <component :is="movementTypeIcon(mov.transaction_type)" class="w-3 h-3" />
                        {{ movementTypeLabel(mov.transaction_type) }}
                      </span>
                    </td>
                    <td class="py-3 text-right font-medium" :class="mov.transaction_type === 'in' ? 'text-green-600' : 'text-red-600'">
                      {{ mov.transaction_type === 'in' ? '+' : '-' }}{{ mov.quantity }}
                    </td>
                    <td class="py-3 text-right text-gray-500">{{ mov.previous_stock ?? '—' }}</td>
                    <td class="py-3 text-right text-gray-500">{{ mov.new_stock ?? '—' }}</td>
                    <td class="py-3 text-gray-600 max-w-xs truncate">{{ mov.reason || mov.notes || '—' }}</td>
                    <td class="py-3 text-gray-500">{{ mov.creator?.name || '—' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Tab: Lotes -->
      <div v-if="activeTab === 'batches'">
        <Card>
          <CardHeader>
            <CardTitle>Lotes (PEPS)</CardTitle>
          </CardHeader>
          <CardContent>
            <div v-if="batches.length === 0" class="text-center py-12 text-gray-500">
              <Layers class="w-12 h-12 mx-auto mb-3 text-gray-300" />
              <p class="text-sm">No hay lotes registrados para este producto.</p>
              <p class="text-xs text-gray-400 mt-1">Los lotes se crean al registrar entradas en Inventario.</p>
            </div>
            <div v-else class="space-y-3">
              <!-- Resumen -->
              <div class="grid grid-cols-3 gap-4 mb-6 p-4 bg-gray-50 rounded-lg">
                <div class="text-center">
                  <div class="text-2xl font-bold text-gray-900">{{ batches.length }}</div>
                  <div class="text-xs text-gray-500">Total lotes</div>
                </div>
                <div class="text-center">
                  <div class="text-2xl font-bold text-green-600">{{ activeBatchesCount }}</div>
                  <div class="text-xs text-gray-500">Activos</div>
                </div>
                <div class="text-center">
                  <div class="text-2xl font-bold text-gray-400">{{ batches.length - activeBatchesCount }}</div>
                  <div class="text-xs text-gray-500">Agotados/Vencidos</div>
                </div>
              </div>

              <!-- Lista de lotes -->
              <div class="overflow-x-auto">
                <table class="w-full text-sm">
                  <thead>
                    <tr class="border-b border-gray-200">
                      <th class="pb-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Lote</th>
                      <th class="pb-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Entrada</th>
                      <th class="pb-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Vencimiento</th>
                      <th class="pb-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Inicial</th>
                      <th class="pb-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Restante</th>
                      <th class="pb-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Uso</th>
                      <th class="pb-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Proveedor</th>
                      <th class="pb-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Estado</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-100">
                    <tr
                      v-for="(batch, index) in batches"
                      :key="batch.id"
                      class="hover:bg-gray-50 transition-colors"
                      :class="{ 'bg-blue-50': batch.status === 'active' && index === 0 }"
                    >
                      <td class="py-3">
                        <div class="font-medium text-gray-900">{{ batch.batch_number }}</div>
                        <div v-if="index === 0 && batch.status === 'active'" class="text-xs text-blue-600 font-medium">Siguiente en PEPS</div>
                      </td>
                      <td class="py-3 text-gray-600 whitespace-nowrap">{{ formatDateShort(batch.entry_date) }}</td>
                      <td class="py-3 whitespace-nowrap">
                        <span v-if="batch.expiry_date" :class="getExpiryDateClass(batch.expiry_date)">
                          {{ formatDateShort(batch.expiry_date) }}
                          <span v-if="isExpired(batch.expiry_date)" class="ml-1">⚠</span>
                          <span v-else-if="isExpiringSoon(batch.expiry_date)" class="ml-1">⚡</span>
                        </span>
                        <span v-else class="text-gray-400">—</span>
                      </td>
                      <td class="py-3 text-right text-gray-600">{{ batch.initial_quantity }}</td>
                      <td class="py-3 text-right font-medium" :class="batch.remaining_quantity > 0 ? 'text-green-700' : 'text-gray-400'">
                        {{ batch.remaining_quantity }}
                      </td>
                      <td class="py-3 w-32">
                        <div class="flex items-center gap-2">
                          <div class="flex-1 bg-gray-200 rounded-full h-1.5">
                            <div
                              class="h-1.5 rounded-full transition-all"
                              :class="batchProgressClass(batch)"
                              :style="{ width: batchUsagePercent(batch) + '%' }"
                            ></div>
                          </div>
                          <span class="text-xs text-gray-500 w-8 text-right">{{ batchUsagePercent(batch) }}%</span>
                        </div>
                      </td>
                      <td class="py-3 text-gray-500 max-w-xs truncate">{{ batch.supplier || '—' }}</td>
                      <td class="py-3">
                        <span
                          class="px-2 py-0.5 text-xs font-medium rounded-full"
                          :class="batchStatusClass(batch.status)"
                        >
                          {{ batchStatusLabel(batch.status) }}
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardHeader, CardTitle, CardContent } from '@/Components/ui'
import {
  Edit, Power, CheckCircle, Info, ArrowLeftRight, Layers, Plus,
  TrendingUp, TrendingDown, RefreshCw
} from 'lucide-vue-next'
import { usePermissions } from '@/composables/usePermissions'

const { can } = usePermissions()

const props = defineProps({
  product: Object,
  stats: {
    type: Object,
    default: () => ({ total_sales: 0, total_presales: 0, total_revenue: 0 })
  },
  batches: { type: Array, default: () => [] },
  movements: { type: Array, default: () => [] },
})

const activeTab = ref('info')

const activeBatchesCount = computed(() =>
  props.batches.filter(b => b.status === 'active' && b.remaining_quantity > 0).length
)

const nearestExpiry = computed(() => {
  const active = props.batches.filter(b => b.status === 'active' && b.expiry_date)
  if (!active.length) return null
  return active.reduce((min, b) => b.expiry_date < min ? b.expiry_date : min, active[0].expiry_date)
})

const tabs = computed(() => [
  { key: 'info', label: 'Información', icon: Info },
  { key: 'movements', label: 'Movimientos', icon: ArrowLeftRight, badge: props.movements.length },
  { key: 'batches', label: 'Lotes', icon: Layers, badge: activeBatchesCount.value },
])

const formatPrice = (price) => {
  if (!price && price !== 0) return '0.00'
  return new Intl.NumberFormat('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(price || 0)
}

const formatField = (value) => {
  if (!value || value === null || value === undefined || value === '' || value === '[]') return '—'
  return value
}

const formatDate = (date) => {
  if (!date) return '—'
  return new Date(date).toLocaleDateString('es-BO', { year: 'numeric', month: 'long', day: 'numeric' })
}

const formatDateShort = (date) => {
  if (!date) return '—'
  return new Date(date).toLocaleDateString('es-BO', { year: 'numeric', month: '2-digit', day: '2-digit' })
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('es-BO', {
    style: 'currency', currency: 'BOB', minimumFractionDigits: 2, maximumFractionDigits: 2,
  }).format(amount || 0)
}

const stockColorClass = computed(() => {
  if (props.product.stock_quantity === 0) return 'text-red-600'
  if (props.product.stock_quantity <= props.product.min_stock) return 'text-orange-600'
  return 'text-gray-900'
})

const isExpired = (date) => {
  if (!date) return false
  return new Date(date) < new Date()
}

const isExpiringSoon = (date) => {
  if (!date) return false
  const days = Math.ceil((new Date(date) - new Date()) / (1000 * 60 * 60 * 24))
  return days > 0 && days <= 30
}

const getExpiryDateClass = (date) => {
  if (!date) return ''
  if (isExpired(date)) return 'text-red-600 font-medium'
  if (isExpiringSoon(date)) return 'text-orange-600 font-medium'
  return 'text-gray-900'
}

const movementTypeLabel = (type) => {
  const labels = { in: 'Entrada', out: 'Salida', adjustment: 'Ajuste' }
  return labels[type] || type
}

const movementTypeClass = (type) => {
  const classes = {
    in: 'bg-green-100 text-green-700',
    out: 'bg-red-100 text-red-700',
    adjustment: 'bg-blue-100 text-blue-700',
  }
  return classes[type] || 'bg-gray-100 text-gray-700'
}

const movementTypeIcon = (type) => {
  if (type === 'in') return TrendingUp
  if (type === 'out') return TrendingDown
  return RefreshCw
}

const batchUsagePercent = (batch) => {
  if (!batch.initial_quantity) return 0
  const used = batch.initial_quantity - batch.remaining_quantity
  return Math.round((used / batch.initial_quantity) * 100)
}

const batchProgressClass = (batch) => {
  const pct = batchUsagePercent(batch)
  if (pct >= 100) return 'bg-gray-400'
  if (pct >= 75) return 'bg-orange-500'
  return 'bg-green-500'
}

const batchStatusClass = (status) => {
  const classes = {
    active: 'bg-green-100 text-green-700',
    depleted: 'bg-gray-100 text-gray-600',
    expired: 'bg-red-100 text-red-700',
  }
  return classes[status] || 'bg-gray-100 text-gray-600'
}

const batchStatusLabel = (status) => {
  const labels = { active: 'Activo', depleted: 'Agotado', expired: 'Vencido' }
  return labels[status] || status
}

const toggleStatus = () => {
  const action = props.product.is_active ? 'desactivar' : 'activar'
  if (confirm(`¿Estás seguro de ${action} este producto?`)) {
    router.post(`/productos/${props.product.id}/toggle-status`, {}, {
      preserveState: true,
      preserveScroll: true,
      onSuccess: () => {
        const past = props.product.is_active ? 'desactivado' : 'activado'
        window.$notify?.success('Producto actualizado', `El producto ha sido ${past} exitosamente.`)
      },
      onError: () => {
        window.$notify?.error('Error', `No se pudo ${action} el producto.`)
      }
    })
  }
}
</script>
