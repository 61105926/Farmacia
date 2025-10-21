<template>
  <AdminLayout>
    <div class="p-6">
      <!-- Header -->
      <div class="mb-6">
        <Link href="/inventario" class="text-sm text-primary-700 hover:text-primary-800 mb-2 inline-block">
          ← Volver a inventario
        </Link>
        <h1 class="text-2xl font-bold text-gray-900">Nuevo Movimiento de Inventario</h1>
        <p class="text-sm text-gray-600 mt-1">Registre un movimiento de entrada o salida de productos</p>
      </div>

      <form @submit.prevent="submit">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Main Form -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Información del Movimiento -->
            <Card>
              <CardHeader>
                <CardTitle>Información del Movimiento</CardTitle>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Producto <span class="text-red-500">*</span>
                    </label>
                    <select
                      v-model="form.product_id"
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                      :class="{ 'border-red-500': form.errors.product_id }"
                      @change="onProductChange"
                    >
                      <option value="">Seleccionar producto</option>
                      <option v-for="product in products" :key="product.id" :value="product.id">
                        {{ product.name }} ({{ product.code }}) - Stock: {{ product.stock_quantity }}
                      </option>
                    </select>
                    <span v-if="form.errors.product_id" class="text-sm text-red-600">
                      {{ form.errors.product_id }}
                    </span>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Fecha del Movimiento <span class="text-red-500">*</span>
                    </label>
                    <input
                      v-model="form.movement_date"
                      type="date"
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                      :class="{ 'border-red-500': form.errors.movement_date }"
                    />
                    <span v-if="form.errors.movement_date" class="text-sm text-red-600">
                      {{ form.errors.movement_date }}
                    </span>
                  </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Tipo de Movimiento <span class="text-red-500">*</span>
                    </label>
                    <select
                      v-model="form.movement_type"
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                      :class="{ 'border-red-500': form.errors.movement_type }"
                    >
                      <option value="">Seleccionar tipo</option>
                      <option v-for="(label, value) in movementTypes" :key="value" :value="value">
                        {{ label }}
                      </option>
                    </select>
                    <span v-if="form.errors.movement_type" class="text-sm text-red-600">
                      {{ form.errors.movement_type }}
                    </span>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Tipo de Transacción <span class="text-red-500">*</span>
                    </label>
                    <select
                      v-model="form.transaction_type"
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                      :class="{ 'border-red-500': form.errors.transaction_type }"
                    >
                      <option value="">Seleccionar transacción</option>
                      <option v-for="(label, value) in transactionTypes" :key="value" :value="value">
                        {{ label }}
                      </option>
                    </select>
                    <span v-if="form.errors.transaction_type" class="text-sm text-red-600">
                      {{ form.errors.transaction_type }}
                    </span>
                  </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Cantidad <span class="text-red-500">*</span>
                    </label>
                    <input
                      v-model.number="form.quantity"
                      type="number"
                      min="1"
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                      :class="{ 'border-red-500': form.errors.quantity }"
                    />
                    <span v-if="form.errors.quantity" class="text-sm text-red-600">
                      {{ form.errors.quantity }}
                    </span>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Costo Unitario
                    </label>
                    <input
                      v-model.number="form.unit_cost"
                      type="number"
                      step="0.01"
                      min="0"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                    />
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Observaciones
                  </label>
                  <textarea
                    v-model="form.notes"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                    placeholder="Motivo del movimiento, referencia, etc."
                  ></textarea>
                </div>
              </CardContent>
            </Card>

            <!-- Información de Lote (Opcional) -->
            <Card>
              <CardHeader>
                <CardTitle>Información de Lote (Opcional)</CardTitle>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Número de Lote
                    </label>
                    <input
                      v-model="form.batch_number"
                      type="text"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                      placeholder="Ej: L2024001"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Fecha de Vencimiento
                    </label>
                    <input
                      v-model="form.expiry_date"
                      type="date"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                    />
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Referencia (Opcional) -->
            <Card>
              <CardHeader>
                <CardTitle>Referencia del Documento (Opcional)</CardTitle>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Tipo de Referencia
                    </label>
                    <select
                      v-model="form.reference_type"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                    >
                      <option value="">Sin referencia</option>
                      <option value="order">Orden de Compra</option>
                      <option value="invoice">Factura</option>
                      <option value="transfer">Transferencia</option>
                      <option value="adjustment">Ajuste</option>
                    </select>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Número de Referencia
                    </label>
                    <input
                      v-model="form.reference_number"
                      type="text"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                      placeholder="Ej: OC-2024-001"
                    />
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>

          <!-- Sidebar -->
          <div class="space-y-6">
            <!-- Resumen del Producto -->
            <Card v-if="selectedProduct">
              <CardHeader>
                <CardTitle>Información del Producto</CardTitle>
              </CardHeader>
              <CardContent class="space-y-3">
                <div>
                  <div class="text-xs text-gray-500">Nombre</div>
                  <div class="text-sm font-medium text-gray-900">{{ selectedProduct.name }}</div>
                </div>
                <div>
                  <div class="text-xs text-gray-500">Código</div>
                  <div class="text-sm text-gray-900">{{ selectedProduct.code }}</div>
                </div>
                <div>
                  <div class="text-xs text-gray-500">Stock Actual</div>
                  <div class="text-sm font-medium" :class="stockColorClass">
                    {{ selectedProduct.stock_quantity }}
                  </div>
                </div>
                <div v-if="form.quantity && form.transaction_type">
                  <div class="text-xs text-gray-500">Stock Después del Movimiento</div>
                  <div class="text-sm font-medium" :class="newStockColorClass">
                    {{ newStock }}
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Resumen del Movimiento -->
            <Card>
              <CardHeader>
                <CardTitle>Resumen del Movimiento</CardTitle>
              </CardHeader>
              <CardContent class="space-y-3">
                <div v-if="form.transaction_type" class="flex items-center justify-between">
                  <span class="text-sm text-gray-600">Tipo</span>
                  <span
                    class="px-2 py-1 text-xs font-medium rounded-full"
                    :class="{
                      'bg-green-100 text-green-800': form.transaction_type === 'in',
                      'bg-red-100 text-red-800': form.transaction_type === 'out',
                    }"
                  >
                    {{ transactionTypes[form.transaction_type] }}
                  </span>
                </div>
                <div v-if="form.movement_type" class="flex items-center justify-between">
                  <span class="text-sm text-gray-600">Movimiento</span>
                  <span class="text-sm text-gray-900">{{ movementTypes[form.movement_type] }}</span>
                </div>
                <div v-if="form.quantity" class="flex items-center justify-between">
                  <span class="text-sm text-gray-600">Cantidad</span>
                  <span class="text-sm font-medium text-gray-900">{{ form.quantity }}</span>
                </div>
                <div v-if="form.unit_cost" class="flex items-center justify-between">
                  <span class="text-sm text-gray-600">Costo Unitario</span>
                  <span class="text-sm text-gray-900">${{ formatPrice(form.unit_cost) }}</span>
                </div>
                <div v-if="form.unit_cost && form.quantity" class="flex items-center justify-between border-t pt-2">
                  <span class="text-sm font-medium text-gray-900">Total</span>
                  <span class="text-sm font-medium text-gray-900">${{ formatPrice(form.unit_cost * form.quantity) }}</span>
                </div>
              </CardContent>
            </Card>

            <!-- Actions -->
            <div class="flex flex-col space-y-2">
              <button
                type="submit"
                :disabled="form.processing"
                class="w-full px-4 py-2 bg-primary-700 text-white rounded-md hover:bg-primary-800 disabled:opacity-50 transition-colors"
              >
                <span v-if="form.processing">Registrando...</span>
                <span v-else>Registrar Movimiento</span>
              </button>
              <Link
                href="/inventario"
                class="w-full px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-center transition-colors"
              >
                Cancelar
              </Link>
            </div>
          </div>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardHeader, CardTitle, CardContent } from '@/Components/ui'

const props = defineProps({
  products: Array,
  movementTypes: Object,
  transactionTypes: Object,
})

const form = useForm({
  product_id: '',
  movement_type: '',
  transaction_type: '',
  quantity: 1,
  movement_date: new Date().toISOString().split('T')[0],
  unit_cost: 0,
  total_cost: 0,
  notes: '',
  batch_number: '',
  expiry_date: '',
  reference_type: '',
  reference_id: null,
  reference_number: '',
})

const selectedProduct = computed(() => {
  return props.products.find(p => p.id == form.product_id)
})

const newStock = computed(() => {
  if (!selectedProduct.value || !form.quantity || !form.transaction_type) return 0
  
  const currentStock = selectedProduct.value.stock_quantity
  const change = form.transaction_type === 'in' ? form.quantity : -form.quantity
  return Math.max(0, currentStock + change)
})

const stockColorClass = computed(() => {
  if (!selectedProduct.value) return 'text-gray-900'
  
  const stock = selectedProduct.value.stock_quantity
  if (stock === 0) return 'text-red-600'
  if (stock <= 10) return 'text-orange-600'
  return 'text-gray-900'
})

const newStockColorClass = computed(() => {
  if (newStock.value === 0) return 'text-red-600'
  if (newStock.value <= 10) return 'text-orange-600'
  return 'text-gray-900'
})

const onProductChange = () => {
  // Resetear campos relacionados cuando cambia el producto
  form.batch_number = ''
  form.expiry_date = ''
}

const formatPrice = (price) => {
  return new Intl.NumberFormat('es-CO', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(price)
}

const submit = () => {
  form.post('/inventario')
}
</script>
