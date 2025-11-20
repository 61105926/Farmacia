<template>
  <AdminLayout>
    <!-- Page Header -->
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Nuevo Producto</h1>
          <p class="text-sm text-gray-600 mt-1">Agregar un nuevo producto al inventario</p>
        </div>
        <Link
          href="/productos"
          class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors"
        >
          Volver
        </Link>
      </div>
    </template>

    <!-- Error Message -->
    <div v-if="error" class="mb-6 bg-red-50 border border-red-200 rounded-md p-4">
      <div class="flex">
        <AlertCircle class="h-5 w-5 text-red-400" />
        <div class="ml-3">
          <h3 class="text-sm font-medium text-red-800">Error</h3>
          <div class="mt-2 text-sm text-red-700">{{ error }}</div>
        </div>
      </div>
    </div>

    <!-- Form -->
    <div class="max-w-6xl mx-auto">
      <form @submit.prevent="submitForm" class="space-y-6">
        <!-- Información Básica -->
        <Card>
          <CardHeader>
            <CardTitle>Información Básica</CardTitle>
            <CardDescription>Datos principales del producto</CardDescription>
          </CardHeader>
          <CardContent class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Nombre del Producto *
                </label>
                <input
                  v-model="form.name"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
                  :class="{ 'border-red-500': errors.name }"
                />
                <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Código *
                </label>
                <input
                  v-model="form.code"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
                  :class="{ 'border-red-500': errors.code }"
                />
                <p v-if="errors.code" class="mt-1 text-sm text-red-600">{{ errors.code }}</p>
              </div>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Descripción
              </label>
              <textarea
                v-model="form.description"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
                :class="{ 'border-red-500': errors.description }"
              ></textarea>
              <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description }}</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Categoría
                </label>
                <select
                  v-model="form.category_id"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
                  :class="{ 'border-red-500': errors.category_id }"
                >
                  <option value="">Seleccionar categoría</option>
                  <option v-if="!categories || categories.length === 0" value="" disabled>
                    No hay categorías disponibles
                  </option>
                  <option v-for="category in categories" :key="category.id" :value="category.id">
                    {{ category.name }}
                  </option>
                </select>
                <p v-if="errors.category_id" class="mt-1 text-sm text-red-600">{{ errors.category_id }}</p>
                <p v-if="(!categories || categories.length === 0) && !error" class="mt-1 text-sm text-amber-600">
                  ⚠️ No hay categorías disponibles. Ejecuta el seeder: 
                  <code class="bg-gray-100 px-2 py-1 rounded text-xs">php artisan db:seed --class=CategorySeeder</code>
                </p>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Unidad *
                </label>
                <select
                  v-model="form.unit_type"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
                  :class="{ 'border-red-500': errors.unit }"
                >
                  <option value="">Seleccionar unidad</option>
                  <option value="unidad">Unidad</option>
                  <option value="caja">Caja</option>
                  <option value="blister">Blister</option>
                  <option value="frasco">Frasco</option>
                  <option value="ampolla">Ampolla</option>
                  <option value="vial">Vial</option>
                  <option value="sobre">Sobre</option>
                  <option value="tubo">Tubo</option>
                </select>
                <p v-if="errors.unit" class="mt-1 text-sm text-red-600">{{ errors.unit }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Precios -->
        <Card>
          <CardHeader>
            <CardTitle>Precios</CardTitle>
          </CardHeader>
          <CardContent class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Precio de Compra *
                </label>
                <div class="relative">
                  <span class="absolute left-3 top-2 text-gray-500">Bs</span>
                  <input
                    v-model="form.cost_price"
                    type="number"
                    step="0.01"
                    min="0"
                    required
                    class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
                    :class="{ 'border-red-500': errors.cost_price }"
                  />
                </div>
                <p v-if="errors.cost_price" class="mt-1 text-sm text-red-600">{{ errors.cost_price }}</p>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Precio de Venta *
                </label>
                <div class="relative">
                  <span class="absolute left-3 top-2 text-gray-500">Bs</span>
                  <input
                    v-model="form.sale_price"
                    type="number"
                    step="0.01"
                    min="0"
                    required
                    class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
                    :class="{ 'border-red-500': errors.sale_price }"
                  />
                </div>
                <p v-if="errors.sale_price" class="mt-1 text-sm text-red-600">{{ errors.sale_price }}</p>
              </div>
            </div>
            
            <div v-if="form.cost_price && form.sale_price" class="bg-blue-50 p-4 rounded-md">
              <div class="text-sm">
                <div class="flex justify-between">
                  <span>Margen de ganancia:</span>
                  <span class="font-semibold">{{ calculateMargin() }}%</span>
                </div>
                <div class="flex justify-between">
                  <span>Ganancia por unidad:</span>
                  <span class="font-semibold">{{ formatCurrency(form.sale_price - form.cost_price) }}</span>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Inventario -->
        <Card>
          <CardHeader>
            <CardTitle>Inventario</CardTitle>
          </CardHeader>
          <CardContent class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Stock Actual *
                </label>
                <input
                  v-model="form.stock_quantity"
                  type="number"
                  min="0"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
                  :class="{ 'border-red-500': errors.stock_quantity }"
                />
                <p v-if="errors.stock_quantity" class="mt-1 text-sm text-red-600">{{ errors.stock_quantity }}</p>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Stock Mínimo *
                </label>
                <input
                  v-model="form.min_stock"
                  type="number"
                  min="0"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
                  :class="{ 'border-red-500': errors.min_stock }"
                />
                <p v-if="errors.min_stock" class="mt-1 text-sm text-red-600">{{ errors.min_stock }}</p>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Stock Máximo
                </label>
                <input
                  v-model="form.max_stock"
                  type="number"
                  min="0"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
                  :class="{ 'border-red-500': errors.max_stock }"
                />
                <p v-if="errors.max_stock" class="mt-1 text-sm text-red-600">{{ errors.max_stock }}</p>
              </div>
            </div>
            
            <div v-if="form.stock_quantity && form.min_stock" class="bg-yellow-50 p-4 rounded-md">
              <div class="text-sm">
                <div class="flex justify-between">
                  <span>Estado del stock:</span>
                  <span class="font-semibold" :class="getStockStatusColor()">
                    {{ getStockStatusText() }}
                  </span>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Configuración -->
        <Card>
          <CardHeader>
            <CardTitle>Configuración</CardTitle>
          </CardHeader>
          <CardContent class="space-y-4">
            <div class="flex items-center">
              <input
                v-model="form.is_active"
                type="checkbox"
                class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
              />
              <label class="ml-2 block text-sm text-gray-900">
                Producto activo
              </label>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Fecha de Vencimiento
                </label>
                <input
                  v-model="form.expiry_date"
                  type="date"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
                  :class="{ 'border-red-500': errors.expiry_date }"
                />
                <p v-if="errors.expiry_date" class="mt-1 text-sm text-red-600">{{ errors.expiry_date }}</p>
              </div>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Notas
              </label>
              <textarea
                v-model="form.notes"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
                :class="{ 'border-red-500': errors.notes }"
                placeholder="Notas adicionales sobre el producto..."
              ></textarea>
              <p v-if="errors.notes" class="mt-1 text-sm text-red-600">{{ errors.notes }}</p>
            </div>
          </CardContent>
        </Card>

        <!-- Actions -->
        <div class="flex justify-end space-x-4">
          <Link
            href="/productos"
            class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors"
          >
            Cancelar
          </Link>
          <button
            type="submit"
            :disabled="isSubmitting"
            class="px-6 py-2 bg-primary-700 text-white rounded-md hover:bg-primary-800 disabled:opacity-50 transition-colors"
          >
            {{ isSubmitting ? 'Guardando...' : 'Guardar Producto' }}
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Card from '@/Components/ui/Card.vue'
import CardHeader from '@/Components/ui/CardHeader.vue'
import CardTitle from '@/Components/ui/CardTitle.vue'
import CardContent from '@/Components/ui/CardContent.vue'
import { AlertCircle } from 'lucide-vue-next'
import { showAlert } from '@/composables/useAlert'

const props = defineProps({
  categories: Array,
  errors: Object,
  error: String
})

const isSubmitting = ref(false)

const form = reactive({
  name: '',
  code: '',
  description: '',
  category_id: '',
  brand: '',
  cost_price: '',
  sale_price: '',
  stock_quantity: '',
  min_stock: '',
  max_stock: '',
  unit_type: 'unidad',
  is_active: true,
  notes: '',
  expiry_date: '',
})

// Debug: Log categories
console.log('Categories recibidas:', props.categories)
console.log('Número de categorías:', props.categories?.length || 0)

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('es-BO', {
    style: 'currency',
    currency: 'BOB',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(amount || 0)
}

const calculateMargin = () => {
  if (!form.cost_price || !form.sale_price) return 0
  const margin = ((form.sale_price - form.cost_price) / form.cost_price) * 100
  return margin.toFixed(2)
}

const getStockStatusText = () => {
  if (!form.stock_quantity || !form.min_stock) return ''
  
  if (form.stock_quantity <= 0) return 'Sin Stock'
  if (form.stock_quantity <= form.min_stock) return 'Stock Bajo'
  return 'En Stock'
}

const getStockStatusColor = () => {
  if (!form.stock_quantity || !form.min_stock) return 'text-gray-600'
  
  if (form.stock_quantity <= 0) return 'text-red-600'
  if (form.stock_quantity <= form.min_stock) return 'text-yellow-600'
  return 'text-green-600'
}

const submitForm = () => {
  isSubmitting.value = true
  
  router.post('/productos', form, {
    onSuccess: () => {
      isSubmitting.value = false
      // Flash message will be handled by watcher
      router.visit('/productos')
    },
    onError: (errors) => {
      isSubmitting.value = false
      // Flash message will be handled by watcher
    },
    onFinish: () => {
      isSubmitting.value = false
    }
  })
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

    // Filtrar errores vacíos, arrays vacíos, objetos vacíos, y strings vacíos
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