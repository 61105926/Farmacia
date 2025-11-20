<template>
  <div class="min-h-screen bg-white">
    <!-- Print Header -->
    <div class="print:hidden bg-gray-100 p-4 border-b">
      <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-gray-900">Imprimir Preventa</h1>
        <div class="flex gap-2">
          <Button @click="window.print()" class="bg-blue-600 hover:bg-blue-700">
            <Printer class="h-4 w-4 mr-2" />
            Imprimir
          </Button>
          <Button variant="outline" @click="window.close()">
            <X class="h-4 w-4 mr-2" />
            Cerrar
          </Button>
        </div>
      </div>
    </div>

    <!-- Print Content -->
    <div class="p-8 print:p-0">
      <!-- Company Header -->
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">FARMACIA PANDO</h1>
        <p class="text-lg text-gray-600">Sistema de Gestión Farmacéutica</p>
        <p class="text-sm text-gray-500">La Paz, Bolivia</p>
      </div>

      <!-- Document Title -->
      <div class="text-center mb-8">
        <h2 class="text-2xl font-bold text-gray-900">PREVENTA</h2>
        <p class="text-lg text-gray-600">Código: {{ presale.code }}</p>
      </div>

      <!-- Document Info -->
      <div class="grid grid-cols-2 gap-8 mb-8">
        <!-- Client Info -->
        <div>
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Información del Cliente</h3>
          <div class="space-y-2">
            <p><span class="font-medium">Razón Social:</span> {{ presale.client?.business_name || 'N/A' }}</p>
            <p><span class="font-medium">Nombre Comercial:</span> {{ presale.client?.trade_name || 'N/A' }}</p>
            <p><span class="font-medium">Email:</span> {{ presale.client?.email || 'N/A' }}</p>
            <p><span class="font-medium">Teléfono:</span> {{ presale.client?.phone || 'N/A' }}</p>
            <p><span class="font-medium">Dirección:</span> {{ presale.client?.address || 'N/A' }}</p>
          </div>
        </div>

        <!-- Document Details -->
        <div>
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Detalles del Documento</h3>
          <div class="space-y-2">
            <p><span class="font-medium">Código:</span> {{ presale.code }}</p>
            <p><span class="font-medium">Fecha:</span> {{ formatDate(presale.created_at) }}</p>
            <p><span class="font-medium">Estado:</span> {{ getStatusText(presale.status) }}</p>
            <p><span class="font-medium">Vendedor:</span> {{ presale.salesperson?.name || 'N/A' }}</p>
            <p v-if="presale.delivery_date"><span class="font-medium">Fecha de Entrega:</span> {{ formatDate(presale.delivery_date) }}</p>
          </div>
        </div>
      </div>

      <!-- Items Table -->
      <div class="mb-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Productos</h3>
        <table class="w-full border-collapse border border-gray-300">
          <thead>
            <tr class="bg-gray-100">
              <th class="border border-gray-300 px-4 py-2 text-left">Código</th>
              <th class="border border-gray-300 px-4 py-2 text-left">Producto</th>
              <th class="border border-gray-300 px-4 py-2 text-center">Cantidad</th>
              <th class="border border-gray-300 px-4 py-2 text-right">Precio Unit.</th>
              <th class="border border-gray-300 px-4 py-2 text-right">Descuento</th>
              <th class="border border-gray-300 px-4 py-2 text-right">Total</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in presale.items" :key="item.id">
              <td class="border border-gray-300 px-4 py-2">{{ item.product?.code || 'N/A' }}</td>
              <td class="border border-gray-300 px-4 py-2">
                {{ item.product?.name || 'N/A' }}
                <span v-if="item.product?.description"> - {{ item.product.description }}</span>
              </td>
              <td class="border border-gray-300 px-4 py-2 text-center">{{ item.quantity }}</td>
              <td class="border border-gray-300 px-4 py-2 text-right">{{ formatCurrency(item.unit_price) }}</td>
              <td class="border border-gray-300 px-4 py-2 text-right">{{ formatCurrency(item.discount_amount) }}</td>
              <td class="border border-gray-300 px-4 py-2 text-right">{{ formatCurrency(item.total) }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Totals -->
      <div class="grid grid-cols-2 gap-8 mb-8">
        <div>
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Observaciones</h3>
          <div class="border border-gray-300 p-4 min-h-[100px]">
            <p>{{ presale.notes || 'Sin observaciones' }}</p>
          </div>
        </div>

        <div>
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Totales</h3>
          <div class="space-y-2">
            <div class="flex justify-between">
              <span>Subtotal:</span>
              <span>{{ formatCurrency(presale.subtotal) }}</span>
            </div>
            <div class="flex justify-between">
              <span>Descuento Total:</span>
              <span>{{ formatCurrency(presale.total_discount) }}</span>
            </div>
            <div class="flex justify-between font-bold text-lg border-t pt-2">
              <span>Total:</span>
              <span>{{ formatCurrency(presale.total) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="mt-16 text-center text-sm text-gray-500">
        <p>Documento generado el {{ formatDateTime(new Date()) }}</p>
        <p>Farmacia Pando - Sistema de Gestión Farmacéutica</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Printer, X } from 'lucide-vue-next'
import Button from '@/Components/ui/Button.vue'

const props = defineProps({
  presale: {
    type: Object,
    required: true
  }
})

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
  return new Date(date).toLocaleDateString('es-BO')
}

const formatDateTime = (date) => {
  return new Date(date).toLocaleString('es-BO')
}

const getStatusText = (status) => {
  const texts = {
    draft: 'Borrador',
    confirmed: 'Confirmada',
    converted: 'Convertida',
    cancelled: 'Cancelada'
  }
  return texts[status] || status
}
</script>

<style scoped>
@media print {
  .print\\:hidden {
    display: none !important;
  }
  
  .print\\:p-0 {
    padding: 0 !important;
  }
  
  body {
    -webkit-print-color-adjust: exact;
    color-adjust: exact;
  }
}
</style>
