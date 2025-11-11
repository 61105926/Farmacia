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
                    NIT <span class="text-red-500">*</span>
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
                      :class="{ 'border-red-500': form.errors.client_type }"
                    >
                      <option value="">Seleccionar...</option>
                      <option value="pharmacy">Farmacia</option>
                      <option value="chain">Cadena de Farmacias</option>
                      <option value="hospital">Hospital</option>
                      <option value="clinic">Clínica</option>
                      <option value="other">Otro</option>
                    </select>
                    <span v-if="form.errors.client_type" class="text-sm text-red-600">
                      {{ form.errors.client_type }}
                    </span>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Categoría <span class="text-red-500">*</span>
                    </label>
                    <select
                      v-model="form.category"
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                      :class="{ 'border-red-500': form.errors.category }"
                    >
                      <option value="A">A - Alto volumen</option>
                      <option value="B">B - Medio volumen</option>
                      <option value="C">C - Bajo volumen</option>
                    </select>
                    <span v-if="form.errors.category" class="text-sm text-red-600">
                      {{ form.errors.category }}
                    </span>
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
                      :class="{ 'border-red-500': form.errors.phone }"
                    />
                    <span v-if="form.errors.phone" class="text-sm text-red-600">
                      {{ form.errors.phone }}
                    </span>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Email
                    </label>
                    <input
                      v-model="form.email"
                      type="email"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                      :class="{ 'border-red-500': form.errors.email }"
                    />
                    <span v-if="form.errors.email" class="text-sm text-red-600">
                      {{ form.errors.email }}
                    </span>
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
                    <select
                      v-model="form.state"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                    >
                      <option value="">Seleccionar...</option>
                      <option value="Chuquisaca">Chuquisaca</option>
                      <option value="La Paz">La Paz</option>
                      <option value="Cochabamba">Cochabamba</option>
                      <option value="Oruro">Oruro</option>
                      <option value="Potosí">Potosí</option>
                      <option value="Tarija">Tarija</option>
                      <option value="Santa Cruz">Santa Cruz</option>
                      <option value="Beni">Beni</option>
                      <option value="Pando">Pando</option>
                    </select>
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
                      :class="{ 'border-red-500': form.errors.status }"
                    >
                      <option value="active">Activo</option>
                      <option value="inactive">Inactivo</option>
                      <option value="blocked">Bloqueado</option>
                    </select>
                    <span v-if="form.errors.status" class="text-sm text-red-600">
                      {{ form.errors.status }}
                    </span>
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
                    <option value="">Predeterminada</option>
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
                    <option value="">Seleccionar...</option>
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
                    <option value="">Sin asignar</option>
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
                    <option value="">Sin asignar</option>
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
import { watch } from 'vue'
import { useForm, Link, usePage } from '@inertiajs/vue3'
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
  business_name: props.client.business_name || '',
  trade_name: props.client.trade_name || '',
  tax_id: props.client.tax_id || '',
  client_type: props.client.client_type || '',
  category: props.client.category || '',
  address: props.client.address || '',
  city: props.client.city || '',
  state: props.client.state || '',
  phone: props.client.phone || '',
  email: props.client.email || '',
  website: props.client.website || '',
  price_list_id: props.client.price_list_id || '',
  default_discount: props.client.default_discount || 0,
  payment_term_id: props.client.payment_term_id || '',
  credit_limit: props.client.credit_limit || 0,
  credit_days: props.client.credit_days || 0,
  salesperson_id: props.client.salesperson_id || '',
  collector_id: props.client.collector_id || '',
  zone: props.client.zone || '',
  visit_day: props.client.visit_day || null,
  visit_frequency: props.client.visit_frequency || null,
  notes: props.client.notes || '',
  status: props.client.status || 'active',
})

const submit = () => {
  // Transformar datos antes de enviar
  const data = { ...form.data() }
  
  // Convertir strings vacíos a null para campos opcionales
  // Asegurar que website sea null o un string válido
  if (!data.website || data.website === '' || data.website === null || data.website === undefined) {
    data.website = null
  } else {
    // Asegurar que sea un string
    data.website = String(data.website).trim() || null
  }
  
  // Convertir price_list_id: string vacío a null, o convertir a número si tiene valor
  if (data.price_list_id === '' || data.price_list_id === null || data.price_list_id === undefined) {
    data.price_list_id = null
  } else if (data.price_list_id) {
    const numValue = parseInt(data.price_list_id, 10)
    data.price_list_id = isNaN(numValue) ? null : numValue
  }
  
  // Convertir payment_term_id
  if (data.payment_term_id === '' || data.payment_term_id === null || data.payment_term_id === undefined) {
    data.payment_term_id = null
  } else if (data.payment_term_id) {
    const numValue = parseInt(data.payment_term_id, 10)
    data.payment_term_id = isNaN(numValue) ? null : numValue
  }
  
  // Convertir salesperson_id
  if (data.salesperson_id === '' || data.salesperson_id === null || data.salesperson_id === undefined) {
    data.salesperson_id = null
  } else if (data.salesperson_id) {
    const numValue = parseInt(data.salesperson_id, 10)
    data.salesperson_id = isNaN(numValue) ? null : numValue
  }
  
  // Convertir collector_id
  if (data.collector_id === '' || data.collector_id === null || data.collector_id === undefined) {
    data.collector_id = null
  } else if (data.collector_id) {
    const numValue = parseInt(data.collector_id, 10)
    data.collector_id = isNaN(numValue) ? null : numValue
  }
  
  // Asegurar que website siempre esté presente en los datos
  if (!('website' in data)) {
    data.website = null
  }
  
  form.transform(() => data).put(`/clientes/${props.client.id}`, {
    preserveScroll: true,
    onError: (errors) => {
      console.error('Errores de validación:', errors)
      console.error('Datos enviados:', data)
    },
    onSuccess: () => {
      // Redirección manejada por el servidor
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
