<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Barra superior - solo pantalla -->
    <div class="print:hidden p-4 bg-gray-100 border-b flex justify-between items-center">
      <h1 class="text-lg font-semibold text-gray-900">Vista Previa - Nota de Pago</h1>
      <div class="flex gap-2">
        <button @click="printDocument" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 flex items-center gap-2">
          <Printer class="h-4 w-4" /> Imprimir
        </button>
        <button @click="goBack" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 flex items-center gap-2">
          <ArrowLeft class="h-4 w-4" /> Volver
        </button>
      </div>
    </div>

    <!-- Ticket -->
    <div class="flex justify-center py-6 print:py-0">
      <div id="print-content" class="ticket">

        <p class="c b">FARMACIA PANDO</p>
        <p class="c">NOTA DE PAGO</p>
        <p class="c sm">{{ payment.payment_number }} | {{ formatDate(payment.payment_date) }}</p>
        <div class="ln"></div>

        <p class="b">{{ payment.client?.business_name || 'N/A' }}</p>
        <p v-if="payment.client?.trade_name" class="sm">{{ payment.client.trade_name }}</p>
        <p v-if="payment.client?.tax_id" class="sm">NIT: {{ payment.client.tax_id }}</p>
        <p v-if="payment.client?.phone" class="sm">Tel: {{ payment.client.phone }}</p>
        <div class="ln"></div>

        <p class="sm">{{ payment.status_label }} | {{ payment.payment_method_label }}<span v-if="payment.payment_reference"> | Ref: {{ payment.payment_reference }}</span></p>
        <p v-if="payment.invoice" class="sm">Factura: {{ payment.invoice.invoice_number }}</p>
        <div class="ln"></div>

        <p class="c sm">Recibí la suma de:</p>
        <p class="c sm it">{{ amountInWords(payment.amount) }}</p>
        <p class="c b lg">{{ formatCurrency(payment.amount) }}</p>
        <div class="ln"></div>

        <p class="c sm">{{ payment.payment_number }} — Gracias por su pago</p>

      </div>
    </div>
  </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import { Printer, ArrowLeft } from 'lucide-vue-next'

const props = defineProps({
  payment: { type: Object, required: true }
})

const formatCurrency = (amount) =>
  new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB', minimumFractionDigits: 2 }).format(amount || 0)

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('es-BO', { year: 'numeric', month: '2-digit', day: '2-digit' })
}

const amountInWords = (amount) => {
  if (!amount || amount === 0) return 'Cero bolivianos'
  const ones = ['','uno','dos','tres','cuatro','cinco','seis','siete','ocho','nueve']
  const tens = ['','','veinte','treinta','cuarenta','cincuenta','sesenta','setenta','ochenta','noventa']
  const teens = ['diez','once','doce','trece','catorce','quince','dieciséis','diecisiete','dieciocho','diecinueve']
  const hundreds = ['','ciento','doscientos','trescientos','cuatrocientos','quinientos','seiscientos','setecientos','ochocientos','novecientos']
  const num = Math.floor(amount)
  const cents = Math.round((amount - num) * 100)
  const convert = (n) => {
    if (n === 0) return ''
    if (n < 10) return ones[n]
    if (n < 20) return teens[n - 10]
    if (n < 100) { const t = Math.floor(n/10), o = n%10; return o === 0 ? tens[t] : tens[t]+' y '+ones[o] }
    if (n < 1000) { const h = Math.floor(n/100), r = n%100; return h===1&&r===0 ? 'cien' : hundreds[h]+(r>0?' '+convert(r):'') }
    if (n < 1000000) { const t = Math.floor(n/1000), r = n%1000; return (t===1?'mil':convert(t)+' mil')+(r>0?' '+convert(r):'') }
    return 'número muy grande'
  }
  let result = convert(num) || 'cero'
  if (num === 1) result = 'un'
  result = result.charAt(0).toUpperCase() + result.slice(1)
  if (cents > 0) result += ' con ' + convert(cents) + '/100'
  return result + ' bolivianos'
}

const printDocument = () => window.print()
const goBack = () => router.visit(`/cuentas-por-cobrar/pagos/${props.payment.id}`)
</script>

<style scoped>
.ticket {
  font-family: 'Courier New', Courier, monospace;
  font-size: 12px;
  width: 300px;
  background: white;
  padding: 10px 8px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}
.ticket p { margin: 1px 0; line-height: 1.3; word-break: break-word; }
.ln  { border-top: 1px dashed #777; margin: 5px 0; }
.c   { text-align: center; }
.b   { font-weight: bold; }
.it  { font-style: italic; }
.sm  { font-size: 10px; }
.lg  { font-size: 15px; }

@media print {
  @page { margin: 0; size: 80mm auto; }
  body  { margin: 0; background: white; }
  .ticket {
    width: 100%;
    box-shadow: none;
    padding: 2px 4px;
    font-size: 11px;
  }
  .lg { font-size: 14px; }
  .sm { font-size: 9px; }
}
</style>
