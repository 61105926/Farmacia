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

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                <input
                  v-model="form.presentation"
                  type="text"
                  list="formas-list"
                  placeholder="Seleccionar o escribir forma"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
                />
                <datalist id="formas-list">
                  <option value="Tableta" />
                  <option value="Cápsula" />
                  <option value="Jarabe" />
                  <option value="Suspensión" />
                  <option value="Inyectable" />
                  <option value="Crema" />
                  <option value="Pomada" />
                  <option value="Gel" />
                  <option value="Gotas" />
                  <option value="Supositorio" />
                  <option value="Parche" />
                  <option value="Polvo" />
                  <option value="Solución" />
                  <option value="Spray" />
                  <option value="Óvulo" />
                </datalist>
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
  dosage: '',
  presentation: '',
})


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
