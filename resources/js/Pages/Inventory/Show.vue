<template>
  <AdminLayout>
    <div class="p-6">
      <!-- Header -->
      <div class="mb-6 flex items-center justify-between">
        <div>
          <Link href="/inventario" class="text-sm text-primary-700 hover:text-primary-800 mb-2 inline-block">
            ← Volver a inventario
          </Link>
          <h1 class="text-2xl font-bold text-gray-900">Detalle del Movimiento</h1>
          <p class="text-sm text-gray-600 mt-1">ID: {{ movement.id }}</p>
        </div>
        <div class="flex items-center gap-2">
          <Link
            :href="`/productos/${movement.product.id}`"
            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors"
          >
            Ver Producto
          </Link>
          <Link
            :href="`/inventario/crear?product=${movement.product.id}`"
            class="px-4 py-2 bg-primary-700 text-white rounded-md hover:bg-primary-800 transition-colors"
          >
            Nuevo Movimiento
          </Link>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Detalle principal -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Información del Movimiento -->
          <Card>
            <CardHeader>
              <CardTitle>Información del Movimiento</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-500">Fecha del Movimiento</label>
                  <div class="text-sm text-gray-900">{{ formatDate(movement.movement_date) }}</div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500">Tipo de Movimiento</label>
                  <div class="text-sm text-gray-900">{{ movement.movement_type_label }}</div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500">Tipo de Transacción</label>
                  <span
                    class="px-2 py-1 text-xs font-medium rounded-full"
                    :class="{
                      'bg-green-100 text-green-800': movement.transaction_type === 'in',
                      'bg-red-100 text-red-800': movement.transaction_type === 'out',
                    }"
                  >
                    {{ movement.transaction_type_label }}
                  </span>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500">Cantidad</label>
                  <div class="text-sm font-medium" :class="quantityColorClass">
                    {{ movement.transaction_type === 'in' ? '+' : '-' }}{{ movement.quantity }}
                  </div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500">Stock Anterior</label>
                  <div class="text-sm text-gray-900">{{ movement.previous_stock }}</div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500">Stock Nuevo</label>
                  <div class="text-sm text-gray-900">{{ movement.new_stock }}</div>
                </div>
              </div>

              <div v-if="movement.notes" class="mt-6">
                <label class="block text-sm font-medium text-gray-500">Observaciones</label>
                <div class="text-sm text-gray-900 whitespace-pre-line">{{ movement.notes }}</div>
              </div>
            </CardContent>
          </Card>

          <!-- Información del Producto -->
          <Card>
            <CardHeader>
              <CardTitle>Información del Producto</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-500">Nombre</label>
                  <div class="text-sm text-gray-900">{{ movement.product.name }}</div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500">Código</label>
                  <div class="text-sm text-gray-900">{{ movement.product.code }}</div>
                </div>
                <div v-if="movement.product.active_ingredient">
                  <label class="block text-sm font-medium text-gray-500">Principio Activo</label>
                  <div class="text-sm text-gray-900">{{ movement.product.active_ingredient }}</div>
                </div>
                <div v-if="movement.product.presentation">
                  <label class="block text-sm font-medium text-gray-500">Presentación</label>
                  <div class="text-sm text-gray-900">{{ movement.product.presentation }}</div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500">Stock Actual</label>
                  <div class="text-sm font-medium" :class="currentStockColorClass">
                    {{ movement.product.stock_quantity }}
                  </div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500">Stock Mínimo</label>
                  <div class="text-sm text-gray-900">{{ movement.product.min_stock }}</div>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Información de Lote -->
          <Card v-if="movement.batch_number || movement.expiry_date">
            <CardHeader>
              <CardTitle>Información de Lote</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="grid grid-cols-2 gap-4">
                <div v-if="movement.batch_number">
                  <label class="block text-sm font-medium text-gray-500">Número de Lote</label>
                  <div class="text-sm text-gray-900">{{ movement.batch_number }}</div>
                </div>
                <div v-if="movement.expiry_date">
                  <label class="block text-sm font-medium text-gray-500">Fecha de Vencimiento</label>
                  <div class="text-sm text-gray-900">{{ formatDate(movement.expiry_date) }}</div>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Información de Referencia -->
          <Card v-if="movement.reference_type || movement.reference_number">
            <CardHeader>
              <CardTitle>Referencia del Documento</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="grid grid-cols-2 gap-4">
                <div v-if="movement.reference_type">
                  <label class="block text-sm font-medium text-gray-500">Tipo de Referencia</label>
                  <div class="text-sm text-gray-900">{{ movement.reference_type }}</div>
                </div>
                <div v-if="movement.reference_number">
                  <label class="block text-sm font-medium text-gray-500">Número de Referencia</label>
                  <div class="text-sm text-gray-900">{{ movement.reference_number }}</div>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Información de Costos -->
          <Card v-if="movement.unit_cost || movement.total_cost">
            <CardHeader>
              <CardTitle>Información de Costos</CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
              <div v-if="movement.unit_cost" class="flex items-center justify-between">
                <span class="text-sm text-gray-600">Costo Unitario</span>
                <span class="text-sm text-gray-900">${{ formatPrice(movement.unit_cost) }}</span>
              </div>
              <div v-if="movement.total_cost" class="flex items-center justify-between border-t pt-2">
                <span class="text-sm font-medium text-gray-900">Costo Total</span>
                <span class="text-sm font-medium text-gray-900">${{ formatPrice(movement.total_cost) }}</span>
              </div>
            </CardContent>
          </Card>

          <!-- Información del Usuario -->
          <Card>
            <CardHeader>
              <CardTitle>Información del Usuario</CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
              <div>
                <div class="text-xs text-gray-500">Registrado por</div>
                <div class="text-sm text-gray-900">{{ movement.creator.name }}</div>
              </div>
              <div>
                <div class="text-xs text-gray-500">Fecha de registro</div>
                <div class="text-sm text-gray-900">{{ formatDateTime(movement.created_at) }}</div>
              </div>
            </CardContent>
          </Card>

          <!-- Resumen del Impacto -->
          <Card>
            <CardHeader>
              <CardTitle>Impacto en el Stock</CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">Stock anterior</span>
                <span class="text-sm text-gray-900">{{ movement.previous_stock }}</span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">Cambio</span>
                <span class="text-sm font-medium" :class="quantityColorClass">
                  {{ movement.transaction_type === 'in' ? '+' : '-' }}{{ movement.quantity }}
                </span>
              </div>
              <div class="flex items-center justify-between border-t pt-2">
                <span class="text-sm font-medium text-gray-900">Stock nuevo</span>
                <span class="text-sm font-medium text-gray-900">{{ movement.new_stock }}</span>
              </div>
            </CardContent>
          </Card>

          <!-- Acciones -->
          <div class="flex flex-col space-y-2">
            <Link
              :href="`/inventario/crear?product=${movement.product.id}`"
              class="w-full px-4 py-2 bg-primary-700 text-white rounded-md hover:bg-primary-800 text-center transition-colors"
            >
              Nuevo Movimiento
            </Link>
            <Link
              :href="`/productos/${movement.product.id}`"
              class="w-full px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-center transition-colors"
            >
              Ver Producto
            </Link>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed, watch } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardHeader, CardTitle, CardContent } from '@/Components/ui'

const props = defineProps({
  movement: Object,
})

const quantityColorClass = computed(() => {
  return props.movement.transaction_type === 'in' ? 'text-green-600' : 'text-red-600'
})

const currentStockColorClass = computed(() => {
  const stock = props.movement.product.stock_quantity
  if (stock === 0) return 'text-red-600'
  if (stock <= props.movement.product.min_stock) return 'text-orange-600'
  return 'text-gray-900'
})

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
  })
}

const formatDateTime = (date) => {
  return new Date(date).toLocaleString('es-ES', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const formatPrice = (price) => {
  return new Intl.NumberFormat('es-CO', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(price)
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
