<template>
  <AdminLayout>
    <div class="p-6">
      <!-- Header -->
      <div class="mb-6 flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Nueva Venta</h1>
          <p class="text-sm text-gray-600 mt-1">Crear una nueva venta</p>
        </div>
        <Link
          href="/ventas"
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
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Preventa
                </label>
                <select
                  v-model="form.presale_id"
                  @change="loadPresaleItems"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                >
                  <option value="">Sin preventa</option>
                  <option v-for="presale in presales" :key="presale.id" :value="presale.id">
                    {{ presale.code }} - {{ presale.total }} Bs
                  </option>
                </select>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Método de Pago <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.payment_method"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                >
                  <option value="">Seleccionar método</option>
                  <option value="cash">Efectivo</option>
                  <option value="credit">Crédito</option>
                  <option value="transfer">Transferencia</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Estado de Pago <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.payment_status"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                >
                  <option value="">Seleccionar estado</option>
                  <option value="paid">Pagado</option>
                  <option value="pending">Pendiente</option>
                  <option value="partial">Pago Parcial</option>
                </select>
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

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Número de Factura
                </label>
                <input
                  v-model="form.invoice_number"
                  type="text"
                  placeholder="Ej: FAC-001-2024"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                />
              </div>
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
                      {{ product.description || product.name || 'Sin descripción' }}
                    </option>
                  </select>
                </div>

                <!-- Quantity -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Cantidad</label>
                  <input
                    v-model.number="item.quantity"
                    @input="validateQuantity(index)"
                    type="number"
                    step="0.001"
                    min="0"
                    :max="getMaxQuantity(index)"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                    :class="{ 'border-red-500': hasQuantityError(index) }"
                  />
                  <p v-if="hasQuantityError(index)" class="text-xs text-red-600 mt-1">
                    No hay suficiente stock. Disponible: {{ getMaxQuantity(index) }} {{ getUnitType(index) }}
                  </p>
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
                    readonly
                    class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed"
                  />
                  <p v-if="form.items[index].product_id" class="text-xs text-gray-600 mt-1">
                    Stock disponible: <span class="font-semibold" :class="getStockClass(index)">{{ getStockText(index) }}</span>
                    <span v-if="getMaxQuantity(index) === 0" class="ml-2 text-red-600 font-medium">⚠️ Sin stock</span>
                  </p>
                  <p v-else class="text-xs text-gray-400 mt-1 italic">
                    Seleccione un producto para ver el stock
                  </p>
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
              placeholder="Notas adicionales sobre la venta..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
            ></textarea>
          </CardContent>
        </Card>

        <!-- Actions -->
        <div class="flex justify-end gap-4">
          <Link
            href="/ventas"
            class="px-6 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors"
          >
            Cancelar
          </Link>
          <button
            type="submit"
            :disabled="form.items.length === 0"
            class="px-6 py-2 bg-primary-700 text-white rounded-md hover:bg-primary-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Guardar Venta
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Card from '@/Components/ui/Card.vue'
import CardHeader from '@/Components/ui/CardHeader.vue'
import CardTitle from '@/Components/ui/CardTitle.vue'
import CardContent from '@/Components/ui/CardContent.vue'

const props = defineProps({
  clients: Array,
  products: Array,
  salespeople: Array,
  presales: Array,
  errors: Object,
})

const form = reactive({
  client_id: '',
  salesperson_id: '',
  presale_id: '',
  payment_method: '',
  payment_status: '',
  delivery_date: '',
  invoice_number: '',
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

const getStockText = (index) => {
  const product = props.products.find(p => p.id == form.items[index].product_id)
  if (!product) return 'N/A'
  const stock = product.stock_quantity || 0
  const unit = product.unit_type ? ` ${product.unit_type}` : ' unidades'
  return `${stock}${unit}`
}

const getStockClass = (index) => {
  const product = props.products.find(p => p.id == form.items[index].product_id)
  if (!product) return 'text-gray-500'
  const stock = product.stock_quantity || 0
  if (stock === 0) return 'text-red-600'
  if (stock <= (product.min_stock || 0)) return 'text-orange-600'
  return 'text-green-600'
}

const getMaxQuantity = (index) => {
  const product = props.products.find(p => p.id == form.items[index].product_id)
  if (!product) return 0
  return product.stock_quantity || 0
}

const getUnitType = (index) => {
  const product = props.products.find(p => p.id == form.items[index].product_id)
  if (!product) return 'unidades'
  return product.unit_type || 'unidades'
}

const hasQuantityError = (index) => {
  const product = props.products.find(p => p.id == form.items[index].product_id)
  if (!product) return false
  const maxQuantity = product.stock_quantity || 0
  const requestedQuantity = parseFloat(form.items[index].quantity) || 0
  return requestedQuantity > maxQuantity
}

const validateQuantity = (index) => {
  const product = props.products.find(p => p.id == form.items[index].product_id)
  if (!product) return
  
  const maxQuantity = product.stock_quantity || 0
  const requestedQuantity = parseFloat(form.items[index].quantity) || 0
  
  if (requestedQuantity > maxQuantity) {
    form.items[index].quantity = maxQuantity
  }
  
  calculateItemTotal(index)
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
  // Validar que haya items
  if (form.items.length === 0) {
    alert('Debe agregar al menos un producto')
    return
  }

  // Validar que todos los items tengan producto seleccionado
  const itemsWithProducts = form.items.filter(item => item.product_id && item.product_id !== '')
  if (itemsWithProducts.length === 0) {
    alert('Debe seleccionar al menos un producto')
    return
  }

  // Validar cliente
  if (!form.client_id || form.client_id === '') {
    alert('Debe seleccionar un cliente')
    return
  }

  // Validar método de pago
  if (!form.payment_method || form.payment_method === '') {
    alert('Debe seleccionar un método de pago')
    return
  }

  // Validar estado de pago
  if (!form.payment_status || form.payment_status === '') {
    alert('Debe seleccionar un estado de pago')
    return
  }

  // Preparar datos para enviar - solo items con producto seleccionado
  const dataToSend = {
    ...form,
    items: itemsWithProducts.map(item => ({
      product_id: item.product_id,
      quantity: item.quantity || 1,
      unit_price: item.unit_price || 0,
      discount: item.discount || 0,
    })),
    // Si delivery_date está vacío, enviar null
    delivery_date: form.delivery_date || null,
    // Si presale_id está vacío, enviar null
    presale_id: form.presale_id || null,
    // Si salesperson_id está vacío, enviar null
    salesperson_id: form.salesperson_id || null,
  }

  console.log('Enviando datos de venta:', dataToSend)
  
  router.post('/ventas', dataToSend, {
    preserveScroll: true,
    onSuccess: () => {
      console.log('Venta creada exitosamente')
      // Redirigir a la lista de ventas
      router.visit('/ventas')
    },
    onError: (errors) => {
      console.error('Errores al crear venta:', errors)
      // Mostrar errores de validación
      if (errors) {
        const errorMessages = Object.values(errors).flat()
        alert('Error al crear la venta:\n' + errorMessages.join('\n'))
      }
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

const loadPresaleItems = async () => {
  if (!form.presale_id) {
    // Si no hay preventa seleccionada, limpiar los items
    form.items = []
    form.subtotal = 0
    form.total_discount = 0
    form.total = 0
    return
  }

  try {
    const response = await axios.get(`/ventas/preventa/${form.presale_id}/items`)
    const data = response.data

    if (data.error) {
      alert(data.error)
      form.presale_id = ''
      return
    }

    // Cargar el cliente de la preventa
    if (data.client_id) {
      form.client_id = data.client_id
    }

    // Cargar los items de la preventa
    form.items = data.items.map(item => ({
      product_id: item.product_id,
      quantity: item.quantity,
      unit_price: item.unit_price,
      discount: item.discount,
      subtotal: item.subtotal,
      discount_amount: item.discount_amount,
      total: item.total,
    }))

    // Actualizar los totales
    form.subtotal = data.subtotal || 0
    form.total_discount = data.total_discount || 0
    form.total = data.total || 0
  } catch (error) {
    console.error('Error al cargar items de preventa:', error)
    alert('Error al cargar los productos de la preventa. Por favor, intente nuevamente.')
    form.presale_id = ''
  }
}
</script>