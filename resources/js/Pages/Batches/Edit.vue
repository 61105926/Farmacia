<template>
  <AdminLayout>
    <div class="p-6 max-w-2xl mx-auto">
      <div class="mb-6">
        <Link :href="`/lotes/producto/${product.id}`" class="text-sm text-primary-700 hover:text-primary-800 mb-2 inline-block">
          ← Volver a lotes de {{ product.name }}
        </Link>
        <h1 class="text-2xl font-bold text-gray-900 mt-1">Editar Lote</h1>
        <p class="text-sm text-gray-500">{{ product.code }} — {{ product.name }}</p>
      </div>

      <Card>
        <CardContent class="p-6">
          <form @submit.prevent="submit" class="space-y-5">
            <div class="grid grid-cols-2 gap-4">
              <!-- N° Lote -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  N° Lote <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.batch_number"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                  :class="{ 'border-red-500': form.errors.batch_number }"
                />
                <p v-if="form.errors.batch_number" class="mt-1 text-xs text-red-600">{{ form.errors.batch_number }}</p>
              </div>

              <!-- Proveedor -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Proveedor</label>
                <input
                  v-model="form.supplier"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                />
              </div>

              <!-- Fecha Ingreso -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Fecha Ingreso <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.entry_date"
                  type="date"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                  :class="{ 'border-red-500': form.errors.entry_date }"
                />
                <p v-if="form.errors.entry_date" class="mt-1 text-xs text-red-600">{{ form.errors.entry_date }}</p>
              </div>

              <!-- Fecha Vencimiento -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Vencimiento</label>
                <input
                  v-model="form.expiry_date"
                  type="date"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                />
              </div>

              <!-- Cantidad Inicial -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Cantidad Inicial <span class="text-red-500">*</span>
                </label>
                <input
                  v-model.number="form.initial_quantity"
                  type="number"
                  min="0"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                  :class="{ 'border-red-500': form.errors.initial_quantity }"
                />
                <p v-if="form.errors.initial_quantity" class="mt-1 text-xs text-red-600">{{ form.errors.initial_quantity }}</p>
              </div>

              <!-- Cantidad Restante -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Cantidad Restante <span class="text-red-500">*</span>
                </label>
                <input
                  v-model.number="form.remaining_quantity"
                  type="number"
                  min="0"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                  :class="{ 'border-red-500': form.errors.remaining_quantity }"
                />
                <p v-if="form.errors.remaining_quantity" class="mt-1 text-xs text-red-600">{{ form.errors.remaining_quantity }}</p>
              </div>

              <!-- Costo -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Precio Compra</label>
                <input
                  v-model.number="form.cost_price"
                  type="number"
                  min="0"
                  step="0.01"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                />
              </div>

              <!-- Precio Venta -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Precio Venta</label>
                <input
                  v-model.number="form.sale_price"
                  type="number"
                  min="0"
                  step="0.01"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                />
              </div>

              <!-- Estado -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Estado <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.status"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                >
                  <option value="active">Activo</option>
                  <option value="depleted">Agotado</option>
                  <option value="expired">Vencido</option>
                </select>
              </div>
            </div>

            <!-- Notas -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Notas</label>
              <textarea
                v-model="form.notes"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                placeholder="Observaciones del lote..."
              ></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-2">
              <Link
                :href="`/lotes/producto/${product.id}`"
                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50"
              >
                Cancelar
              </Link>
              <button
                type="submit"
                :disabled="form.processing"
                class="px-4 py-2 bg-primary-700 text-white rounded-md hover:bg-primary-800 disabled:opacity-50"
              >
                {{ form.processing ? 'Guardando...' : 'Guardar cambios' }}
              </button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardContent } from '@/Components/ui'

const props = defineProps({
  batch:   { type: Object, required: true },
  product: { type: Object, required: true },
})

const toDateInput = (val) => {
  if (!val) return ''
  return String(val).substring(0, 10)
}

const form = useForm({
  batch_number:      props.batch.batch_number,
  supplier:          props.batch.supplier || '',
  entry_date:        toDateInput(props.batch.entry_date),
  expiry_date:       toDateInput(props.batch.expiry_date),
  initial_quantity:  props.batch.initial_quantity,
  remaining_quantity:props.batch.remaining_quantity,
  cost_price:        props.batch.cost_price || '',
  sale_price:        props.batch.sale_price || '',
  notes:             props.batch.notes || '',
  status:            props.batch.status,
})

const submit = () => {
  form.put(`/lotes/${props.batch.id}`)
}
</script>
