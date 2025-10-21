<template>
  <AdminLayout>
    <!-- Page Header -->
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Historial de Crédito</h1>
          <p class="text-sm text-gray-600 mt-1">{{ client.business_name || client.trade_name }}</p>
        </div>
        <div class="flex gap-2">
          <Link
            :href="`/clientes/${client.id}`"
            class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors"
          >
            Ver Cliente
          </Link>
          <Link
            :href="`/clientes/${client.id}/editar`"
            class="px-4 py-2 bg-primary-700 text-white rounded-md hover:bg-primary-800 transition-colors"
          >
            Editar Cliente
          </Link>
        </div>
      </div>
    </template>

    <!-- Client Info Card -->
    <Card class="mb-6">
      <CardContent class="p-6">
        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <div class="flex-shrink-0 h-16 w-16">
              <div class="h-16 w-16 rounded-full bg-green-100 flex items-center justify-center">
                <CreditCard class="h-8 w-8 text-green-600" />
              </div>
            </div>
            <div class="ml-6">
              <h2 class="text-xl font-semibold text-gray-900">
                {{ client.business_name || client.trade_name }}
              </h2>
              <p class="text-sm text-gray-500">{{ client.email || 'Sin email' }}</p>
              <p class="text-sm text-gray-500">{{ client.phone || 'Sin teléfono' }}</p>
            </div>
          </div>
          <div class="text-right">
            <p class="text-sm text-gray-500">Límite de Crédito</p>
            <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(client.credit_limit || 0) }}</p>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Credit Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
      <Card>
        <CardContent class="p-6">
          <div class="flex items-center">
            <DollarSign class="h-8 w-8 text-green-500" />
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Crédito Utilizado</p>
              <p class="text-2xl font-bold">{{ formatCurrency(creditSummary.used_credit || 0) }}</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardContent class="p-6">
          <div class="flex items-center">
            <TrendingUp class="h-8 w-8 text-blue-500" />
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Crédito Disponible</p>
              <p class="text-2xl font-bold">{{ formatCurrency(creditSummary.available_credit || 0) }}</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardContent class="p-6">
          <div class="flex items-center">
            <AlertTriangle class="h-8 w-8 text-orange-500" />
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Facturas Vencidas</p>
              <p class="text-2xl font-bold">{{ creditSummary.overdue_invoices || 0 }}</p>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Credit History Table -->
    <Card>
      <CardContent class="p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900">Historial de Crédito</h3>
          <div class="flex gap-2">
            <select v-model="selectedPeriod" class="px-3 py-2 border border-gray-300 rounded-md text-sm">
              <option value="all">Todos los períodos</option>
              <option value="30">Últimos 30 días</option>
              <option value="90">Últimos 90 días</option>
              <option value="365">Último año</option>
            </select>
          </div>
        </div>

        <div v-if="creditHistory && creditHistory.length > 0" class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Saldo</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="entry in creditHistory" :key="entry.id">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatDate(entry.created_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <Badge :variant="getTypeVariant(entry.type)">
                    {{ getTypeLabel(entry.type) }}
                  </Badge>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">
                  {{ entry.description }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  <span :class="getAmountClass(entry.type)">
                    {{ getAmountPrefix(entry.type) }}{{ formatCurrency(Math.abs(entry.amount)) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatCurrency(entry.balance) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <Badge :variant="getStatusVariant(entry.status)">
                    {{ getStatusLabel(entry.status) }}
                  </Badge>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="text-center py-8">
          <CreditCard class="mx-auto h-12 w-12 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900">Sin historial de crédito</h3>
          <p class="mt-1 text-sm text-gray-500">
            No hay movimientos de crédito registrados para este cliente.
          </p>
        </div>
      </CardContent>
    </Card>

    <!-- Overdue Invoices -->
    <Card v-if="overdueInvoices && overdueInvoices.length > 0" class="mt-6">
      <CardContent class="p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Facturas Vencidas</h3>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Factura</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Vencimiento</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Días Vencido</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="invoice in overdueInvoices" :key="invoice.id">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  {{ invoice.invoice_number }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(invoice.due_date) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatCurrency(invoice.total_amount) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <Badge variant="error">
                    {{ getDaysOverdue(invoice.due_date) }} días
                  </Badge>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <Link
                    :href="`/facturas/${invoice.id}`"
                    class="text-blue-600 hover:text-blue-900"
                  >
                    Ver Factura
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </CardContent>
    </Card>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Card from '@/Components/ui/Card.vue'
import CardContent from '@/Components/ui/CardContent.vue'
import Badge from '@/Components/ui/Badge.vue'
import { 
  CreditCard, 
  DollarSign, 
  TrendingUp, 
  AlertTriangle,
  Receipt,
  ShoppingCart,
  FileText
} from 'lucide-vue-next'

const props = defineProps({
  client: Object,
  creditHistory: Array,
  creditSummary: Object,
  overdueInvoices: Array
})

const selectedPeriod = ref('all')

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('es-BO', {
    style: 'currency',
    currency: 'BOB'
  }).format(amount)
}

const formatDate = (date) => {
  if (!date) return 'Sin fecha'
  return new Date(date).toLocaleDateString('es-BO')
}

const getDaysOverdue = (dueDate) => {
  if (!dueDate) return 0
  const today = new Date()
  const due = new Date(dueDate)
  const diffTime = today - due
  return Math.ceil(diffTime / (1000 * 60 * 60 * 24))
}

const getTypeVariant = (type) => {
  const variants = {
    'credit_granted': 'success',
    'credit_used': 'warning',
    'payment_received': 'primary',
    'credit_adjustment': 'info',
    'default': 'secondary'
  }
  return variants[type] || variants.default
}

const getTypeLabel = (type) => {
  const labels = {
    'credit_granted': 'Crédito Otorgado',
    'credit_used': 'Crédito Usado',
    'payment_received': 'Pago Recibido',
    'credit_adjustment': 'Ajuste de Crédito',
    'default': 'Movimiento'
  }
  return labels[type] || labels.default
}

const getAmountClass = (type) => {
  const classes = {
    'credit_granted': 'text-green-600',
    'credit_used': 'text-red-600',
    'payment_received': 'text-blue-600',
    'credit_adjustment': 'text-orange-600',
    'default': 'text-gray-600'
  }
  return classes[type] || classes.default
}

const getAmountPrefix = (type) => {
  const prefixes = {
    'credit_granted': '+',
    'credit_used': '-',
    'payment_received': '+',
    'credit_adjustment': '±',
    'default': ''
  }
  return prefixes[type] || prefixes.default
}

const getStatusVariant = (status) => {
  const variants = {
    'completed': 'success',
    'pending': 'warning',
    'cancelled': 'error',
    'default': 'secondary'
  }
  return variants[status] || variants.default
}

const getStatusLabel = (status) => {
  const labels = {
    'completed': 'Completado',
    'pending': 'Pendiente',
    'cancelled': 'Cancelado',
    'default': 'Desconocido'
  }
  return labels[status] || labels.default
}
</script>
