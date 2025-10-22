<template>
  <AdminLayout>
    <!-- Page Header -->
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Historial de Stock</h1>
          <p class="text-sm text-gray-600 mt-1">{{ product.name }} ({{ product.code }})</p>
        </div>
        <div class="flex gap-2">
          <Button
            @click="$inertia.visit(`/productos/${product.id}`)"
            variant="outline"
          >
            <ArrowLeft class="h-4 w-4 mr-2" />
            Volver al Producto
          </Button>
          <Button
            @click="$inertia.visit('/productos')"
            variant="outline"
          >
            <Package class="h-4 w-4 mr-2" />
            Todos los Productos
          </Button>
        </div>
      </div>
    </template>

    <!-- Product Info -->
    <Card class="mb-6">
      <CardContent class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <Label class="text-sm font-medium text-gray-500">Stock Actual</Label>
            <p class="text-2xl font-bold" :class="getStockClass(product.stock_quantity, product.min_stock)">
              {{ product.stock_quantity }}
            </p>
          </div>
          <div>
            <Label class="text-sm font-medium text-gray-500">Stock Mínimo</Label>
            <p class="text-2xl font-bold text-gray-900">{{ product.min_stock }}</p>
          </div>
          <div>
            <Label class="text-sm font-medium text-gray-500">Precio de Venta</Label>
            <p class="text-2xl font-bold text-green-600">{{ formatCurrency(product.sale_price) }}</p>
          </div>
          <div>
            <Label class="text-sm font-medium text-gray-500">Precio de Costo</Label>
            <p class="text-2xl font-bold text-blue-600">{{ formatCurrency(product.cost_price) }}</p>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Stock Movements Table -->
    <Card>
      <CardHeader>
        <CardTitle>Movimientos de Stock</CardTitle>
        <p class="text-sm text-gray-600">Historial completo de ajustes de stock</p>
      </CardHeader>
      <CardContent>
        <div v-if="movements.data.length === 0" class="text-center py-8">
          <Package class="h-12 w-12 text-gray-400 mx-auto mb-4" />
          <p class="text-gray-500" v-if="!$page.props.error">No hay movimientos de stock registrados</p>
          <p class="text-red-500" v-else>{{ $page.props.error }}</p>
        </div>
        
        <div v-else class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b">
                <th class="text-left py-3 px-4 font-medium text-gray-500">Fecha</th>
                <th class="text-left py-3 px-4 font-medium text-gray-500">Tipo</th>
                <th class="text-right py-3 px-4 font-medium text-gray-500">Cantidad</th>
                <th class="text-right py-3 px-4 font-medium text-gray-500">Stock Anterior</th>
                <th class="text-right py-3 px-4 font-medium text-gray-500">Stock Nuevo</th>
                <th class="text-left py-3 px-4 font-medium text-gray-500">Motivo</th>
                <th class="text-left py-3 px-4 font-medium text-gray-500">Usuario</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="movement in movements.data" :key="movement.id" class="border-b hover:bg-gray-50">
                <td class="py-3 px-4">
                  <div class="text-sm font-medium">{{ formatDate(movement.created_at) }}</div>
                  <div class="text-xs text-gray-500">{{ formatTime(movement.created_at) }}</div>
                </td>
                <td class="py-3 px-4">
                  <Badge :variant="getMovementTypeVariant(movement.type)">
                    {{ getMovementTypeLabel(movement.type) }}
                  </Badge>
                </td>
                <td class="py-3 px-4 text-right">
                  <span :class="getMovementQuantityClass(movement.type)">
                    {{ getMovementQuantitySign(movement.type) }}{{ movement.quantity }}
                  </span>
                </td>
                <td class="py-3 px-4 text-right">
                  <span class="text-sm font-medium">{{ movement.previous_stock }}</span>
                </td>
                <td class="py-3 px-4 text-right">
                  <span class="text-sm font-medium" :class="getStockClass(movement.new_stock, product.min_stock)">
                    {{ movement.new_stock }}
                  </span>
                </td>
                <td class="py-3 px-4">
                  <span class="text-sm text-gray-600">{{ movement.notes || 'Sin motivo' }}</span>
                </td>
                <td class="py-3 px-4">
                  <span class="text-sm text-gray-600">{{ movement.user_name || 'Sistema' }}</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="movements.links" class="mt-6">
          <Pagination :links="movements.links" />
        </div>
      </CardContent>
    </Card>
  </AdminLayout>
</template>

<script setup>
import { computed } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Card from '@/Components/ui/Card.vue'
import CardContent from '@/Components/ui/CardContent.vue'
import CardHeader from '@/Components/ui/CardHeader.vue'
import CardTitle from '@/Components/ui/CardTitle.vue'
import Button from '@/Components/ui/Button.vue'
import Badge from '@/Components/ui/Badge.vue'
import Label from '@/Components/ui/Label.vue'
import Pagination from '@/Components/ui/Pagination.vue'
import { 
  Package, 
  ArrowLeft 
} from 'lucide-vue-next'

const props = defineProps({
  product: Object,
  movements: Object
})

// Methods
const formatCurrency = (amount) => {
  return new Intl.NumberFormat('es-BO', {
    style: 'currency',
    currency: 'BOB'
  }).format(amount)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('es-BO')
}

const formatTime = (date) => {
  return new Date(date).toLocaleTimeString('es-BO', {
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getStockClass = (stock, minStock) => {
  if (stock <= 0) return 'text-red-600'
  if (stock <= minStock) return 'text-yellow-600'
  return 'text-green-600'
}

const getMovementTypeVariant = (type) => {
  switch (type) {
    case 'add': return 'default'
    case 'subtract': return 'destructive'
    case 'set': return 'outline'
    case 'adjustment': return 'secondary'
    default: return 'outline'
  }
}

const getMovementTypeLabel = (type) => {
  switch (type) {
    case 'add': return 'Entrada'
    case 'subtract': return 'Salida'
    case 'set': return 'Establecer'
    case 'adjustment': return 'Ajuste'
    default: return type
  }
}

const getMovementQuantityClass = (type) => {
  switch (type) {
    case 'add': return 'text-green-600 font-medium'
    case 'subtract': return 'text-red-600 font-medium'
    case 'set': return 'text-purple-600 font-medium'
    case 'adjustment': return 'text-blue-600 font-medium'
    default: return 'text-gray-600'
  }
}

const getMovementQuantitySign = (type) => {
  switch (type) {
    case 'add': return '+'
    case 'subtract': return '-'
    case 'set': return '='
    case 'adjustment': return '±'
    default: return ''
  }
}
</script>
