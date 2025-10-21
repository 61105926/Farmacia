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
          <!-- Editar -->
          <Link
            v-if="can('products.update')"
            :href="`/productos/${product.id}/editar`"
            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
            title="Editar producto"
          >
            <Edit class="w-4 h-4" />
            <span>Editar</span>
          </Link>

          <!-- Activar/Desactivar -->
          <button
            v-if="can('products.update')"
            @click="toggleStatus"
            :class="[
              'inline-flex items-center gap-2 px-4 py-2 rounded-md transition-colors',
              product.is_active
                ? 'bg-yellow-600 text-white hover:bg-yellow-700'
                : 'bg-green-600 text-white hover:bg-green-700'
            ]"
            :title="product.is_active ? 'Desactivar producto' : 'Activar producto'"
          >
            <Power v-if="product.is_active" class="w-4 h-4" />
            <CheckCircle v-else class="w-4 h-4" />
            <span>{{ product.is_active ? 'Desactivar' : 'Activar' }}</span>
          </button>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Detalle principal -->
        <div class="lg:col-span-2 space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>Información del Producto</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-500">Categoría</label>
                  <div class="text-sm text-gray-900">{{ product.category?.name || 'Sin categoría' }}</div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500">Marca/Laboratorio</label>
                  <div class="text-sm text-gray-900">{{ product.brand || '—' }}</div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500">Principio activo</label>
                  <div class="text-sm text-gray-900">{{ product.active_ingredient || '—' }}</div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500">Dosificación</label>
                  <div class="text-sm text-gray-900">{{ product.dosage || '—' }}</div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500">Presentación</label>
                  <div class="text-sm text-gray-900">{{ product.presentation || '—' }}</div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500">Código de barras</label>
                  <div class="text-sm text-gray-900">{{ product.barcode || '—' }}</div>
                </div>
              </div>

              <div class="mt-6">
                <label class="block text-sm font-medium text-gray-500">Descripción</label>
                <div class="text-sm text-gray-900 whitespace-pre-line">{{ product.description || '—' }}</div>
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
                  <div class="text-sm font-medium text-gray-900">${{ formatPrice(product.cost_price) }}</div>
                </div>
                <div>
                  <div class="text-xs text-gray-500">Precio Base</div>
                  <div class="text-sm font-medium text-gray-900">${{ formatPrice(product.base_price) }}</div>
                </div>
                <div>
                  <div class="text-xs text-gray-500">Precio Venta</div>
                  <div class="text-sm font-medium text-gray-900">${{ formatPrice(product.sale_price) }}</div>
                </div>
              </div>
              <div class="mt-4">
                <div class="text-xs text-gray-500">Impuesto (%)</div>
                <div class="text-sm font-medium text-gray-900">{{ product.tax_rate ?? 0 }}%</div>
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
                <span class="text-sm font-medium" :class="stockColorClass">{{ product.stock_quantity }}</span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">Stock mínimo</span>
                <span class="text-sm text-gray-900">{{ product.min_stock }}</span>
              </div>
              <div v-if="product.stock_quantity === 0" class="text-xs text-red-600">Sin stock</div>
              <div v-else-if="product.stock_quantity <= product.min_stock" class="text-xs text-orange-600">Stock bajo</div>
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
        </div>
      </div>
    </div>
  </AdminLayout>
  </template>

<script setup>
import { computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardHeader, CardTitle, CardContent } from '@/Components/ui'
import { Edit, Power, CheckCircle } from 'lucide-vue-next'
import { usePermissions } from '@/composables/usePermissions'

const { can } = usePermissions()

const props = defineProps({
  product: Object,
})

const formatPrice = (price) => {
  return new Intl.NumberFormat('es-CO', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(price)
}

const stockColorClass = computed(() => {
  if (props.product.stock_quantity === 0) return 'text-red-600'
  if (props.product.stock_quantity <= props.product.min_stock) return 'text-orange-600'
  return 'text-gray-900'
})

const toggleStatus = () => {
  const action = props.product.is_active ? 'desactivar' : 'activar'

  if (confirm(`¿Estás seguro de ${action} este producto?`)) {
    router.post(`/productos/${props.product.id}/toggle-status`, {}, {
      preserveState: true,
      preserveScroll: true,
      onSuccess: () => {
        const actionPast = props.product.is_active ? 'activado' : 'desactivado'
        window.$notify?.success(
          'Producto actualizado',
          `El producto "${props.product.name}" ha sido ${actionPast} exitosamente.`
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

