<template>
  <AdminLayout>
    <div class="p-6">
      <!-- Header -->
      <div class="mb-6 flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Nueva Preventa</h1>
          <p class="text-sm text-gray-600 mt-1">Crear una nueva preventa</p>
        </div>
        <Link
          href="/preventas"
          class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors"
        >
          Volver
        </Link>
      </div>

      <form @submit.prevent="submitForm" class="space-y-6">
        <!-- Client Selection -->
        <Card>
          <CardHeader>
            <CardTitle>Información del Cliente</CardTitle>
          </CardHeader>
          <CardContent class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Cliente <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.client_id"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                >
                  <option value="">Seleccionar cliente</option>
                  <option v-for="client in clients" :key="client.id" :value="client.id">
                    {{ client.business_name }} - {{ client.trade_name }}
                  </option>
                </select>
                <div v-if="errors.client_id" class="text-red-500 text-sm mt-1">
                  {{ errors.client_id }}
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Vendedor
                </label>
                <select
                  v-model="form.salesperson_id"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                >
                  <option value="">Sin asignar</option>
                  <option v-for="salesperson in salespeople" :key="salesperson.id" :value="salesperson.id">
                    {{ salesperson.name }}
                  </option>
                </select>
              </div>
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
          </CardContent>
        </Card>

        <!-- Products -->
        <Card>
          <CardHeader>
            <div class="flex items-center justify-between">
              <CardTitle>Productos</CardTitle>
              <button
                type="button"
                @click="addProduct"
                class="px-4 py-2 bg-primary-700 text-white rounded-md hover:bg-primary-800 transition-colors"
              >
                Agregar Producto
              </button>
            </div>
          </CardHeader>
          <CardContent>
            <div v-if="form.items.length === 0" class="text-center py-8 text-gray-500">
              <p>No hay productos agregados</p>
              <p class="text-sm">Haz clic en "Agregar Producto" para comenzar</p>
            </div>

            <div v-else class="space-y-4">
              <div
                v-for="(item, index) in form.items"
                :key="index"
                class="grid grid-cols-1 md:grid-cols-6 gap-4 p-4 border border-gray-200 rounded-lg"
              >
                <!-- Product -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Producto</label>
                  <select
                    v-model="item.product_id"
                    @change="updateProductInfo(index)"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                  >
                    <option value="">Seleccionar</option>
                    <option v-for="product in products" :key="product.id" :value="product.id">
                      {{ product.name }}
                    </option>
                  </select>
                </div>

                <!-- Quantity -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Cantidad</label>
                  <input
                    v-model.number="item.quantity"
                    @input="calculateItemTotal(index)"
                    type="number"
                    step="0.001"
                    min="0"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                  />
                </div>

                <!-- Unit Price -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Precio Unit.</label>
                  <input
                    v-model.number="item.unit_price"
                    @input="calculateItemTotal(index)"
                    type="number"
                    step="0.01"
                    min="0"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                  />
                </div>

                <!-- Discount -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Descuento %</label>
                  <input
                    v-model.number="item.discount"
                    @input="calculateItemTotal(index)"
                    type="number"
                    step="0.01"
                    min="0"
                    max="100"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                  />
                </div>

                <!-- Total -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Total</label>
                  <div class="px-3 py-2 bg-gray-50 border border-gray-300 rounded-md text-sm font-medium">
                    {{ formatCurrency(item.total) }}
                  </div>
                </div>

                <!-- Actions -->
                <div class="flex items-end">
                  <button
                    type="button"
                    @click="removeProduct(index)"
                    class="px-3 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors"
                  >
                    Eliminar
                  </button>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Totals -->
        <Card v-if="form.items.length > 0">
          <CardHeader>
            <CardTitle>Resumen</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Subtotal</label>
                <div class="px-3 py-2 bg-gray-50 border border-gray-300 rounded-md text-lg font-medium">
                  {{ formatCurrency(form.subtotal) }}
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Total Descuentos</label>
                <div class="px-3 py-2 bg-gray-50 border border-gray-300 rounded-md text-lg font-medium">
                  {{ formatCurrency(form.total_discount) }}
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Total</label>
                <div class="px-3 py-2 bg-primary-50 border border-primary-300 rounded-md text-xl font-bold text-primary-900">
                  {{ formatCurrency(form.total) }}
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Notes -->
        <Card>
          <CardHeader>
            <CardTitle>Notas</CardTitle>
          </CardHeader>
          <CardContent>
            <textarea
              v-model="form.notes"
              rows="3"
              placeholder="Notas adicionales sobre la preventa..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
            ></textarea>
          </CardContent>
        </Card>

        <!-- Actions -->
        <div class="flex justify-end gap-4">
          <Link
            href="/preventas"
            class="px-6 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors"
          >
            Cancelar
          </Link>
          <button
            type="submit"
            :disabled="form.items.length === 0"
            class="px-6 py-2 bg-primary-700 text-white rounded-md hover:bg-primary-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Guardar Preventa
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Card from '@/Components/ui/Card.vue'
import CardHeader from '@/Components/ui/CardHeader.vue'
import CardTitle from '@/Components/ui/CardTitle.vue'
import CardContent from '@/Components/ui/CardContent.vue'

const props = defineProps({
  clients: Array,
  products: Array,
  salespeople: Array,
  errors: Object,
})

const form = reactive({
  client_id: '',
  salesperson_id: '',
  delivery_date: '',
  notes: '',
  items: [],
  subtotal: 0,
  total_discount: 0,
  total: 0,
})

const addProduct = () => {
  form.items.push({
    product_id: '',
    quantity: 1,
    unit_price: 0,
    discount: 0,
    subtotal: 0,
    discount_amount: 0,
    total: 0,
  })
}

const removeProduct = (index) => {
  form.items.splice(index, 1)
  calculateTotals()
}

const updateProductInfo = (index) => {
  const product = props.products.find(p => p.id == form.items[index].product_id)
  if (product) {
    form.items[index].unit_price = product.sale_price
    calculateItemTotal(index)
  }
}

const calculateItemTotal = (index) => {
  const item = form.items[index]
  item.subtotal = item.quantity * item.unit_price
  item.discount_amount = (item.subtotal * item.discount) / 100
  item.total = item.subtotal - item.discount_amount
  calculateTotals()
}

const calculateTotals = () => {
  form.subtotal = form.items.reduce((sum, item) => sum + item.subtotal, 0)
  form.total_discount = form.items.reduce((sum, item) => sum + item.discount_amount, 0)
  form.total = form.subtotal - form.total_discount
}

const submitForm = () => {
  if (form.items.length === 0) {
    alert('Debe agregar al menos un producto')
    return
  }

  console.log('Enviando datos de preventa:', form)
  router.post('/preventas', form, {
    onSuccess: () => {
      console.log('Preventa creada exitosamente')
    },
    onError: (errors) => {
      console.error('Errores al crear preventa:', errors)
    }
  })
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('es-BO', {
    style: 'currency',
    currency: 'BOB',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(amount || 0)
}
</script>