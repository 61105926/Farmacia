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
          <p class="text-sm text-gray-600 mt-1">{{ client.trade_name || 'Sin razón social' }}</p>
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
                  @click="openContactModal(null)"
                  class="px-3 py-1 text-sm bg-primary-700 text-white rounded-md hover:bg-primary-800"
                >
                  + Agregar Contacto
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
                    <div class="flex items-center gap-2">
                      <span
                        v-if="contact.is_primary"
                        class="px-2 py-1 text-xs font-medium bg-primary-100 text-primary-800 rounded-full"
                      >
                        Principal
                      </span>
                      <button
                        v-if="can('clients.update')"
                        @click="openContactModal(contact)"
                        class="text-xs text-blue-600 hover:text-blue-800"
                      >Editar</button>
                      <button
                        v-if="can('clients.update')"
                        @click="deleteContact(contact)"
                        class="text-xs text-red-600 hover:text-red-800"
                      >Eliminar</button>
                    </div>
                  </div>
                </div>
              </div>
              <p v-else class="text-gray-500 text-center py-8">
                No hay contactos registrados
              </p>
            </CardContent>
          </Card>

          <!-- Modal Contacto -->
          <div v-if="showContactModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">
                {{ editingContact ? 'Editar Contacto' : 'Nuevo Contacto' }}
              </h3>
              <form @submit.prevent="submitContact" class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                  <input v-model="contactForm.name" type="text" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500" />
                </div>
                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cargo</label>
                    <input v-model="contactForm.position" type="text"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500" />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                    <input v-model="contactForm.phone" type="text"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500" />
                  </div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                  <input v-model="contactForm.email" type="email"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500" />
                </div>
                <div class="flex items-center gap-2">
                  <input v-model="contactForm.is_primary" type="checkbox" id="is_primary" class="rounded" />
                  <label for="is_primary" class="text-sm text-gray-700">Contacto principal</label>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                  <button type="button" @click="showContactModal = false"
                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">
                    Cancelar
                  </button>
                  <button type="submit" :disabled="contactForm.processing"
                    class="px-4 py-2 bg-primary-700 text-white rounded-md hover:bg-primary-800 disabled:opacity-50">
                    {{ contactForm.processing ? 'Guardando...' : 'Guardar' }}
                  </button>
                </div>
              </form>
            </div>
          </div>
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
                        :style="{ width: `${creditUtilizationPct}%` }"
                      ></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                      {{ creditUtilizationPct }}% utilizado
                    </p>
                  </div>
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
                          Bs {{ formatNumber(entry.balance) }} → Bs {{ formatNumber(entry.amount) }}
                        </p>
                        <p class="text-xs text-gray-600">{{ entry.description }}</p>
                      </div>
                      <div class="text-right">
                        <p class="text-xs text-gray-500">{{ formatDate(entry.created_at) }}</p>
                        <p class="text-xs text-gray-500">{{ entry.changer?.name }}</p>
                      </div>
                    </div>
                  </div>
                </div>
                <p v-else class="text-gray-500 text-center py-4">
                  No hay historial de cambios de crédito.<br>
                  <span class="text-xs">Se registra automáticamente al editar el límite en el formulario del cliente.</span>
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
            <CardContent class="p-0">
              <div v-if="recentSales?.length > 0" class="overflow-x-auto">
                <table class="w-full text-sm">
                  <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                      <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">N° Venta</th>
                      <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                      <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                      <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado Pago</th>
                      <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Método</th>
                      <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vendedor</th>
                      <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acción</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-100">
                    <tr v-for="sale in recentSales" :key="sale.id" class="hover:bg-gray-50">
                      <td class="px-4 py-3 font-mono text-gray-800">{{ sale.invoice_number || sale.code || `#${sale.id}` }}</td>
                      <td class="px-4 py-3 text-gray-600">{{ formatDate(sale.created_at) }}</td>
                      <td class="px-4 py-3 text-right font-medium text-gray-900">Bs {{ formatNumber(sale.total) }}</td>
                      <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium"
                          :class="{
                            'bg-green-100 text-green-800': sale.payment_status === 'paid',
                            'bg-yellow-100 text-yellow-800': sale.payment_status === 'pending',
                            'bg-red-100 text-red-800': sale.payment_status === 'overdue',
                          }">
                          {{ { paid: 'Pagado', pending: 'Pendiente', overdue: 'Vencido', partial: 'Parcial' }[sale.payment_status] || sale.payment_status }}
                        </span>
                      </td>
                      <td class="px-4 py-3 text-gray-600 capitalize">{{ sale.payment_method || '—' }}</td>
                      <td class="px-4 py-3 text-gray-600">{{ sale.salesperson?.name || '—' }}</td>
                      <td class="px-4 py-3 text-right">
                        <Link :href="`/ventas/${sale.id}`" class="text-primary-700 hover:text-primary-900 text-xs">Ver</Link>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <p v-else class="text-gray-500 text-center py-8">No hay ventas registradas para este cliente.</p>
            </CardContent>
          </Card>
        </div>

        <!-- Receivables Tab -->
        <div v-show="activeTab === 'receivables'">
          <Card>
            <CardHeader>
              <CardTitle>Cuentas por Cobrar</CardTitle>
            </CardHeader>
            <CardContent class="p-0">
              <div v-if="recentReceivables?.length > 0" class="overflow-x-auto">
                <table class="w-full text-sm">
                  <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                      <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Referencia</th>
                      <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vencimiento</th>
                      <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Monto</th>
                      <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Saldo</th>
                      <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-100">
                    <tr v-for="rec in recentReceivables" :key="rec.id" class="hover:bg-gray-50">
                      <td class="px-4 py-3 text-gray-800">{{ rec.invoice_id ? `Factura #${rec.invoice_id}` : `#${rec.id}` }}</td>
                      <td class="px-4 py-3 text-gray-600">{{ rec.due_date ? formatDate(rec.due_date) : '—' }}</td>
                      <td class="px-4 py-3 text-right text-gray-900">Bs {{ formatNumber(rec.amount) }}</td>
                      <td class="px-4 py-3 text-right font-medium"
                        :class="rec.balance > 0 ? 'text-red-600' : 'text-green-600'">
                        Bs {{ formatNumber(rec.balance) }}
                      </td>
                      <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium"
                          :class="{
                            'bg-green-100 text-green-800': rec.status === 'paid',
                            'bg-yellow-100 text-yellow-800': rec.status === 'pending',
                            'bg-orange-100 text-orange-800': rec.status === 'partial',
                            'bg-red-100 text-red-800': rec.status === 'overdue',
                          }">
                          {{ { paid: 'Pagado', pending: 'Pendiente', partial: 'Parcial', overdue: 'Vencido', cancelled: 'Cancelado' }[rec.status] || rec.status }}
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <p v-else class="text-gray-500 text-center py-8">No hay cuentas por cobrar para este cliente.</p>
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
import { ref, computed } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardHeader, CardTitle, CardContent } from '@/Components/ui'
import { usePermissions } from '@/composables/usePermissions'


const { can } = usePermissions()

const props = defineProps({
  client: Object,
  recentSales: { type: Array, default: () => [] },
  recentReceivables: { type: Array, default: () => [] },
})

const activeTab = ref('general')

const creditUtilizationPct = computed(() => {
  const limit = props.client?.credit_limit || 0
  const balance = props.client?.pending_balance || 0
  if (!limit) return 0
  return Math.min(100, Math.round((balance / limit) * 100))
})

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

// Contactos
const showContactModal = ref(false)
const editingContact = ref(null)

const contactForm = useForm({
  name: '',
  position: '',
  phone: '',
  email: '',
  is_primary: false,
})

const openContactModal = (contact) => {
  editingContact.value = contact
  contactForm.name = contact?.name || ''
  contactForm.position = contact?.position || ''
  contactForm.phone = contact?.phone || ''
  contactForm.email = contact?.email || ''
  contactForm.is_primary = contact?.is_primary || false
  showContactModal.value = true
}

const submitContact = () => {
  const clientId = props.client.id
  if (editingContact.value) {
    contactForm.put(`/clientes/${clientId}/contactos/${editingContact.value.id}`, {
      onSuccess: () => { showContactModal.value = false },
    })
  } else {
    contactForm.post(`/clientes/${clientId}/contactos`, {
      onSuccess: () => { showContactModal.value = false },
    })
  }
}

const deleteContact = (contact) => {
  if (!confirm(`¿Eliminar el contacto "${contact.name}"?`)) return
  router.delete(`/clientes/${props.client.id}/contactos/${contact.id}`)
}
</script>
