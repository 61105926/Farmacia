<template>
  <AdminLayout>
    <div class="p-6">
      <!-- Header -->
      <div class="mb-6">
        <Link href="/ventas" class="text-sm text-primary-700 hover:text-primary-800 mb-2 inline-block">
          ← Volver a ventas
        </Link>
        <h1 class="text-2xl font-bold text-gray-900">Editar Factura</h1>
        <p class="text-sm text-gray-600 mt-1">{{ invoice.invoice_number }}</p>
      </div>

      <form @submit.prevent="submit">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Main Form -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Información del Cliente -->
            <Card>
              <CardHeader>
                <CardTitle>Información del Cliente</CardTitle>
              </CardHeader>
              <CardContent class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Cliente <span class="text-red-500">*</span>
                  </label>
                  <select
                    v-model="form.client_id"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                    :class="{ 'border-red-500': form.errors.client_id }"
                    @change="onClientChange"
                  >
                    <option value="">Seleccionar cliente</option>
                    <option v-for="client in clients" :key="client.id" :value="client.id">
                      {{ client.business_name }} - {{ client.trade_name }}
                    </option>
                  </select>
                  <span v-if="form.errors.client_id" class="text-sm text-red-600">
                    {{ form.errors.client_id }}
                  </span>
                </div>

                <div v-if="selectedClient" class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">NIT</label>
                    <div class="text-sm text-gray-900">{{ selectedClient.tax_id || 'N/A' }}</div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                    <div class="text-sm text-gray-900">{{ selectedClient.address || 'N/A' }}</div>
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Información de la Factura -->
            <Card>
              <CardHeader>
                <CardTitle>Información de la Factura</CardTitle>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Fecha de Factura <span class="text-red-500">*</span>
                    </label>
                    <input
                      v-model="form.invoice_date"
                      type="date"
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                      :class="{ 'border-red-500': form.errors.invoice_date }"
                    />
                    <span v-if="form.errors.invoice_date" class="text-sm text-red-600">
                      {{ form.errors.invoice_date }}
                    </span>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Fecha de Vencimiento
                    </label>
                    <input
                      v-model="form.due_date"
                      type="date"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                    />
                  </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Método de Pago
                    </label>
                    <select
                      v-model="form.payment_method"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                    >
                      <option value="">Seleccionar método</option>
                      <option v-for="(label, value) in paymentMethods" :key="value" :value="value">
                        {{ label }}
                      </option>
                    </select>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Términos de Pago
                    </label>
                    <input
                      v-model="form.payment_terms"
                      type="text"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                      placeholder="Ej: 30 días"
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
                    placeholder="Observaciones de la factura"
                  ></textarea>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Términos y Condiciones
                  </label>
                  <textarea
                    v-model="form.terms_and_conditions"
                    rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                    placeholder="Términos y condiciones de la venta"
                  ></textarea>
                </div>
              </CardContent>
            </Card>

            <!-- Productos -->
            <Card>
              <CardHeader>
                <CardTitle>Productos</CardTitle>
              </CardHeader>
              <CardContent>
                <div class="space-y-4">
                  <!-- Add Product Form -->
                  <div class="border border-gray-200 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-gray-900 mb-3">Agregar Producto</h4>
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                      <div class="md:col-span-2">
                        <select
                          v-model="newItem.product_id"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                          @change="onProductSelect"
                        >
                          <option value="">Seleccionar producto</option>
                          <option v-for="product in products" :key="product.id" :value="product.id">
                            {{ product.name }} ({{ product.code }}) - Stock: {{ product.stock_quantity }}
                          </option>
                        </select>
                      </div>
                      <div>
                        <input
                          v-model.number="newItem.quantity"
                          type="number"
                          min="1"
                          :max="getNewItemMaxQuantity()"
                          placeholder="Cantidad"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                          :class="{ 'border-red-500': hasNewItemQuantityError() }"
                        />
                        <p v-if="hasNewItemQuantityError()" class="text-xs text-red-600 mt-1">
                          No hay suficiente stock. Disponible: {{ getNewItemMaxQuantity() }} {{ getNewItemUnitType() }}
                        </p>
                      </div>
                      <div>
                        <input
                          v-model.number="newItem.unit_price"
                          type="number"
                          min="0"
                          step="0.01"
                          placeholder="Precio"
                          readonly
                          class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed"
                        />
                        <p v-if="newItem.product_id" class="text-xs text-gray-600 mt-1">
                          Stock: <span class="font-semibold" :class="getNewItemStockClass()">{{ getNewItemStockText() }}</span>
                        </p>
                      </div>
                      <div>
                        <button
                          type="button"
                          @click="addItem"
                          :disabled="!canAddItem"
                          class="w-full px-4 py-2 bg-primary-700 text-white rounded-md hover:bg-primary-800 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                          Agregar
                        </button>
                      </div>
                    </div>
                  </div>

                  <!-- Items List -->
                  <div v-if="form.items.length > 0" class="space-y-2">
                    <div
                      v-for="(item, index) in form.items"
                      :key="index"
                      class="flex items-center justify-between p-3 border border-gray-200 rounded-lg"
                    >
                      <div class="flex-1">
                        <div class="text-sm font-medium text-gray-900">{{ item.product_name }}</div>
                        <div class="text-xs text-gray-500">{{ item.product_code }}</div>
                      </div>
                      <div class="flex items-center gap-4">
                        <div class="text-sm text-gray-900">{{ item.quantity }}</div>
                        <div class="text-sm text-gray-900">{{ formatPrice(item.unit_price) }}</div>
                        <div class="text-sm font-medium text-gray-900">{{ formatPrice(item.subtotal) }}</div>
                        <button
                          type="button"
                          @click="removeItem(index)"
                          class="text-red-600 hover:text-red-900"
                        >
                          <Trash2 class="w-4 h-4" />
                        </button>
                      </div>
                    </div>
                  </div>

                  <div v-else class="text-center py-8 text-gray-500">
                    <Package class="mx-auto h-12 w-12 text-gray-400" />
                    <p class="mt-2 text-sm">No hay productos agregados</p>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>

          <!-- Sidebar -->
          <div class="space-y-6">
            <!-- Resumen de la Factura -->
            <Card>
              <CardHeader>
                <CardTitle>Resumen de la Factura</CardTitle>
              </CardHeader>
              <CardContent class="space-y-3">
                <div class="flex items-center justify-between">
                  <span class="text-sm text-gray-600">Subtotal</span>
                  <span class="text-sm text-gray-900">{{ formatPrice(subtotal) }}</span>
                </div>
                <div v-if="discountAmount > 0" class="flex items-center justify-between">
                  <span class="text-sm text-gray-600">Descuento</span>
                  <span class="text-sm text-green-600">-{{ formatPrice(discountAmount) }}</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-sm text-gray-600">IVA (19%)</span>
                  <span class="text-sm text-gray-900">{{ formatPrice(taxAmount) }}</span>
                </div>
                <div class="flex items-center justify-between border-t pt-2">
                  <span class="text-sm font-medium text-gray-900">Total</span>
                  <span class="text-sm font-medium text-gray-900">{{ formatPrice(total) }}</span>
                </div>
              </CardContent>
            </Card>

            <!-- Información del Cliente -->
            <Card v-if="selectedClient">
              <CardHeader>
                <CardTitle>Información del Cliente</CardTitle>
              </CardHeader>
              <CardContent class="space-y-3">
                <div>
                  <div class="text-xs text-gray-500">Razón Social</div>
                  <div class="text-sm text-gray-900">{{ selectedClient.business_name }}</div>
                </div>
                <div>
                  <div class="text-xs text-gray-500">Nombre Comercial</div>
                  <div class="text-sm text-gray-900">{{ selectedClient.trade_name }}</div>
                </div>
                <div v-if="selectedClient.tax_id">
                  <div class="text-xs text-gray-500">NIT</div>
                  <div class="text-sm text-gray-900">{{ selectedClient.tax_id }}</div>
                </div>
                <div v-if="selectedClient.address">
                  <div class="text-xs text-gray-500">Dirección</div>
                  <div class="text-sm text-gray-900">{{ selectedClient.address }}</div>
                </div>
              </CardContent>
            </Card>

            <!-- Estado Actual -->
            <Card>
              <CardHeader>
                <CardTitle>Estado Actual</CardTitle>
              </CardHeader>
              <CardContent>
                <div class="text-center">
                  <span
                    class="px-3 py-1 text-sm font-medium rounded-full"
                    :class="getStatusColorClass(invoice.status)"
                  >
                    {{ invoice.status_label }}
                  </span>
                  <div class="mt-2 text-xs text-gray-500">
                    {{ getStatusDescription(invoice.status) }}
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Actions -->
            <div class="flex flex-col space-y-2">
              <button
                type="submit"
                :disabled="form.processing || form.items.length === 0"
                class="w-full px-4 py-2 bg-primary-700 text-white rounded-md hover:bg-primary-800 disabled:opacity-50 transition-colors"
              >
                <span v-if="form.processing">Actualizando...</span>
                <span v-else>Actualizar Factura</span>
              </button>
              <Link
                :href="`/ventas/${invoice.id}`"
                class="w-full px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-center transition-colors"
              >
                Ver Detalle
              </Link>
              <Link
                href="/ventas"
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
import { computed, ref, watch } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardHeader, CardTitle, CardContent } from '@/Components/ui'
import { Package, Trash2 } from 'lucide-vue-next'

const props = defineProps({
  invoice: Object,
  clients: Array,
  products: Array,
  paymentMethods: Object,
})

const form = useForm({
  client_id: props.invoice.client_id,
  invoice_date: props.invoice.invoice_date,
  due_date: props.invoice.due_date,
  payment_method: props.invoice.payment_method,
  payment_terms: props.invoice.payment_terms,
  notes: props.invoice.notes,
  terms_and_conditions: props.invoice.terms_and_conditions,
  items: props.invoice.items.map(item => ({
    product_id: item.product_id,
    product_code: item.product_code,
    product_name: item.product_name,
    product_description: item.product_description,
    quantity: item.quantity,
    unit_price: item.unit_price,
    discount_percentage: item.discount_percentage,
    tax_rate: item.tax_rate,
    notes: item.notes,
    subtotal: item.subtotal,
    discount_amount: item.discount_amount,
    tax_amount: item.tax_amount,
    total: item.total,
  })),
})

const newItem = ref({
  product_id: '',
  quantity: 1,
  unit_price: 0,
  discount_percentage: 0,
  notes: '',
})

const selectedClient = computed(() => {
  return props.clients.find(c => c.id == form.client_id)
})

const selectedProduct = computed(() => {
  return props.products.find(p => p.id == newItem.value.product_id)
})

const canAddItem = computed(() => {
  return newItem.value.product_id && 
         newItem.value.quantity > 0 && 
         newItem.value.unit_price > 0
})

const subtotal = computed(() => {
  return form.items.reduce((sum, item) => sum + item.subtotal, 0)
})

const discountAmount = computed(() => {
  return form.items.reduce((sum, item) => sum + (item.discount_amount || 0), 0)
})

const taxAmount = computed(() => {
  return form.items.reduce((sum, item) => sum + (item.tax_amount || 0), 0)
})

const total = computed(() => {
  return subtotal.value - discountAmount.value + taxAmount.value
})

const onClientChange = () => {
  // Resetear campos relacionados cuando cambia el cliente
  form.payment_terms = ''
}

const onProductSelect = () => {
  if (selectedProduct.value) {
    newItem.value.unit_price = selectedProduct.value.sale_price
    // Validar cantidad si excede el stock
    const maxQuantity = selectedProduct.value.stock_quantity || 0
    if (newItem.value.quantity > maxQuantity) {
      newItem.value.quantity = maxQuantity
    }
  }
}

const getNewItemStockText = () => {
  const product = selectedProduct.value
  if (!product) return 'N/A'
  const stock = product.stock_quantity || 0
  const unit = product.unit_type ? ` ${product.unit_type}` : ' unidades'
  return `${stock}${unit}`
}

const getNewItemStockClass = () => {
  const product = selectedProduct.value
  if (!product) return 'text-gray-500'
  const stock = product.stock_quantity || 0
  if (stock === 0) return 'text-red-600'
  if (stock <= (product.min_stock || 0)) return 'text-orange-600'
  return 'text-green-600'
}

const getNewItemMaxQuantity = () => {
  const product = selectedProduct.value
  if (!product) return 0
  return product.stock_quantity || 0
}

const getNewItemUnitType = () => {
  const product = selectedProduct.value
  if (!product) return 'unidades'
  return product.unit_type || 'unidades'
}

const hasNewItemQuantityError = () => {
  const product = selectedProduct.value
  if (!product) return false
  const maxQuantity = product.stock_quantity || 0
  const requestedQuantity = parseFloat(newItem.value.quantity) || 0
  return requestedQuantity > maxQuantity
}

// Watcher para validar cantidad cuando cambia
watch(() => newItem.value.quantity, (newQuantity) => {
  const product = selectedProduct.value
  if (product && newQuantity) {
    const maxQuantity = product.stock_quantity || 0
    if (newQuantity > maxQuantity) {
      newItem.value.quantity = maxQuantity
    }
  }
})

const addItem = () => {
  if (!canAddItem.value) return

  const product = selectedProduct.value
  
  // Validar stock antes de agregar
  const maxQuantity = product.stock_quantity || 0
  const requestedQuantity = parseFloat(newItem.value.quantity) || 0
  
  if (requestedQuantity > maxQuantity) {
    alert(`No hay suficiente stock. Disponible: ${maxQuantity} ${product.unit_type || 'unidades'}`)
    return
  }

  const subtotal = newItem.value.quantity * newItem.value.unit_price
  const discountAmount = subtotal * (newItem.value.discount_percentage / 100)
  const taxAmount = (subtotal - discountAmount) * 0.19 // IVA 19%
  const total = subtotal - discountAmount + taxAmount

  const item = {
    product_id: newItem.value.product_id,
    product_code: product.code,
    product_name: product.name,
    product_description: product.description,
    quantity: newItem.value.quantity,
    unit_price: newItem.value.unit_price,
    discount_percentage: newItem.value.discount_percentage,
    tax_rate: 19,
    notes: newItem.value.notes,
    subtotal: subtotal,
    discount_amount: discountAmount,
    tax_amount: taxAmount,
    total: total,
  }

  form.items.push(item)

  // Reset form
  newItem.value = {
    product_id: '',
    quantity: 1,
    unit_price: 0,
    discount_percentage: 0,
    notes: '',
  }
}

const removeItem = (index) => {
  form.items.splice(index, 1)
}

const getStatusColorClass = (status) => {
  const classes = {
    'draft': 'bg-gray-100 text-gray-800',
    'pending': 'bg-yellow-100 text-yellow-800',
    'approved': 'bg-green-100 text-green-800',
    'paid': 'bg-blue-100 text-blue-800',
    'partially_paid': 'bg-purple-100 text-purple-800',
    'overdue': 'bg-red-100 text-red-800',
    'cancelled': 'bg-gray-100 text-gray-800',
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getStatusDescription = (status) => {
  const descriptions = {
    'draft': 'Factura en borrador',
    'pending': 'Pendiente de aprobación',
    'approved': 'Factura aprobada',
    'paid': 'Factura pagada',
    'partially_paid': 'Pago parcial',
    'overdue': 'Factura vencida',
    'cancelled': 'Factura cancelada',
  }
  return descriptions[status] || 'Estado desconocido'
}

const formatPrice = (price) => {
  return new Intl.NumberFormat('es-BO', {
    style: 'currency',
    currency: 'BOB',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(price || 0)
}

const submit = () => {
  form.put(`/ventas/${props.invoice.id}`)
}
</script>
