<template>
  <AdminLayout>
    <div class="p-6">
      <!-- Header -->
      <div class="mb-6">
        <Link href="/ventas" class="text-sm text-primary-700 hover:text-primary-800 mb-2 inline-block">
          ← Volver a ventas
        </Link>
        <h1 class="text-2xl font-bold text-gray-900">Editar Venta</h1>
        <p class="text-sm text-gray-600 mt-1">{{ sale?.code || invoice?.code || 'N/A' }}</p>
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
                <CardTitle>Información de la Venta</CardTitle>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Método de Pago <span class="text-red-500">*</span>
                    </label>
                    <select
                      v-model="form.payment_method"
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                      :class="{ 'border-red-500': form.errors.payment_method }"
                    >
                      <option value="">Seleccionar método</option>
                      <option value="cash">Efectivo</option>
                      <option value="credit">Crédito</option>
                      <option value="transfer">Transferencia</option>
                    </select>
                    <span v-if="form.errors.payment_method" class="text-sm text-red-600">
                      {{ form.errors.payment_method }}
                    </span>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Estado de Pago <span class="text-red-500">*</span>
                    </label>
                    <select
                      v-model="form.payment_status"
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                      :class="{ 'border-red-500': form.errors.payment_status }"
                    >
                      <option value="">Seleccionar estado</option>
                      <option value="paid">Pagado</option>
                      <option value="pending">Pendiente</option>
                      <option value="partial">Pago Parcial</option>
                    </select>
                    <span v-if="form.errors.payment_status" class="text-sm text-red-600">
                      {{ form.errors.payment_status }}
                    </span>
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
                    :class="{ 'border-red-500': form.errors.delivery_date }"
                  />
                  <span v-if="form.errors.delivery_date" class="text-sm text-red-600">
                    {{ form.errors.delivery_date }}
                  </span>
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

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Observaciones
                  </label>
                  <textarea
                    v-model="form.notes"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                    placeholder="Observaciones de la venta"
                    :class="{ 'border-red-500': form.errors.notes }"
                  ></textarea>
                  <span v-if="form.errors.notes" class="text-sm text-red-600">
                    {{ form.errors.notes }}
                  </span>
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
                            {{ product.description || product.name || 'Sin descripción' }} ({{ product.code }}) - Stock: {{ product.stock_quantity }}
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
                  <div v-if="getValidItems().length > 0" class="space-y-2">
                    <div
                      v-for="(item, index) in getValidItems()"
                      :key="index"
                      class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50"
                    >
                      <div class="flex-1">
                        <div class="text-sm font-medium text-gray-900">{{ item.product_name || 'Producto sin nombre' }}</div>
                        <div class="text-xs text-gray-500">{{ item.product_code || 'Sin código' }}</div>
                        <div v-if="item.discount > 0" class="text-xs text-green-600 mt-1">
                          Descuento: {{ item.discount }}%
                        </div>
                      </div>
                      <div class="flex items-center gap-4">
                        <div class="text-sm text-gray-900">
                          <span class="text-gray-500">Cant:</span> {{ item.quantity }}
                        </div>
                        <div class="text-sm text-gray-900">
                          <span class="text-gray-500">Precio:</span> {{ formatPrice(item.unit_price) }}
                        </div>
                        <div class="text-sm font-medium text-gray-900">
                          <span class="text-gray-500">Total:</span> {{ formatPrice(item.total) }}
                        </div>
                        <button
                          type="button"
                          @click="removeItemByIndex(index)"
                          class="text-red-600 hover:text-red-900 p-1"
                          title="Eliminar producto"
                        >
                          <Trash2 class="w-4 h-4" />
                        </button>
                      </div>
                    </div>
                  </div>

                  <div v-else-if="getValidItems().length === 0" class="text-center py-8 text-gray-500">
                    <Package class="mx-auto h-12 w-12 text-gray-400" />
                    <p class="mt-2 text-sm">No hay productos agregados</p>
                    <p v-if="form.items.length > getValidItems().length" class="mt-1 text-xs text-red-600">
                      Hay {{ form.items.length - getValidItems().length }} producto(s) con datos inválidos
                    </p>
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
                <CardTitle>Resumen de la Venta</CardTitle>
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
                    :class="getStatusColorClass(saleData.status)"
                  >
                    {{ getStatusText(saleData.status) }}
                  </span>
                  <div class="mt-2 text-xs text-gray-500">
                    {{ getStatusDescription(saleData.status) }}
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Actions -->
            <div class="flex flex-col space-y-2">
              <button
                type="submit"
                :disabled="form.processing || getValidItems().length === 0"
                class="w-full px-4 py-2 bg-primary-700 text-white rounded-md hover:bg-primary-800 disabled:opacity-50 transition-colors"
              >
                <span v-if="form.processing">Actualizando...</span>
                <span v-else>Actualizar Venta</span>
              </button>
              <Link
                :href="`/ventas/${saleData.id}`"
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
  sale: Object,
  invoice: Object, // Para compatibilidad
  clients: Array,
  products: Array,
  paymentMethods: Object,
})

const saleData = props.sale || props.invoice

const form = useForm({
  client_id: saleData.client_id || '',
  payment_method: saleData.payment_method || 'cash',
  payment_status: saleData.payment_status || 'pending',
  invoice_number: saleData.invoice_number || '',
  notes: saleData.notes || '',
  delivery_date: saleData.delivery_date || null,
  items: (saleData.items || [])
    .filter(item => item.product_id) // Solo items con producto válido
    .map(item => ({
      product_id: parseInt(item.product_id, 10),
      product_code: item.product?.code || item.product_code || '',
      product_name: item.product?.name || item.product_name || '',
      product_description: item.product?.description || item.product_description || '',
      quantity: parseFloat(item.quantity) || 1,
      unit_price: parseFloat(item.unit_price) || 0,
      discount: parseFloat(item.discount) || 0,
      discount_percentage: parseFloat(item.discount) || 0,
      subtotal: parseFloat(item.subtotal) || 0,
      discount_amount: parseFloat(item.discount_amount) || 0,
      total: parseFloat(item.total) || 0,
    })),
})

const newItem = ref({
  product_id: '',
  quantity: '',
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
         newItem.value.quantity && 
         parseFloat(newItem.value.quantity) > 0 && 
         newItem.value.unit_price > 0
})

const subtotal = computed(() => {
  return getValidItems().reduce((sum, item) => {
    const itemSubtotal = parseFloat(item.quantity || 0) * parseFloat(item.unit_price || 0)
    return sum + itemSubtotal
  }, 0)
})

const discountAmount = computed(() => {
  return getValidItems().reduce((sum, item) => {
    const itemSubtotal = parseFloat(item.quantity || 0) * parseFloat(item.unit_price || 0)
    const discount = parseFloat(item.discount || item.discount_percentage || 0)
    const itemDiscount = itemSubtotal * (discount / 100)
    return sum + itemDiscount
  }, 0)
})

const total = computed(() => {
  return subtotal.value - discountAmount.value
})

const onClientChange = () => {
  // El cliente ha cambiado, no hay nada que resetear
}

const onProductSelect = () => {
  if (selectedProduct.value) {
    newItem.value.unit_price = selectedProduct.value.sale_price
    // Validar cantidad si excede el stock (solo si ya tiene un valor)
    if (newItem.value.quantity && newItem.value.quantity !== '') {
      const maxQuantity = selectedProduct.value.stock_quantity || 0
      if (parseFloat(newItem.value.quantity) > maxQuantity) {
        newItem.value.quantity = maxQuantity
      }
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

  const itemSubtotal = newItem.value.quantity * newItem.value.unit_price
  const itemDiscount = itemSubtotal * (newItem.value.discount_percentage / 100)
  const itemTotal = itemSubtotal - itemDiscount

  const item = {
    product_id: newItem.value.product_id,
    product_code: product.code,
    product_name: product.name,
    product_description: product.description || '',
    quantity: newItem.value.quantity,
    unit_price: newItem.value.unit_price,
    discount: newItem.value.discount_percentage,
    discount_percentage: newItem.value.discount_percentage,
    subtotal: itemSubtotal,
    discount_amount: itemDiscount,
    total: itemTotal,
  }

  form.items.push(item)

  // Reset form
  newItem.value = {
    product_id: '',
    quantity: '',
    unit_price: 0,
    discount_percentage: 0,
    notes: '',
  }
}

const getValidItems = () => {
  return form.items.filter(item => 
    item.product_id && 
    item.product_id !== '' && 
    item.product_id != null &&
    parseFloat(item.quantity) > 0 &&
    parseFloat(item.unit_price) >= 0
  )
}

const removeItem = (index) => {
  form.items.splice(index, 1)
}

const removeItemByIndex = (index) => {
  const validItems = getValidItems()
  if (validItems[index]) {
    const itemToRemove = validItems[index]
    const actualIndex = form.items.findIndex(item => 
      item.product_id === itemToRemove.product_id &&
      item.quantity === itemToRemove.quantity
    )
    if (actualIndex !== -1) {
      form.items.splice(actualIndex, 1)
    }
  }
}

const getStatusText = (status) => {
  const statusMap = {
    'draft': 'Borrador',
    'completed': 'Completada',
    'cancelled': 'Cancelada',
  }
  return statusMap[status] || status || 'Desconocido'
}

const getStatusColorClass = (status) => {
  const classes = {
    'draft': 'bg-gray-100 text-gray-800',
    'completed': 'bg-green-100 text-green-800',
    'cancelled': 'bg-red-100 text-red-800',
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getStatusDescription = (status) => {
  const descriptions = {
    'draft': 'Venta en borrador - Puede ser editada',
    'completed': 'Venta completada',
    'cancelled': 'Venta cancelada',
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
  // Validaciones frontend
  if (!form.client_id) {
    alert('Debe seleccionar un cliente')
    return
  }

  // Usar solo items válidos
  const validItems = getValidItems()

  if (validItems.length === 0) {
    alert('Debe agregar al menos un producto válido')
    return
  }

  // Preparar datos para enviar
  const dataToSend = {
    client_id: parseInt(form.client_id, 10),
    salesperson_id: null, // Se puede agregar después si es necesario
    payment_method: form.payment_method || 'cash',
    payment_status: form.payment_status || 'pending',
    invoice_number: form.invoice_number && form.invoice_number.trim() !== '' ? form.invoice_number.trim() : null,
    notes: form.notes && form.notes.trim() !== '' ? form.notes.trim() : null,
    delivery_date: form.delivery_date || null,
    items: validItems.map(item => {
      const quantity = parseFloat(item.quantity)
      const unitPrice = parseFloat(item.unit_price)
      const discount = parseFloat(item.discount || item.discount_percentage || 0)
      
      // Asegurar valores mínimos válidos
      return {
        product_id: parseInt(item.product_id, 10),
        quantity: quantity > 0 ? quantity : 1,
        unit_price: unitPrice >= 0 ? unitPrice : 0,
        discount: discount >= 0 && discount <= 100 ? discount : 0,
      }
    }),
  }

  // Validación final de items
  const hasInvalidItems = dataToSend.items.some(item => 
    !item.product_id || 
    isNaN(item.product_id) ||
    item.quantity <= 0 || 
    isNaN(item.quantity) ||
    item.unit_price < 0 ||
    isNaN(item.unit_price) ||
    item.discount < 0 ||
    item.discount > 100
  )

  if (hasInvalidItems) {
    alert('Hay productos con datos inválidos. Por favor verifique las cantidades y precios.')
    console.error('Items inválidos:', dataToSend.items)
    return
  }

  console.log('Datos a enviar:', dataToSend)

  form.transform(() => dataToSend).put(`/ventas/${saleData.id}`, {
    preserveScroll: true,
    onError: (errors) => {
      console.error('Errores de validación:', errors)
      // Mostrar errores al usuario
      const errorMessages = Object.values(errors).flat()
      if (errorMessages.length > 0) {
        alert('Error al actualizar la venta:\n' + errorMessages.join('\n'))
      }
    },
    onSuccess: () => {
      // Redirección manejada por el servidor
    }
  })
}
</script>
