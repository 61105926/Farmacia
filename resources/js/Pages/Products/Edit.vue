<template>
  <AdminLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Editar Producto</h1>
          <p class="text-sm text-gray-600 mt-1">{{ product.description || product.name }}</p>
        </div>
        <Link
          href="/productos"
          class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors"
        >
          Volver
        </Link>
      </div>
    </template>

    <div class="max-w-6xl mx-auto">
      <form @submit.prevent="submit" class="space-y-6">

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
                  :class="{ 'border-red-500': form.errors.name }"
                />
                <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Código *</label>
                <input
                  v-model="form.code"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
                  :class="{ 'border-red-500': form.errors.code }"
                />
                <p v-if="form.errors.code" class="mt-1 text-sm text-red-600">{{ form.errors.code }}</p>
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

            <div>
              <label class="flex items-center gap-2">
                <input
                  v-model="form.is_active"
                  type="checkbox"
                  class="h-4 w-4 text-primary-700 focus:ring-primary-500 border-gray-300 rounded"
                />
                <span class="text-sm font-medium text-gray-700">Producto activo</span>
              </label>
            </div>
          </CardContent>
        </Card>


        <!-- Actions -->
        <div class="flex justify-end space-x-4">
          <Link
            :href="`/productos/${product.id}`"
            class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors"
          >
            Cancelar
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-6 py-2 bg-primary-700 text-white rounded-md hover:bg-primary-800 disabled:opacity-50 transition-colors"
          >
            {{ form.processing ? 'Guardando...' : 'Actualizar Producto' }}
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import { watch } from 'vue'
import { useForm, Link, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Card from '@/Components/ui/Card.vue'
import CardHeader from '@/Components/ui/CardHeader.vue'
import CardTitle from '@/Components/ui/CardTitle.vue'
import CardContent from '@/Components/ui/CardContent.vue'

const props = defineProps({
  product: Object,
  categories: Array,
})

const form = useForm({
  name:         props.product.name,
  code:         props.product.code,
  description:  props.product.description || '',
  action:       props.product.action || '',
  supplier:     props.product.supplier || '',
  dosage:       props.product.dosage || '',
  presentation: props.product.presentation || '',
  is_active:    props.product.is_active ?? true,
})

const submit = () => {
  form.put(`/productos/${props.product.id}`)
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
