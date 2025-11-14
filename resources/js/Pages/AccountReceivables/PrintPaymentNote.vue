<template>
  <div class="min-h-screen bg-white">
    <!-- Print Button -->
    <div class="print:hidden p-4 bg-gray-100 border-b">
      <div class="flex justify-between items-center">
        <h1 class="text-lg font-semibold text-gray-900">Imprimir Nota de Pago</h1>
        <div class="flex gap-2">
          <button
            @click="printDocument"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors flex items-center gap-2"
          >
            <Printer class="h-4 w-4" />
            Imprimir
          </button>
          <button
            @click="goBack"
            class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors flex items-center gap-2"
          >
            <ArrowLeft class="h-4 w-4" />
            Volver
          </button>
        </div>
      </div>
    </div>

    <!-- Document Content -->
    <div id="print-content" class="max-w-4xl mx-auto p-8 print:p-4">
      <!-- Company Header with Border -->
      <div class="border-2 border-gray-800 print:border-black p-6 print:p-4 mb-8 print:mb-6">
        <div class="text-center">
          <h1 class="text-4xl font-bold text-gray-900 print:text-3xl mb-2 tracking-wide">FARMACIA PANDO</h1>
          <div class="border-t-2 border-gray-800 print:border-black w-32 mx-auto my-3"></div>
          <p class="text-lg font-semibold text-gray-700 print:text-base mb-1">Sistema de Gestión Farmacéutica</p>
          <div class="mt-3 text-sm text-gray-600 space-y-1">
            <p class="font-medium">Dirección: Av. Principal #123, Cobija, Pando, Bolivia</p>
            <p>Teléfono: (591) 3-842-1234 | Email: info@farmaciapando.com</p>
          </div>
        </div>
      </div>

      <!-- Document Title -->
      <div class="text-center mb-8 print:mb-6">
        <div class="border-2 border-gray-800 print:border-black py-4 print:py-3 px-6 print:px-4 inline-block">
          <h2 class="text-3xl font-bold text-gray-900 print:text-2xl tracking-wider">NOTA DE PAGO</h2>
        </div>
        <div class="mt-4 flex justify-center gap-8 text-sm font-semibold text-gray-700">
          <div class="border-b-2 border-gray-400 print:border-black pb-1">
            <span class="text-gray-600">Nº:</span> <span class="text-gray-900">{{ payment.payment_number }}</span>
          </div>
          <div class="border-b-2 border-gray-400 print:border-black pb-1">
            <span class="text-gray-600">Fecha:</span> <span class="text-gray-900">{{ formatDate(payment.payment_date) }}</span>
          </div>
        </div>
      </div>

      <!-- Document Details -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8 print:mb-6">
        <!-- Client Information -->
        <div class="border-2 border-gray-300 print:border-black p-5 print:p-4">
          <h3 class="text-lg font-bold text-gray-900 mb-4 print:text-base border-b-2 border-gray-400 print:border-black pb-2 uppercase tracking-wide">
            Datos del Cliente
          </h3>
          <div class="space-y-2 text-sm">
            <div class="flex">
              <span class="font-semibold text-gray-700 w-32 flex-shrink-0">Razón Social:</span>
              <span class="text-gray-900">{{ payment.client?.business_name || 'N/A' }}</span>
            </div>
            <div class="flex">
              <span class="font-semibold text-gray-700 w-32 flex-shrink-0">Nombre Comercial:</span>
              <span class="text-gray-900">{{ payment.client?.trade_name || 'N/A' }}</span>
            </div>
            <div v-if="payment.client?.tax_id" class="flex">
              <span class="font-semibold text-gray-700 w-32 flex-shrink-0">NIT:</span>
              <span class="text-gray-900">{{ payment.client.tax_id }}</span>
            </div>
            <div v-if="payment.client?.address" class="flex">
              <span class="font-semibold text-gray-700 w-32 flex-shrink-0">Dirección:</span>
              <span class="text-gray-900">{{ payment.client.address }}</span>
            </div>
            <div v-if="payment.client?.phone" class="flex">
              <span class="font-semibold text-gray-700 w-32 flex-shrink-0">Teléfono:</span>
              <span class="text-gray-900">{{ payment.client.phone }}</span>
            </div>
          </div>
        </div>

        <!-- Payment Information -->
        <div class="border-2 border-gray-300 print:border-black p-5 print:p-4">
          <h3 class="text-lg font-bold text-gray-900 mb-4 print:text-base border-b-2 border-gray-400 print:border-black pb-2 uppercase tracking-wide">
            Datos del Pago
          </h3>
          <div class="space-y-2 text-sm">
            <div class="flex">
              <span class="font-semibold text-gray-700 w-32 flex-shrink-0">Estado:</span>
              <span :class="getStatusClass(payment.status)" class="font-medium">
                {{ payment.status_label }}
              </span>
            </div>
            <div class="flex">
              <span class="font-semibold text-gray-700 w-32 flex-shrink-0">Método:</span>
              <span class="text-gray-900">{{ payment.payment_method_label }}</span>
            </div>
            <div v-if="payment.payment_reference" class="flex">
              <span class="font-semibold text-gray-700 w-32 flex-shrink-0">Referencia:</span>
              <span class="text-gray-900">{{ payment.payment_reference }}</span>
            </div>
            <div v-if="payment.bank_name" class="flex">
              <span class="font-semibold text-gray-700 w-32 flex-shrink-0">Banco:</span>
              <span class="text-gray-900">{{ payment.bank_name }}</span>
            </div>
            <div v-if="payment.account_number" class="flex">
              <span class="font-semibold text-gray-700 w-32 flex-shrink-0">Nº Cuenta:</span>
              <span class="text-gray-900">{{ payment.account_number }}</span>
            </div>
            <div v-if="payment.check_number" class="flex">
              <span class="font-semibold text-gray-700 w-32 flex-shrink-0">Nº Cheque:</span>
              <span class="text-gray-900">{{ payment.check_number }}</span>
            </div>
            <div v-if="payment.invoice" class="flex">
              <span class="font-semibold text-gray-700 w-32 flex-shrink-0">Factura:</span>
              <span class="text-gray-900 font-medium">{{ payment.invoice.invoice_number }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Payment Amount - Formal Section -->
      <div class="mb-8 print:mb-6">
        <div class="border-2 border-gray-800 print:border-black p-6 print:p-5 bg-gray-50 print:bg-white">
          <div class="text-center mb-4">
            <h3 class="text-xl font-bold text-gray-900 print:text-lg uppercase tracking-wide mb-4">
              Recibí de
            </h3>
            <p class="text-lg font-semibold text-gray-900 print:text-base border-b-2 border-gray-400 print:border-black pb-2 inline-block px-8">
              {{ payment.client?.business_name || payment.client?.trade_name || 'N/A' }}
            </p>
          </div>
          
          <div class="text-center mt-6 print:mt-5">
            <p class="text-sm text-gray-600 mb-2 print:mb-1">La cantidad de:</p>
            <p class="text-xl font-bold text-gray-900 print:text-lg border-2 border-gray-400 print:border-black py-3 print:py-2 px-6 print:px-4 inline-block">
              {{ amountInWords(payment.amount) }}
            </p>
          </div>
          
          <div class="text-center mt-6 print:mt-5">
            <p class="text-sm text-gray-600 mb-2 print:mb-1">El monto de:</p>
            <div class="text-4xl font-bold text-gray-900 print:text-3xl border-2 border-gray-800 print:border-black py-4 print:py-3 px-8 print:px-6 inline-block">
              {{ formatCurrency(payment.amount) }}
            </div>
          </div>
          
          <div class="text-center mt-4 print:mt-3">
            <p class="text-sm text-gray-600 italic">
              En concepto de pago {{ payment.invoice ? `de la factura ${payment.invoice.invoice_number}` : '' }}
            </p>
          </div>
        </div>
      </div>

      <!-- Notes -->
      <div v-if="payment.notes" class="mb-6 print:mb-4">
        <div class="border-2 border-gray-300 print:border-black p-4 print:p-3">
          <h3 class="text-base font-bold text-gray-900 mb-3 print:mb-2 uppercase tracking-wide border-b border-gray-400 print:border-black pb-2">
            Observaciones
          </h3>
          <p class="text-sm text-gray-700 whitespace-pre-wrap leading-relaxed">{{ payment.notes }}</p>
        </div>
      </div>

      <!-- Approval Information -->
      <div v-if="payment.approved_at" class="mb-6 print:mb-4">
        <div class="border-2 border-green-600 print:border-black p-4 print:p-3 bg-green-50 print:bg-white">
          <div class="flex items-center gap-2 mb-3 print:mb-2 border-b border-green-400 print:border-black pb-2">
            <CheckCircle class="h-5 w-5 text-green-600 print:text-black" />
            <p class="text-base font-bold text-gray-900 uppercase tracking-wide">Pago Aprobado</p>
          </div>
          <div class="text-sm text-gray-700 space-y-1">
            <div class="flex">
              <span class="font-semibold w-40">Aprobado por:</span>
              <span>{{ payment.approver?.name || 'N/A' }}</span>
            </div>
            <div class="flex">
              <span class="font-semibold w-40">Fecha de aprobación:</span>
              <span>{{ formatDateTime(payment.approved_at) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Signatures Section -->
      <div class="mt-12 print:mt-10 mb-8 print:mb-6">
        <div class="grid grid-cols-2 gap-8 print:gap-6">
          <div class="text-center">
            <div class="border-t-2 border-gray-800 print:border-black pt-2 mt-16 print:mt-12">
              <p class="text-sm font-semibold text-gray-900">RECIBIDO POR</p>
              <p class="text-xs text-gray-600 mt-2">{{ payment.creator?.name || 'Sistema' }}</p>
              <p class="text-xs text-gray-500 mt-1">{{ formatDateTime(payment.created_at) }}</p>
            </div>
          </div>
          <div class="text-center">
            <div class="border-t-2 border-gray-800 print:border-black pt-2 mt-16 print:mt-12">
              <p class="text-sm font-semibold text-gray-900">ENTREGADO POR</p>
              <p class="text-xs text-gray-600 mt-2">{{ payment.client?.business_name || payment.client?.trade_name || 'Cliente' }}</p>
              <p class="text-xs text-gray-500 mt-1">_________________________</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="mt-8 print:mt-6 border-t-2 border-gray-800 print:border-black pt-4 print:pt-3">
        <div class="flex justify-between items-center text-xs text-gray-600">
          <div>
            <p class="font-semibold">Emitido por: {{ payment.creator?.name || 'Sistema' }}</p>
            <p>Fecha de emisión: {{ formatDateTime(payment.created_at) }}</p>
          </div>
          <div class="text-right">
            <p class="font-bold text-gray-900">NOTA DE PAGO Nº</p>
            <p class="text-lg font-bold text-gray-900">{{ payment.payment_number }}</p>
          </div>
        </div>
      </div>

      <!-- Footer Info -->
      <div class="mt-6 print:mt-4 text-center text-xs text-gray-500 border-t border-gray-300 print:border-black pt-3 print:pt-2">
        <p class="italic">Este documento fue generado automáticamente por el Sistema de Gestión Farmacéutica</p>
        <p class="mt-1">Fecha de impresión: {{ formatDate(new Date()) }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import { Printer, ArrowLeft, CheckCircle } from 'lucide-vue-next'

const props = defineProps({
  payment: {
    type: Object,
    required: true
  }
})

// Methods
const formatCurrency = (amount) => {
  return new Intl.NumberFormat('es-BO', {
    style: 'currency',
    currency: 'BOB',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(amount || 0)
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

const getStatusClass = (status) => {
  const classes = {
    'pending': 'text-yellow-600 font-medium',
    'completed': 'text-green-600 font-medium',
    'cancelled': 'text-red-600 font-medium',
    'rejected': 'text-red-600 font-medium'
  }
  return classes[status] || 'text-gray-600'
}

const amountInWords = (amount) => {
  if (!amount || amount === 0) return 'Cero'
  
  const ones = ['', 'uno', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve']
  const tens = ['', '', 'veinte', 'treinta', 'cuarenta', 'cincuenta', 'sesenta', 'setenta', 'ochenta', 'noventa']
  const teens = ['diez', 'once', 'doce', 'trece', 'catorce', 'quince', 'dieciséis', 'diecisiete', 'dieciocho', 'diecinueve']
  const hundreds = ['', 'ciento', 'doscientos', 'trescientos', 'cuatrocientos', 'quinientos', 'seiscientos', 'setecientos', 'ochocientos', 'novecientos']
  
  const num = Math.floor(amount)
  const cents = Math.round((amount - num) * 100)
  
  const convert = (n) => {
    if (n === 0) return ''
    if (n < 10) return ones[n]
    if (n < 20) return teens[n - 10]
    if (n < 100) {
      const ten = Math.floor(n / 10)
      const one = n % 10
      if (one === 0) return tens[ten]
      if (ten === 1) return 'dieci' + ones[one]
      return tens[ten] + (one > 0 ? ' y ' + ones[one] : '')
    }
    if (n < 1000) {
      const hundred = Math.floor(n / 100)
      const remainder = n % 100
      if (hundred === 1 && remainder === 0) return 'cien'
      return hundreds[hundred] + (remainder > 0 ? ' ' + convert(remainder) : '')
    }
    if (n < 1000000) {
      const thousand = Math.floor(n / 1000)
      const remainder = n % 1000
      const thousandText = thousand === 1 ? 'mil' : convert(thousand) + ' mil'
      return thousandText + (remainder > 0 ? ' ' + convert(remainder) : '')
    }
    return 'Número muy grande'
  }
  
  let result = convert(num)
  if (num === 0) result = 'Cero'
  if (num === 1) result = 'Un'
  
  result = result.charAt(0).toUpperCase() + result.slice(1)
  
  if (cents > 0) {
    result += ' con ' + convert(cents) + ' centavos'
  }
  
  return result + ' bolivianos'
}

const printDocument = () => {
  window.print()
}

const goBack = () => {
  router.visit(`/cuentas-por-cobrar/pagos/${props.payment.id}`)
}
</script>

<style scoped>
@media print {
  .print\\:hidden {
    display: none !important;
  }
  
  .print\\:p-4 {
    padding: 1rem !important;
  }
  
  .print\\:mb-6 {
    margin-bottom: 1.5rem !important;
  }
  
  .print\\:mb-4 {
    margin-bottom: 1rem !important;
  }
  
  .print\\:text-2xl {
    font-size: 1.5rem !important;
  }
  
  .print\\:text-xl {
    font-size: 1.25rem !important;
  }
  
  .print\\:text-base {
    font-size: 1rem !important;
  }
  
  .print\\:bg-transparent {
    background-color: transparent !important;
  }
  
  .print\\:border {
    border: 1px solid #000 !important;
  }
  
  .print\\:border-gray-300 {
    border-color: #d1d5db !important;
  }
  
  .print\\:border-black {
    border-color: #000 !important;
  }
  
  .print\\:mt-6 {
    margin-top: 1.5rem !important;
  }
}
</style>

