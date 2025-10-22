<template>
  <AdminLayout>
    <div class="p-6">
      <!-- Header -->
      <div class="mb-6">
        <Link href="/preventas" class="text-sm text-primary-700 hover:text-primary-800 mb-2 inline-block">
          ← Volver a preventas
        </Link>
        <h1 class="text-2xl font-bold text-gray-900">Editar Preventa</h1>
        <p class="text-sm text-gray-600 mt-1">{{ order.order_number }}</p>
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
                    <label class="block text-sm font-medium text-gray-700 mb-1">Vendedor</label>
                    <select
                      v-model="form.salesperson_id"
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                      :class="{ 'border-red-500': form.errors.salesperson_id }"
                    >
                      <option value="">Seleccionar vendedor</option>
                      <option v-for="salesperson in salespeople" :key="salesperson.id" :value="salesperson.id">
                        {{ salesperson.name }}
                      </option>
                    </select>
                    <span v-if="form.errors.salesperson_id" class="text-sm text-red-600">
                      {{ form.errors.salesperson_id }}
                    </span>
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Información del Pedido -->
            <Card>
              <CardHeader>
                <CardTitle>Información del Pedido</CardTitle>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Fecha del Pedido <span class="text-red-500">*</span>
                    </label>
                    <input
                      v-model="form.order_date"
                      type="date"
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                      :class="{ 'border-red-500': form.errors.order_date }"
                    />
                    <span v-if="form.errors.order_date" class="text-sm text-red-600">
                      {{ form.errors.order_date }}
                    </span>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Fecha de Entrega
                    </label>
                    <input
                      v-model="form.delivery_date"
                      type="date"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                    />
                  </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Descuento (%)
                    </label>
                    <input
                      v-model.number="form.discount_percentage"
                      type="number"
                      min="0"
                      max="100"
                      step="0.01"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Costo de Envío
                    </label>
                    <input
                      v-model.number="form.shipping_cost"
                      type="number"
                      min="0"
                      step="0.01"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                    />
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Referencia
                  </label>
                  <input
                    v-model="form.reference"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                    placeholder="Referencia del pedido"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Observaciones
                  </label>
                  <textarea
                    v-model="form.notes"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                    placeholder="Observaciones del pedido"
                  ></textarea>
                </div>
              </CardContent>
            </Card>

            <!-- Dirección de Entrega -->
            <Card>
              <CardHeader>
                <CardTitle>Dirección de Entrega</CardTitle>
              </CardHeader>
              <CardContent class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Dirección
                  </label>
                  <textarea
                    v-model="form.delivery_address"
                    rows="2"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                    placeholder="Dirección completa de entrega"
                  ></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Ciudad
                    </label>
                    <input
                      v-model="form.delivery_city"
                      type="text"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Departamento
                    </label>
                    <input
                      v-model="form.delivery_state"
                      type="text"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                    />
                  </div>
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
                          min="0.01"
                          step="0.01"
                          placeholder="Cantidad"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                        />
                      </div>
                      <div>
                        <input
                          v-model.number="newItem.unit_price"
                          type="number"
                          min="0"
                          step="0.01"
                          placeholder="Precio"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                        />
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
                        <div class="text-sm text-gray-900">${{ formatPrice(item.unit_price) }}</div>
                        <div class="text-sm font-medium text-gray-900">${{ formatPrice(item.subtotal) }}</div>
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
            <!-- Resumen del Pedido -->
            <Card>
              <CardHeader>
                <CardTitle>Resumen del Pedido</CardTitle>
              </CardHeader>
              <CardContent class="space-y-3">
                <div class="flex items-center justify-between">
                  <span class="text-sm text-gray-600">Subtotal</span>
                  <span class="text-sm text-gray-900">${{ formatPrice(subtotal) }}</span>
                </div>
                <div v-if="discountAmount > 0" class="flex items-center justify-between">
                  <span class="text-sm text-gray-600">Descuento ({{ form.discount_percentage }}%)</span>
                  <span class="text-sm text-green-600">-${{ formatPrice(discountAmount) }}</span>
                </div>
                <div v-if="form.shipping_cost > 0" class="flex items-center justify-between">
                  <span class="text-sm text-gray-600">Envío</span>
                  <span class="text-sm text-gray-900">${{ formatPrice(form.shipping_cost) }}</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-sm text-gray-600">IVA (19%)</span>
                  <span class="text-sm text-gray-900">${{ formatPrice(taxAmount) }}</span>
                </div>
                <div class="flex items-center justify-between border-t pt-2">
                  <span class="text-sm font-medium text-gray-900">Total</span>
                  <span class="text-sm font-medium text-gray-900">${{ formatPrice(total) }}</span>
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
              </CardContent>
            </Card>

            <!-- Estado Actual -->
            <Card>
              <CardHeader>
                <CardTitle>Estado Actual</CardTitle>
              </CardHeader>
              <CardContent>
                <span
                  class="px-2 py-1 text-xs font-medium rounded-full"
                  :class="getStatusColorClass(order.status)"
                >
                  {{ order.status_label }}
                </span>
                <div class="mt-2 text-xs text-gray-500">
                  Última actualización: {{ formatDateTime(order.updated_at) }}
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
                <span v-else>Actualizar Preventa</span>
              </button>
              <Link
                :href="`/preventas/${order.id}`"
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
import { Package, Trash2 } from 'lucide-vue-next'

const props = defineProps({
  order: Object,
  clients: Array,
  products: Array,
  salespeople: Array,
})

const form = useForm({
  client_id: props.order.client_id,
  order_date: props.order.order_date,
  delivery_date: props.order.delivery_date || '',
  salesperson_id: props.order.salesperson_id,
  delivery_address: props.order.delivery_address || '',
  delivery_city: props.order.delivery_city || '',
  delivery_state: props.order.delivery_state || '',
  discount_percentage: props.order.discount_percentage || 0,
  shipping_cost: props.order.shipping_cost || 0,
  notes: props.order.notes || '',
  reference: props.order.reference || '',
  items: props.order.items.map(item => ({
    product_id: item.product_id,
    product_code: item.product_code,
    product_name: item.product_name,
    product_description: item.product_description,
    quantity: item.quantity,
    unit_price: item.unit_price,
    discount_percentage: item.discount_percentage || 0,
    tax_rate: item.tax_rate || 19,
    notes: item.notes || '',
    subtotal: item.subtotal,
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
  return subtotal.value * (form.discount_percentage / 100)
})

const taxAmount = computed(() => {
  return (subtotal.value - discountAmount.value) * 0.19
})

const total = computed(() => {
  return subtotal.value - discountAmount.value + taxAmount.value + form.shipping_cost
})

const onClientChange = () => {
  // Resetear campos relacionados cuando cambia el cliente
  form.delivery_address = ''
  form.delivery_city = ''
  form.delivery_state = ''
}

const onProductSelect = () => {
  if (selectedProduct.value) {
    newItem.value.unit_price = selectedProduct.value.sale_price
  }
}

const addItem = () => {
  if (!canAddItem.value) return

  const product = selectedProduct.value
  const item = {
    product_id: newItem.value.product_id,
    product_code: product.code,
    product_name: product.name,
    product_description: product.description,
    quantity: newItem.value.quantity,
    unit_price: newItem.value.unit_price,
    discount_percentage: newItem.value.discount_percentage,
    tax_rate: product.tax_rate || 19,
    notes: newItem.value.notes,
    subtotal: newItem.value.quantity * newItem.value.unit_price,
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
    'confirmed': 'bg-green-100 text-green-800',
    'processing': 'bg-blue-100 text-blue-800',
    'shipped': 'bg-purple-100 text-purple-800',
    'delivered': 'bg-green-100 text-green-800',
    'cancelled': 'bg-red-100 text-red-800',
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const formatPrice = (price) => {
  return new Intl.NumberFormat('es-CO', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(price)
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

const submit = () => {
  form.put(`/preventas/${props.order.id}`)
}
</script>
