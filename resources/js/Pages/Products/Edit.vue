<template>
  <AdminLayout>
    <div class="p-6">
      <!-- Header -->
      <div class="mb-6">
        <Link href="/productos" class="text-sm text-primary-700 hover:text-primary-800 mb-2 inline-block">
          ← Volver a productos
        </Link>
        <h1 class="text-2xl font-bold text-gray-900">Editar Producto</h1>
        <p class="text-sm text-gray-600 mt-1">{{ product.name }}</p>
      </div>

      <form @submit.prevent="submit">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Main Info -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Información Básica -->
            <Card>
              <CardHeader>
                <CardTitle>Información Básica</CardTitle>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Código del Producto <span class="text-red-500">*</span>
                    </label>
                    <input
                      v-model="form.code"
                      type="text"
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                      :class="{ 'border-red-500': form.errors.code }"
                    />
                    <span v-if="form.errors.code" class="text-sm text-red-600">
                      {{ form.errors.code }}
                    </span>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Código de Barras
                    </label>
                    <input
                      v-model="form.barcode"
                      type="text"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                    />
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nombre del Producto <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="form.name"
                    type="text"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                    :class="{ 'border-red-500': form.errors.name }"
                  />
                  <span v-if="form.errors.name" class="text-sm text-red-600">
                    {{ form.errors.name }}
                  </span>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Descripción
                  </label>
                  <textarea
                    v-model="form.description"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                  ></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Categoría
                    </label>
                    <select
                      v-model="form.category_id"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                    >
                      <option :value="null">Sin categoría</option>
                      <option v-for="category in categories" :key="category.id" :value="category.id">
                        {{ category.name }}
                      </option>
                    </select>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Marca/Laboratorio
                    </label>
                    <input
                      v-model="form.brand"
                      type="text"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                    />
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Información Farmacéutica -->
            <Card>
              <CardHeader>
                <CardTitle>Información Farmacéutica</CardTitle>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Principio Activo
                    </label>
                    <input
                      v-model="form.active_ingredient"
                      type="text"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Dosificación
                    </label>
                    <input
                      v-model="form.dosage"
                      type="text"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                    />
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Presentación
                  </label>
                  <input
                    v-model="form.presentation"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                  />
                </div>

                <div class="flex items-center gap-4">
                  <label class="flex items-center">
                    <input
                      v-model="form.requires_prescription"
                      type="checkbox"
                      class="h-4 w-4 text-primary-700 focus:ring-primary-500 border-gray-300 rounded"
                    />
                    <span class="ml-2 text-sm text-gray-700">Requiere prescripción médica</span>
                  </label>

                  <label class="flex items-center">
                    <input
                      v-model="form.is_controlled"
                      type="checkbox"
                      class="h-4 w-4 text-primary-700 focus:ring-primary-500 border-gray-300 rounded"
                    />
                    <span class="ml-2 text-sm text-gray-700">Medicamento controlado</span>
                  </label>
                </div>
              </CardContent>
            </Card>

            <!-- Precios -->
            <Card>
              <CardHeader>
                <CardTitle>Precios</CardTitle>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="grid grid-cols-3 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Precio de Costo <span class="text-red-500">*</span>
                    </label>
                    <input
                      v-model.number="form.cost_price"
                      type="number"
                      step="0.01"
                      min="0"
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Precio Base <span class="text-red-500">*</span>
                    </label>
                    <input
                      v-model.number="form.base_price"
                      type="number"
                      step="0.01"
                      min="0"
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Precio de Venta <span class="text-red-500">*</span>
                    </label>
                    <input
                      v-model.number="form.sale_price"
                      type="number"
                      step="0.01"
                      min="0"
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                    />
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Impuesto (%)
                  </label>
                  <input
                    v-model.number="form.tax_rate"
                    type="number"
                    step="0.01"
                    min="0"
                    max="100"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                  />
                </div>
              </CardContent>
            </Card>
          </div>

          <!-- Sidebar -->
          <div class="space-y-6">
            <!-- Inventario -->
            <Card>
              <CardHeader>
                <CardTitle>Inventario</CardTitle>
              </CardHeader>
              <CardContent class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Stock Actual
                  </label>
                  <input
                    v-model.number="form.stock_quantity"
                    type="number"
                    min="0"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Stock Mínimo
                  </label>
                  <input
                    v-model.number="form.min_stock"
                    type="number"
                    min="0"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                  />
                </div>
              </CardContent>
            </Card>

            <!-- Fecha de Vencimiento -->
            <Card>
              <CardHeader>
                <CardTitle>Fecha de Vencimiento</CardTitle>
              </CardHeader>
              <CardContent>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Fecha de Vencimiento
                  </label>
                  <input
                    v-model="form.expiry_date"
                    type="date"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                    :class="{ 'border-red-500': form.errors.expiry_date }"
                  />
                  <span v-if="form.errors.expiry_date" class="text-sm text-red-600">
                    {{ form.errors.expiry_date }}
                  </span>
                </div>
              </CardContent>
            </Card>

            <!-- Estado -->
            <Card>
              <CardHeader>
                <CardTitle>Estado</CardTitle>
              </CardHeader>
              <CardContent>
                <label class="flex items-center">
                  <input
                    v-model="form.is_active"
                    type="checkbox"
                    class="h-4 w-4 text-primary-700 focus:ring-primary-500 border-gray-300 rounded"
                  />
                  <span class="ml-2 text-sm text-gray-700">Producto activo</span>
                </label>
              </CardContent>
            </Card>

            <!-- Actions -->
            <div class="flex flex-col space-y-2">
              <button
                type="submit"
                :disabled="form.processing"
                class="w-full px-4 py-2 bg-primary-700 text-white rounded-md hover:bg-primary-800 disabled:opacity-50 transition-colors"
              >
                <span v-if="form.processing">Guardando...</span>
                <span v-else>Actualizar Producto</span>
              </button>
              <Link
                :href="`/productos/${product.id}`"
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
import { watch } from 'vue'
import { useForm, Link, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardHeader, CardTitle, CardContent } from '@/Components/ui'

const props = defineProps({
  product: Object,
  categories: Array,
})

const form = useForm({
  code: props.product.code,
  name: props.product.name,
  description: props.product.description,
  category_id: props.product.category_id,
  brand: props.product.brand,
  active_ingredient: props.product.active_ingredient,
  dosage: props.product.dosage,
  presentation: props.product.presentation,
  barcode: props.product.barcode,
  base_price: props.product.base_price,
  cost_price: props.product.cost_price,
  sale_price: props.product.sale_price,
  tax_rate: props.product.tax_rate,
  stock_quantity: props.product.stock_quantity,
  min_stock: props.product.min_stock,
  requires_prescription: props.product.requires_prescription,
  is_controlled: props.product.is_controlled,
  is_active: props.product.is_active,
  expiry_date: props.product.expiry_date ? new Date(props.product.expiry_date).toISOString().split('T')[0] : '',
})

const submit = () => {
  form.put(`/productos/${props.product.id}`, {
    onSuccess: () => {
      // Flash message will be handled by watcher
    },
    onError: () => {
      // Flash message will be handled by watcher
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
