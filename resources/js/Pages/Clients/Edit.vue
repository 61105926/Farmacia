<template>
  <AdminLayout>
    <div class="p-6">
      <!-- Header -->
      <div class="mb-6">
        <Link href="/clientes" class="text-sm text-primary-700 hover:text-primary-800 mb-2 inline-block">
          ← Volver a clientes
        </Link>
        <h1 class="text-2xl font-bold text-gray-900">Editar Cliente</h1>
        <p class="text-sm text-gray-600 mt-1">{{ client.business_name }}</p>
      </div>

      <form @submit.prevent="submit">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Main Info -->
          <div class="lg:col-span-2">
            <Card>
              <CardHeader>
                <CardTitle>Información General</CardTitle>
              </CardHeader>
              <CardContent class="space-y-4">
                <!-- Business Name -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Razón Social <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="form.business_name"
                    type="text"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    :class="{ 'border-red-500': form.errors.business_name }"
                  />
                  <span v-if="form.errors.business_name" class="text-sm text-red-600">
                    {{ form.errors.business_name }}
                  </span>
                </div>

                <!-- Trade Name -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nombre Comercial
                  </label>
                  <input
                    v-model="form.trade_name"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                  />
                </div>

                <!-- Tax ID -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    NIT/RUT <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="form.tax_id"
                    type="text"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                    :class="{ 'border-red-500': form.errors.tax_id }"
                  />
                  <span v-if="form.errors.tax_id" class="text-sm text-red-600">
                    {{ form.errors.tax_id }}
                  </span>
                </div>

                <!-- Type and Category -->
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Tipo de Cliente <span class="text-red-500">*</span>
                    </label>
                    <select
                      v-model="form.client_type"
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                    >
                      <option value="">Seleccionar...</option>
                      <option value="pharmacy">Farmacia</option>
                      <option value="chain">Cadena de Farmacias</option>
                      <option value="hospital">Hospital</option>
                      <option value="clinic">Clínica</option>
                      <option value="other">Otro</option>
                    </select>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Categoría <span class="text-red-500">*</span>
                    </label>
                    <select
                      v-model="form.category"
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                    >
                      <option value="A">A - Alto volumen</option>
                      <option value="B">B - Medio volumen</option>
                      <option value="C">C - Bajo volumen</option>
                    </select>
                  </div>
                </div>

                <!-- Contact Info -->
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Teléfono
                    </label>
                    <input
                      v-model="form.phone"
                      type="tel"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Email
                    </label>
                    <input
                      v-model="form.email"
                      type="email"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                    />
                  </div>
                </div>

                <!-- Address -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Dirección
                  </label>
                  <textarea
                    v-model="form.address"
                    rows="2"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                  ></textarea>
                </div>

                <!-- City and State -->
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Ciudad
                    </label>
                    <input
                      v-model="form.city"
                      type="text"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Departamento
                    </label>
                    <input
                      v-model="form.state"
                      type="text"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                    />
                  </div>
                </div>

                <!-- Status -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Estado
                  </label>
                  <select
                    v-model="form.status"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                  >
                    <option value="active">Activo</option>
                    <option value="inactive">Inactivo</option>
                    <option value="blocked">Bloqueado</option>
                  </select>
                </div>
              </CardContent>
            </Card>
          </div>

          <!-- Commercial Info -->
          <div>
            <Card class="mb-6">
              <CardHeader>
                <CardTitle>Información Comercial</CardTitle>
              </CardHeader>
              <CardContent class="space-y-4">
                <!-- Price List -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Lista de Precios
                  </label>
                  <select
                    v-model="form.price_list_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                  >
                    <option :value="null">Predeterminada</option>
                    <option v-for="list in priceLists" :key="list.id" :value="list.id">
                      {{ list.name }}
                    </option>
                  </select>
                </div>

                <!-- Payment Term -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Condición de Pago
                  </label>
                  <select
                    v-model="form.payment_term_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                  >
                    <option :value="null">Seleccionar...</option>
                    <option v-for="term in paymentTerms" :key="term.id" :value="term.id">
                      {{ term.name }} ({{ term.days }} días)
                    </option>
                  </select>
                </div>

                <!-- Discount -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Descuento (%)
                  </label>
                  <input
                    v-model.number="form.default_discount"
                    type="number"
                    step="0.01"
                    min="0"
                    max="100"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                  />
                </div>

                <!-- Credit Limit -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Límite de Crédito
                  </label>
                  <input
                    v-model.number="form.credit_limit"
                    type="number"
                    step="0.01"
                    min="0"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                  />
                </div>

                <!-- Salesperson -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Vendedor/Preventista
                  </label>
                  <select
                    v-model="form.salesperson_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                  >
                    <option :value="null">Sin asignar</option>
                    <option v-for="salesperson in salespeople" :key="salesperson.id" :value="salesperson.id">
                      {{ salesperson.name }}
                    </option>
                  </select>
                </div>

                <!-- Collector -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Cobrador
                  </label>
                  <select
                    v-model="form.collector_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                  >
                    <option :value="null">Sin asignar</option>
                    <option v-for="collector in collectors" :key="collector.id" :value="collector.id">
                      {{ collector.name }}
                    </option>
                  </select>
                </div>
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
                <span v-else>Actualizar Cliente</span>
              </button>
              <Link
                :href="`/clientes/${client.id}`"
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
import { useForm, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardHeader, CardTitle, CardContent } from '@/Components/ui'


const props = defineProps({
  client: Object,
  priceLists: Array,
  paymentTerms: Array,
  salespeople: Array,
  collectors: Array,
})

const form = useForm({
  business_name: props.client.business_name,
  trade_name: props.client.trade_name,
  tax_id: props.client.tax_id,
  client_type: props.client.client_type,
  category: props.client.category,
  address: props.client.address,
  city: props.client.city,
  state: props.client.state,
  phone: props.client.phone,
  email: props.client.email,
  website: props.client.website,
  price_list_id: props.client.price_list_id,
  default_discount: props.client.default_discount,
  payment_term_id: props.client.payment_term_id,
  credit_limit: props.client.credit_limit,
  credit_days: props.client.credit_days,
  salesperson_id: props.client.salesperson_id,
  collector_id: props.client.collector_id,
  zone: props.client.zone,
  visit_day: props.client.visit_day,
  visit_frequency: props.client.visit_frequency,
  notes: props.client.notes,
  status: props.client.status,
})

const submit = () => {
  form.put(`/clientes/${props.client.id}`)
}
</script>
