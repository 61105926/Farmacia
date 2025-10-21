<template>
  <AdminLayout>
    <div class="p-6">
      <!-- Header -->
      <div class="mb-6 flex items-center justify-between">
        <div>
          <Link href="/inventario" class="text-sm text-primary-700 hover:text-primary-800 mb-2 inline-block">
            ← Volver a inventario
          </Link>
          <h1 class="text-2xl font-bold text-gray-900">Productos por Vencer</h1>
          <p class="text-sm text-gray-600 mt-1">Productos próximos a vencer (próximos 30 días)</p>
        </div>
        <div class="flex items-center gap-2">
          <Link
            href="/inventario/crear"
            class="px-4 py-2 bg-primary-700 text-white rounded-md hover:bg-primary-800 transition-colors"
          >
            Nuevo Movimiento
          </Link>
          <button
            @click="exportExpired"
            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors"
          >
            Exportar
          </button>
        </div>
      </div>

      <!-- Alert -->
      <div class="mb-6 bg-red-50 border border-red-200 rounded-md p-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <AlertTriangle class="h-5 w-5 text-red-400" />
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">
              Atención: {{ products.data.length }} productos próximos a vencer
            </h3>
            <div class="mt-2 text-sm text-red-700">
              <p>Estos productos tienen fecha de vencimiento en los próximos 30 días. Se recomienda revisar el stock y aplicar estrategias de rotación.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Products Table -->
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
                    Fecha Vencimiento
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Días Restantes
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Estado
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Lote
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
                      <span class="text-sm font-medium text-gray-900">
                        {{ product.stock_quantity }}
                      </span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatDate(product.expiry_date) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      class="text-sm font-medium"
                      :class="{
                        'text-red-600': getDaysUntilExpiry(product.expiry_date) <= 7,
                        'text-orange-600': getDaysUntilExpiry(product.expiry_date) <= 15,
                        'text-yellow-600': getDaysUntilExpiry(product.expiry_date) <= 30,
                      }"
                    >
                      {{ getDaysUntilExpiry(product.expiry_date) }} días
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      class="px-2 py-1 text-xs font-medium rounded-full"
                      :class="{
                        'bg-red-100 text-red-800': getDaysUntilExpiry(product.expiry_date) <= 7,
                        'bg-orange-100 text-orange-800': getDaysUntilExpiry(product.expiry_date) <= 15,
                        'bg-yellow-100 text-yellow-800': getDaysUntilExpiry(product.expiry_date) <= 30,
                      }"
                    >
                      {{ getExpiryStatus(product.expiry_date) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ product.batch_number || '—' }}
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
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Empty State -->
          <div v-if="products.data.length === 0" class="text-center py-12">
            <CheckCircle class="mx-auto h-12 w-12 text-green-400" />
            <h3 class="mt-2 text-sm font-medium text-gray-900">¡Excelente!</h3>
            <p class="mt-1 text-sm text-gray-500">No hay productos próximos a vencer en los próximos 30 días.</p>
            <div class="mt-6">
              <Link
                href="/inventario"
                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-700 hover:bg-primary-800"
              >
                Ver Inventario Completo
              </Link>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Pagination -->
      <div v-if="products.data.length > 0" class="mt-6">
        <Pagination :links="products.links" />
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardContent } from '@/Components/ui'
import Pagination from '@/Components/Pagination.vue'
import { AlertTriangle, CheckCircle } from 'lucide-vue-next'

const props = defineProps({
  products: Object,
})

const getDaysUntilExpiry = (expiryDate) => {
  const today = new Date()
  const expiry = new Date(expiryDate)
  const diffTime = expiry - today
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  return Math.max(0, diffDays)
}

const getExpiryStatus = (expiryDate) => {
  const days = getDaysUntilExpiry(expiryDate)
  if (days <= 7) return 'Crítico'
  if (days <= 15) return 'Urgente'
  if (days <= 30) return 'Próximo'
  return 'Normal'
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
  })
}

const exportExpired = () => {
  // Implementar exportación de productos próximos a vencer
  window.open('/inventario/por-vencer/export', '_blank')
}
</script>
