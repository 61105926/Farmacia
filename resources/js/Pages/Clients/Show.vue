<template>
  <AdminLayout>
    <div class="p-6">
      <!-- Header -->
      <div class="mb-6 flex items-start justify-between">
        <div>
          <Link href="/clientes" class="text-sm text-primary-700 hover:text-primary-800 mb-2 inline-block">
            ← Volver a clientes
          </Link>
          <h1 class="text-2xl font-bold text-gray-900">{{ client.business_name }}</h1>
          <p class="text-sm text-gray-600 mt-1">{{ client.trade_name || 'Sin nombre comercial' }}</p>
          <div class="flex items-center gap-2 mt-2">
            <span
              class="px-2 py-1 text-xs font-medium rounded-full"
              :class="{
                'bg-green-100 text-green-800': client.status === 'active',
                'bg-gray-100 text-gray-800': client.status === 'inactive',
                'bg-red-100 text-red-800': client.status === 'blocked',
              }"
            >
              {{ statusLabels[client.status] }}
            </span>
            <span class="px-2 py-1 text-xs font-medium bg-primary-100 text-primary-800 rounded-full">
              Categoría {{ client.category }}
            </span>
          </div>
        </div>

        <div class="flex gap-2">
          <Link
            v-if="can('clients.update')"
            :href="`/clientes/${client.id}/editar`"
            class="px-4 py-2 bg-primary-700 text-white rounded-md hover:bg-primary-800 transition-colors"
          >
            Editar
          </Link>
          <button
            v-if="can('clients.update')"
            @click="toggleStatus"
            class="px-4 py-2 rounded-md transition-colors"
            :class="client.status === 'active' 
              ? 'bg-orange-600 text-white hover:bg-orange-700' 
              : 'bg-green-600 text-white hover:bg-green-700'"
          >
            {{ client.status === 'active' ? 'Bloquear' : 'Desbloquear' }}
          </button>
          <Link
            :href="`/clientes/${client.id}/statistics`"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
          >
            Estadísticas
          </Link>
          <button
            v-if="can('clients.delete')"
            @click="deleteClient"
            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors"
          >
            Eliminar
          </button>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <Card>
          <CardContent class="p-4">
            <p class="text-sm text-gray-600">Crédito Disponible</p>
            <p class="text-2xl font-bold text-primary-700">
              Bs {{ formatNumber(client.available_credit || 0) }}
            </p>
            <p class="text-xs text-gray-500 mt-1">
              de Bs {{ formatNumber(client.credit_limit || 0) }}
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-4">
            <p class="text-sm text-gray-600">Saldo Pendiente</p>
            <p class="text-2xl font-bold text-orange-600">
              Bs {{ formatNumber(client.pending_balance || 0) }}
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-4">
            <p class="text-sm text-gray-600">Última Compra</p>
            <p class="text-lg font-semibold text-gray-900">
              {{ client.last_purchase_date ? formatDate(client.last_purchase_date) : 'Sin compras' }}
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-4">
            <p class="text-sm text-gray-600">Total Compras</p>
            <p class="text-2xl font-bold text-gray-900">
              Bs {{ formatNumber(client.total_purchases || 0) }}
            </p>
          </CardContent>
        </Card>
      </div>

      <!-- Tabs -->
      <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex space-x-8">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            class="py-4 px-1 border-b-2 font-medium text-sm transition-colors"
            :class="{
              'border-primary-700 text-primary-700': activeTab === tab.id,
              'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== tab.id,
            }"
          >
            {{ tab.label }}
          </button>
        </nav>
      </div>

      <!-- Tab Content -->
      <div>
        <!-- General Info Tab -->
        <div v-show="activeTab === 'general'">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <Card>
              <CardHeader>
                <CardTitle>Información General</CardTitle>
              </CardHeader>
              <CardContent class="space-y-3">
                <div>
                  <p class="text-sm text-gray-600">NIT</p>
                  <p class="font-medium">{{ client.tax_id }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-600">Tipo de Cliente</p>
                  <p class="font-medium">{{ clientTypeLabels[client.client_type] }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-600">Teléfono</p>
                  <p class="font-medium">{{ client.phone || 'No registrado' }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-600">Email</p>
                  <p class="font-medium">{{ client.email || 'No registrado' }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-600">Dirección</p>
                  <p class="font-medium">{{ client.address || 'No registrada' }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-600">Ciudad / Departamento</p>
                  <p class="font-medium">{{ [client.city, client.state].filter(Boolean).join(', ') || 'No registrada' }}</p>
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Información Comercial</CardTitle>
              </CardHeader>
              <CardContent class="space-y-3">
                <div>
                  <p class="text-sm text-gray-600">Vendedor/Preventista</p>
                  <p class="font-medium">{{ client.salesperson?.name || 'Sin asignar' }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-600">Cobrador</p>
                  <p class="font-medium">{{ client.collector?.name || 'Sin asignar' }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-600">Lista de Precios</p>
                  <p class="font-medium">{{ client.price_list?.name || 'Predeterminada' }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-600">Condición de Pago</p>
                  <p class="font-medium">{{ client.payment_term?.name || 'Sin definir' }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-600">Descuento Predeterminado</p>
                  <p class="font-medium">{{ client.default_discount }}%</p>
                </div>
                <div>
                  <p class="text-sm text-gray-600">Límite de Crédito</p>
                  <p class="font-medium">Bs {{ formatNumber(client.credit_limit || 0) }}</p>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>

        <!-- Contacts Tab -->
        <div v-show="activeTab === 'contacts'">
          <Card>
            <CardHeader>
              <div class="flex items-center justify-between">
                <CardTitle>Contactos</CardTitle>
                <button
                  v-if="can('clients.update')"
                  class="px-3 py-1 text-sm bg-primary-700 text-white rounded-md hover:bg-primary-800"
                >
                  Agregar Contacto
                </button>
              </div>
            </CardHeader>
            <CardContent>
              <div v-if="client.contacts?.length > 0" class="space-y-4">
                <div
                  v-for="contact in client.contacts"
                  :key="contact.id"
                  class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50"
                >
                  <div class="flex items-start justify-between">
                    <div>
                      <p class="font-medium text-gray-900">{{ contact.name }}</p>
                      <p class="text-sm text-gray-600">{{ contact.position || 'Sin cargo' }}</p>
                      <div class="mt-2 space-y-1 text-sm text-gray-600">
                        <p v-if="contact.phone">📞 {{ contact.phone }}</p>
                        <p v-if="contact.email">📧 {{ contact.email }}</p>
                      </div>
                    </div>
                    <span
                      v-if="contact.is_primary"
                      class="px-2 py-1 text-xs font-medium bg-accent-100 text-accent-800 rounded-full"
                    >
                      Principal
                    </span>
                  </div>
                </div>
              </div>
              <p v-else class="text-gray-500 text-center py-8">
                No hay contactos registrados
              </p>
            </CardContent>
          </Card>
        </div>

        <!-- Credit Tab -->
        <div v-show="activeTab === 'credit'">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <Card>
              <CardHeader>
                <CardTitle>Información de Crédito</CardTitle>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <p class="text-sm text-gray-600">Límite de Crédito</p>
                    <p class="text-lg font-semibold text-gray-900">Bs {{ formatNumber(client.credit_limit || 0) }}</p>
                  </div>
                  <div>
                    <p class="text-sm text-gray-600">Días de Crédito</p>
                    <p class="text-lg font-semibold text-gray-900">{{ client.credit_days || 0 }} días</p>
                  </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <p class="text-sm text-gray-600">Saldo Pendiente</p>
                    <p class="text-lg font-semibold text-orange-600">Bs {{ formatNumber(client.pending_balance || 0) }}</p>
                  </div>
                  <div>
                    <p class="text-sm text-gray-600">Crédito Disponible</p>
                    <p class="text-lg font-semibold text-green-600">Bs {{ formatNumber(client.available_credit || 0) }}</p>
                  </div>
                </div>
                <div>
                  <p class="text-sm text-gray-600">Utilización de Crédito</p>
                  <div class="mt-2">
                    <div class="w-full bg-gray-200 rounded-full h-2">
                      <div 
                        class="bg-primary-600 h-2 rounded-full transition-all duration-300"
                        :style="{ width: `${Math.min(100, (client.pending_balance / client.credit_limit) * 100)}%` }"
                      ></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                      {{ Math.round((client.pending_balance / client.credit_limit) * 100) }}% utilizado
                    </p>
                  </div>
                </div>
                <div v-if="can('clients.update')" class="pt-4 border-t">
                  <button
                    @click="showCreditModal = true"
                    class="w-full px-4 py-2 bg-primary-700 text-white rounded-md hover:bg-primary-800 transition-colors"
                  >
                    Actualizar Límite de Crédito
                  </button>
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <div class="flex items-center justify-between">
                  <CardTitle>Historial de Crédito</CardTitle>
                  <Link
                    :href="`/clientes/${client.id}/credit-history`"
                    class="text-sm text-primary-700 hover:text-primary-800"
                  >
                    Ver completo
                  </Link>
                </div>
              </CardHeader>
              <CardContent>
                <div v-if="client.credit_history?.length > 0" class="space-y-3">
                  <div
                    v-for="entry in client.credit_history.slice(0, 5)"
                    :key="entry.id"
                    class="p-3 bg-gray-50 rounded-lg"
                  >
                    <div class="flex justify-between items-start">
                      <div>
                        <p class="text-sm font-medium text-gray-900">
                          Bs {{ formatNumber(entry.old_limit) }} → Bs {{ formatNumber(entry.new_limit) }}
                        </p>
                        <p class="text-xs text-gray-600">{{ entry.reason }}</p>
                      </div>
                      <div class="text-right">
                        <p class="text-xs text-gray-500">{{ formatDate(entry.created_at) }}</p>
                        <p class="text-xs text-gray-500">{{ entry.changer?.name }}</p>
                      </div>
                    </div>
                  </div>
                </div>
                <p v-else class="text-gray-500 text-center py-4">
                  No hay historial de cambios de crédito
                </p>
              </CardContent>
            </Card>
          </div>
        </div>

        <!-- Sales History Tab -->
        <div v-show="activeTab === 'sales'">
          <Card>
            <CardHeader>
              <CardTitle>Historial de Ventas</CardTitle>
            </CardHeader>
            <CardContent>
              <p class="text-gray-500 text-center py-8">
                El historial de ventas estará disponible cuando se implemente el módulo de ventas
              </p>
            </CardContent>
          </Card>
        </div>

        <!-- Receivables Tab -->
        <div v-show="activeTab === 'receivables'">
          <Card>
            <CardHeader>
              <CardTitle>Cuentas por Cobrar</CardTitle>
            </CardHeader>
            <CardContent>
              <p class="text-gray-500 text-center py-8">
                Las cuentas por cobrar estarán disponibles cuando se implemente el módulo correspondiente
              </p>
            </CardContent>
          </Card>
        </div>

        <!-- Activity Tab -->
        <div v-show="activeTab === 'activity'">
          <Card>
            <CardHeader>
              <CardTitle>Actividad Reciente</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="space-y-4">
                <div class="flex items-start gap-3">
                  <div class="w-2 h-2 bg-primary-700 rounded-full mt-2"></div>
                  <div>
                    <p class="text-sm font-medium">Cliente creado</p>
                    <p class="text-xs text-gray-500">{{ formatDate(client.created_at) }}</p>
                  </div>
                </div>
                <div v-if="client.updated_at !== client.created_at" class="flex items-start gap-3">
                  <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                  <div>
                    <p class="text-sm font-medium">Última actualización</p>
                    <p class="text-xs text-gray-500">{{ formatDate(client.updated_at) }}</p>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardHeader, CardTitle, CardContent } from '@/Components/ui'
import { usePermissions } from '@/composables/usePermissions'


const { can } = usePermissions()

const props = defineProps({
  client: Object,
})

const activeTab = ref('general')

const tabs = [
  { id: 'general', label: 'Información General' },
  { id: 'contacts', label: 'Contactos' },
  { id: 'credit', label: 'Crédito' },
  { id: 'sales', label: 'Ventas' },
  { id: 'receivables', label: 'Cuentas por Cobrar' },
  { id: 'activity', label: 'Actividad' },
]

const statusLabels = {
  active: 'Activo',
  inactive: 'Inactivo',
  blocked: 'Bloqueado',
}

const clientTypeLabels = {
  pharmacy: 'Farmacia',
  chain: 'Cadena de Farmacias',
  hospital: 'Hospital',
  clinic: 'Clínica',
  other: 'Otro',
}

const formatNumber = (number) => {
  return new Intl.NumberFormat('es-BO', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(number)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('es-CO', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
}

const deleteClient = () => {
  if (confirm('¿Está seguro de eliminar este cliente? Esta acción no se puede deshacer.')) {
    router.delete(`/clientes/${props.client.id}`)
  }
}

const toggleStatus = () => {
  const action = props.client.status === 'active' ? 'bloquear' : 'desbloquear'
  if (confirm(`¿Está seguro de ${action} este cliente?`)) {
    router.post(`/clientes/${props.client.id}/toggle-status`)
  }
}
</script>
