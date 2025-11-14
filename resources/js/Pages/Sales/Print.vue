<template>
  <div class="min-h-screen bg-white">
    <!-- Print Button -->
    <div class="print:hidden p-4 bg-gray-100 border-b">
      <div class="flex justify-between items-center">
        <h1 class="text-lg font-semibold text-gray-900">Imprimir Venta</h1>
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
      <!-- Company Header -->
      <div class="text-center mb-8 print:mb-6">
        <h1 class="text-3xl font-bold text-gray-900 print:text-2xl">FARMACIA PANDO</h1>
        <p class="text-lg text-gray-600 print:text-base">Sistema de Gestión Farmacéutica</p>
        <div class="mt-2 text-sm text-gray-500">
          <p>Dirección: Av. Principal #123, Cobija, Pando</p>
          <p>Teléfono: (591) 3-842-1234 | Email: info@farmaciapando.com</p>
        </div>
      </div>

      <!-- Document Title -->
      <div class="text-center mb-6 print:mb-4">
        <h2 class="text-2xl font-semibold text-gray-900 print:text-xl">COMPROBANTE DE VENTA</h2>
        <div class="mt-2 flex justify-center gap-8 text-sm text-gray-600">
          <span><strong>Código:</strong> {{ sale.code }}</span>
          <span><strong>Fecha:</strong> {{ formatDate(sale.created_at) }}</span>
        </div>
      </div>

      <!-- Document Details -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6 print:mb-4">
        <!-- Client Information -->
        <div class="bg-gray-50 p-4 rounded-lg print:bg-transparent print:p-0">
          <h3 class="text-lg font-semibold text-gray-900 mb-3 print:text-base">INFORMACIÓN DEL CLIENTE</h3>
          <div class="space-y-1 text-sm">
            <p><strong>Razón Social:</strong> {{ sale.client?.business_name || 'N/A' }}</p>
            <p><strong>Nombre Comercial:</strong> {{ sale.client?.trade_name || 'N/A' }}</p>
            <p><strong>Email:</strong> {{ sale.client?.email || 'N/A' }}</p>
            <p><strong>Teléfono:</strong> {{ sale.client?.phone || 'N/A' }}</p>
          </div>
        </div>

        <!-- Sale Information -->
        <div class="bg-gray-50 p-4 rounded-lg print:bg-transparent print:p-0">
          <h3 class="text-lg font-semibold text-gray-900 mb-3 print:text-base">INFORMACIÓN DE LA VENTA</h3>
          <div class="space-y-1 text-sm">
            <p><strong>Estado:</strong> 
              <span :class="getStatusClass(sale.status)">
                {{ getStatusText(sale.status) }}
              </span>
            </p>
            <p><strong>Método de Pago:</strong> {{ getPaymentMethodText(sale.payment_method) }}</p>
            <p><strong>Vendedor:</strong> {{ sale.salesperson?.name || 'N/A' }}</p>
            <p v-if="sale.completed_at"><strong>Completada:</strong> {{ formatDate(sale.completed_at) }}</p>
          </div>
        </div>
      </div>

      <!-- Products Table -->
      <div class="mb-6 print:mb-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-3 print:text-base">PRODUCTOS VENDIDOS</h3>
        <div class="overflow-x-auto">
          <table class="min-w-full border border-gray-300 print:border-black">
            <thead class="bg-gray-100 print:bg-gray-200">
              <tr>
                <th class="border border-gray-300 print:border-black px-4 py-2 text-left text-sm font-medium text-gray-900">
                  Código
                </th>
                <th class="border border-gray-300 print:border-black px-4 py-2 text-left text-sm font-medium text-gray-900">
                  Producto
                </th>
                <th class="border border-gray-300 print:border-black px-4 py-2 text-center text-sm font-medium text-gray-900">
                  Cantidad
                </th>
                <th class="border border-gray-300 print:border-black px-4 py-2 text-right text-sm font-medium text-gray-900">
                  Precio Unit.
                </th>
                <th class="border border-gray-300 print:border-black px-4 py-2 text-center text-sm font-medium text-gray-900">
                  Descuento
                </th>
                <th class="border border-gray-300 print:border-black px-4 py-2 text-right text-sm font-medium text-gray-900">
                  Subtotal
                </th>
                <th class="border border-gray-300 print:border-black px-4 py-2 text-right text-sm font-medium text-gray-900">
                  Total
                </th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in sale.items" :key="item.id">
                <td class="border border-gray-300 print:border-black px-4 py-2 text-sm text-gray-900">
                  {{ item.product?.code || 'N/A' }}
                </td>
                <td class="border border-gray-300 print:border-black px-4 py-2 text-sm text-gray-900">
                  {{ item.product?.description || item.product?.name || 'N/A' }}
                </td>
                <td class="border border-gray-300 print:border-black px-4 py-2 text-center text-sm text-gray-900">
                  {{ item.quantity }}
                </td>
                <td class="border border-gray-300 print:border-black px-4 py-2 text-right text-sm text-gray-900">
                  {{ formatCurrency(item.unit_price) }}
                </td>
                <td class="border border-gray-300 print:border-black px-4 py-2 text-center text-sm text-gray-900">
                  {{ item.discount || 0 }}%
                </td>
                <td class="border border-gray-300 print:border-black px-4 py-2 text-right text-sm text-gray-900">
                  {{ formatCurrency(item.subtotal) }}
                </td>
                <td class="border border-gray-300 print:border-black px-4 py-2 text-right text-sm font-medium text-gray-900">
                  {{ formatCurrency(item.total) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Totals -->
      <div class="flex justify-end mb-6 print:mb-4">
        <div class="w-80 print:w-64">
          <div class="bg-gray-50 p-4 rounded-lg print:bg-transparent print:p-0">
            <div class="space-y-2">
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Subtotal:</span>
                <span class="font-medium">{{ formatCurrency(sale.subtotal) }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Descuento Total:</span>
                <span class="font-medium text-red-600">-{{ formatCurrency(sale.total_discount) }}</span>
              </div>
              <div class="border-t border-gray-300 print:border-black pt-2">
                <div class="flex justify-between text-lg font-bold">
                  <span>TOTAL:</span>
                  <span>{{ formatCurrency(sale.total) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Notes -->
      <div v-if="sale.notes" class="mb-6 print:mb-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-2 print:text-base">OBSERVACIONES</h3>
        <div class="bg-gray-50 p-4 rounded-lg print:bg-transparent print:p-0 print:border print:border-gray-300">
          <p class="text-sm text-gray-700">{{ sale.notes }}</p>
        </div>
      </div>

      <!-- Footer -->
      <div class="mt-8 print:mt-6 text-center text-xs text-gray-500">
        <p>Este documento fue generado automáticamente por el Sistema de Gestión Farmacéutica</p>
        <p>Fecha de impresión: {{ formatDate(new Date()) }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import { Printer, ArrowLeft } from 'lucide-vue-next'

const props = defineProps({
  sale: {
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

const getStatusText = (status) => {
  const texts = {
    draft: 'Borrador',
    pending: 'Pendiente',
    completed: 'Completada',
    complete: 'Completada',
    cancelled: 'Cancelada',
    canceled: 'Cancelada'
  }
  return texts[status] || status
}

const getStatusClass = (status) => {
  const classes = {
    draft: 'text-gray-600 font-medium',
    pending: 'text-yellow-600 font-medium',
    completed: 'text-green-600 font-medium',
    complete: 'text-green-600 font-medium',
    cancelled: 'text-red-600 font-medium',
    canceled: 'text-red-600 font-medium'
  }
  return classes[status] || 'text-gray-600'
}

const getPaymentMethodText = (method) => {
  const texts = {
    cash: 'Efectivo',
    credit: 'Crédito',
    transfer: 'Transferencia'
  }
  return texts[method] || method
}

const printDocument = () => {
  window.print()
}

const goBack = () => {
  router.visit(`/ventas/${props.sale.id}`)
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
  
  .print\\:border-black {
    border-color: #000 !important;
  }
  
  .print\\:w-64 {
    width: 16rem !important;
  }
  
  .print\\:mt-6 {
    margin-top: 1.5rem !important;
  }
}
</style>
