<template>
  <AdminLayout>
    <div class="p-6">
      <!-- Error State -->
      <div v-if="!payment" class="text-center py-12">
        <CreditCard class="mx-auto h-12 w-12 text-gray-400" />
        <h3 class="mt-2 text-sm font-medium text-gray-900">Pago no encontrado</h3>
        <p class="mt-1 text-sm text-gray-500">El pago que buscas no existe o ha sido eliminado.</p>
        <Link
          href="/cuentas-por-cobrar/pagos/listado"
          class="mt-4 inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-700 hover:bg-primary-800"
        >
          Volver a Historial de Pagos
        </Link>
      </div>

      <!-- Payment Content -->
      <div v-else>
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
          <div>
            <Link href="/cuentas-por-cobrar/pagos/listado" class="text-sm text-primary-700 hover:text-primary-800 mb-2 inline-block">
              ← Volver a historial de pagos
            </Link>
            <h1 class="text-2xl font-bold text-gray-900">Detalle de Pago</h1>
            <p class="text-sm text-gray-600 mt-1">Nota de Pago: {{ payment.payment_number }}</p>
            <p v-if="payment.payment_reference" class="text-xs text-gray-500 mt-1">
              Referencia: {{ payment.payment_reference }}
            </p>
          </div>
          <div class="flex items-center gap-2">
            <button
              @click="printPaymentNote"
              class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors flex items-center gap-2"
            >
              <Printer class="h-4 w-4" />
              Imprimir Nota
            </button>
            <button
              v-if="canApprovePayment"
              @click="approvePayment"
              class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors"
            >
              Aprobar
            </button>
            <button
              v-if="canCancelPayment"
              @click="showCancelModal = true"
              class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors"
            >
              Cancelar
            </button>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Detalle principal -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Información del Pago -->
            <Card>
              <CardHeader>
                <CardTitle>Información del Pago</CardTitle>
              </CardHeader>
              <CardContent>
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-500">Número de Nota</label>
                    <div class="text-sm font-semibold text-gray-900">{{ payment.payment_number }}</div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-500">Estado</label>
                    <span
                      class="px-2 py-1 text-xs font-medium rounded-full"
                      :class="getStatusColorClass(payment.status)"
                    >
                      {{ payment.status_label }}
                    </span>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-500">Fecha de Pago</label>
                    <div class="text-sm text-gray-900">{{ formatDate(payment.payment_date) }}</div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-500">Método de Pago</label>
                    <div class="text-sm text-gray-900">{{ payment.payment_method_label }}</div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-500">Monto</label>
                    <div class="text-lg font-bold text-primary-600">{{ formatPrice(payment.amount) }}</div>
                  </div>
                  <div v-if="payment.payment_reference">
                    <label class="block text-sm font-medium text-gray-500">Referencia</label>
                    <div class="text-sm text-gray-900">{{ payment.payment_reference }}</div>
                  </div>
                  <div v-if="payment.bank_name">
                    <label class="block text-sm font-medium text-gray-500">Banco</label>
                    <div class="text-sm text-gray-900">{{ payment.bank_name }}</div>
                  </div>
                  <div v-if="payment.account_number">
                    <label class="block text-sm font-medium text-gray-500">Número de Cuenta</label>
                    <div class="text-sm text-gray-900">{{ payment.account_number }}</div>
                  </div>
                  <div v-if="payment.check_number">
                    <label class="block text-sm font-medium text-gray-500">Número de Cheque</label>
                    <div class="text-sm text-gray-900">{{ payment.check_number }}</div>
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Información del Cliente -->
            <Card v-if="payment.client">
              <CardHeader>
                <CardTitle>Información del Cliente</CardTitle>
              </CardHeader>
              <CardContent>
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-500">Razón Social</label>
                    <div class="text-sm text-gray-900">{{ payment.client.business_name || 'N/A' }}</div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-500">Nombre Comercial</label>
                    <div class="text-sm text-gray-900">{{ payment.client.trade_name || 'N/A' }}</div>
                  </div>
                  <div v-if="payment.client.tax_id">
                    <label class="block text-sm font-medium text-gray-500">NIT</label>
                    <div class="text-sm text-gray-900">{{ payment.client.tax_id }}</div>
                  </div>
                  <div v-if="payment.client.phone">
                    <label class="block text-sm font-medium text-gray-500">Teléfono</label>
                    <div class="text-sm text-gray-900">{{ payment.client.phone }}</div>
                  </div>
                  <div v-if="payment.client.address" class="col-span-2">
                    <label class="block text-sm font-medium text-gray-500">Dirección</label>
                    <div class="text-sm text-gray-900">{{ payment.client.address }}</div>
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Información de la Factura -->
            <Card v-if="payment.invoice">
              <CardHeader>
                <CardTitle>Factura Relacionada</CardTitle>
              </CardHeader>
              <CardContent>
                <div class="flex items-center justify-between">
                  <div>
                    <label class="block text-sm font-medium text-gray-500">Número de Factura</label>
                    <div class="text-sm font-semibold text-gray-900">
                      {{ payment.invoice.sale?.invoice_number || payment.invoice.invoice_number }}
                    </div>
                    <div v-if="payment.invoice.sale?.invoice_number && payment.invoice.invoice_number" class="text-xs text-gray-500 mt-1">
                      Referencia: {{ payment.invoice.invoice_number }}
                    </div>
                  </div>
                  <Link
                    :href="`/cuentas-por-cobrar/${payment.invoice.id}`"
                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors text-sm"
                  >
                    Ver Factura
                  </Link>
                </div>
              </CardContent>
            </Card>

            <!-- Observaciones -->
            <Card v-if="payment.notes">
              <CardHeader>
                <CardTitle>Observaciones</CardTitle>
              </CardHeader>
              <CardContent>
                <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ payment.notes }}</p>
              </CardContent>
            </Card>
          </div>

          <!-- Sidebar -->
          <div class="space-y-6">
            <!-- Resumen -->
            <Card>
              <CardHeader>
                <CardTitle>Resumen</CardTitle>
              </CardHeader>
              <CardContent class="space-y-3">
                <div class="flex justify-between">
                  <span class="text-sm text-gray-600">Monto del Pago:</span>
                  <span class="text-sm font-bold text-gray-900">{{ formatPrice(payment.amount) }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-sm text-gray-600">Estado:</span>
                  <span
                    class="px-2 py-1 text-xs font-medium rounded-full"
                    :class="getStatusColorClass(payment.status)"
                  >
                    {{ payment.status_label }}
                  </span>
                </div>
              </CardContent>
            </Card>

            <!-- Información de Registro -->
            <Card>
              <CardHeader>
                <CardTitle>Información de Registro</CardTitle>
              </CardHeader>
              <CardContent class="space-y-3">
                <div>
                  <label class="block text-sm font-medium text-gray-500">Registrado por</label>
                  <div class="text-sm text-gray-900">{{ payment.creator?.name || 'Sistema' }}</div>
                  <div class="text-xs text-gray-500 mt-1">{{ formatDateTime(payment.created_at) }}</div>
                </div>
                <div v-if="payment.approved_at">
                  <label class="block text-sm font-medium text-gray-500">Aprobado por</label>
                  <div class="text-sm text-gray-900">{{ payment.approver?.name || 'N/A' }}</div>
                  <div class="text-xs text-gray-500 mt-1">{{ formatDateTime(payment.approved_at) }}</div>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>

      <!-- Cancel Payment Modal -->
      <div v-if="showCancelModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
          <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Cancelar Pago</h3>
            <form @submit.prevent="submitCancellation">
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Motivo de cancelación <span class="text-red-500">*</span>
                </label>
                <textarea
                  v-model="cancelForm.reason"
                  rows="3"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                  placeholder="Motivo de la cancelación..."
                ></textarea>
              </div>
              <div class="flex justify-end gap-2">
                <button
                  type="button"
                  @click="showCancelModal = false"
                  class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50"
                >
                  Cancelar
                </button>
                <button
                  type="submit"
                  class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                >
                  Cancelar Pago
                </button>
              </div>
            </form>
          </div>
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
import { CreditCard, Printer } from 'lucide-vue-next'

const { can } = usePermissions()

const props = defineProps({
  payment: {
    type: Object,
    required: true
  }
})

const showCancelModal = ref(false)

const cancelForm = useForm({
  reason: '',
})

const canApprovePayment = computed(() => {
  return can('payments.approve') && props.payment?.status === 'pending'
})

const canCancelPayment = computed(() => {
  return can('payments.cancel') && ['pending', 'completed'].includes(props.payment?.status)
})

const getStatusColorClass = (status) => {
  const classes = {
    'pending': 'bg-yellow-100 text-yellow-800',
    'completed': 'bg-green-100 text-green-800',
    'cancelled': 'bg-gray-100 text-gray-800',
    'rejected': 'bg-red-100 text-red-800',
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('es-BO', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatDateTime = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleString('es-BO', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatPrice = (price) => {
  return new Intl.NumberFormat('es-BO', {
    style: 'currency',
    currency: 'BOB',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(price || 0)
}

const printPaymentNote = () => {
  window.open(`/cuentas-por-cobrar/pagos/${props.payment.id}/imprimir`, '_blank')
}

const approvePayment = () => {
  if (confirm(`¿Está seguro de aprobar el pago ${props.payment.payment_number}?`)) {
    router.post(`/cuentas-por-cobrar/pagos/${props.payment.id}/aprobar`, {}, {
      onSuccess: () => {
        router.reload({ only: ['payment'] })
      }
    })
  }
}

const submitCancellation = () => {
  cancelForm.post(`/cuentas-por-cobrar/pagos/${props.payment.id}/cancelar`, {
    onSuccess: () => {
      showCancelModal.value = false
      cancelForm.reset()
      router.reload({ only: ['payment'] })
    }
  })
}
</script>

