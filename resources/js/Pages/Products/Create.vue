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
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del Producto *</label>
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
                <label class="block text-sm font-medium text-gray-700 mb-1">Código *</label>
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
              <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
              <textarea
                v-model="form.description"
                rows="2"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
              ></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Acción</label>
                <input
                  v-model="form.action"
                  type="text"
                  placeholder="Ej: Analgésico, Antibiótico..."
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Proveedor</label>
                <input
                  v-model="form.supplier"
                  type="text"
                  placeholder="Nombre del proveedor"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
                />
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre Genérico</label>
                <input
                  v-model="form.active_ingredient"
                  type="text"
                  placeholder="Principio activo"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Concentración</label>
                <input
                  v-model="form.dosage"
                  type="text"
                  placeholder="Ej: 500mg, 10mg/5ml"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Forma</label>
                <select
                  v-model="form.presentation"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
                >
                  <option value="">Seleccionar forma</option>
                  <option value="Tableta">Tableta</option>
                  <option value="Cápsula">Cápsula</option>
                  <option value="Jarabe">Jarabe</option>
                  <option value="Suspensión">Suspensión</option>
                  <option value="Inyectable">Inyectable</option>
                  <option value="Crema">Crema</option>
                  <option value="Pomada">Pomada</option>
                  <option value="Gel">Gel</option>
                  <option value="Gotas">Gotas</option>
                  <option value="Supositorio">Supositorio</option>
                  <option value="Parche">Parche</option>
                  <option value="Polvo">Polvo</option>
                  <option value="Solución">Solución</option>
                  <option value="Spray">Spray</option>
                  <option value="Óvulo">Óvulo</option>
                  <option value="Otro">Otro</option>
                </select>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Marca / Laboratorio</label>
                <input
                  v-model="form.brand"
                  type="text"
                  placeholder="Nombre del laboratorio"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
                />
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
                <label class="block text-sm font-medium text-gray-700 mb-1">Precio de Compra *</label>
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
                <label class="block text-sm font-medium text-gray-700 mb-1">Precio de Venta *</label>
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

const props = defineProps({
  errors: Object,
  error: String
})

const isSubmitting = ref(false)

const form = reactive({
  name: '',
  code: '',
  description: '',
  action: '',
  supplier: '',
  active_ingredient: '',
  dosage: '',
  presentation: '',
  brand: '',
  cost_price: '',
  sale_price: '',
})

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

const submitForm = () => {
  isSubmitting.value = true
  router.post('/productos', form, {
    onSuccess: () => {
      isSubmitting.value = false
      router.visit('/productos')
    },
    onError: () => {
      isSubmitting.value = false
    },
    onFinish: () => {
      isSubmitting.value = false
    }
  })
}

const page = usePage()
let lastFlashSuccess = null
let lastFlashError = null

watch(
  () => page.props.flash,
  (flash) => {
    if (flash?.success && typeof flash.success === 'string' && flash.success !== lastFlashSuccess && flash.success.trim() !== '') {
      lastFlashSuccess = flash.success
      window.$notify?.success('Éxito', flash.success)
    }

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
