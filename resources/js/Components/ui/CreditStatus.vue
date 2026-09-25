<template>
  <div v-if="client" class="rounded-lg border p-3 text-sm" :class="exceeded ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-gray-50'">
    <template v-if="limit > 0">
      <div class="grid grid-cols-3 gap-2">
        <div>
          <div class="text-xs text-gray-500">Límite de crédito</div>
          <div class="font-medium text-gray-900">{{ money(limit) }}</div>
        </div>
        <div>
          <div class="text-xs text-gray-500">Deuda pendiente</div>
          <div class="font-medium text-gray-900">{{ money(pending) }}</div>
        </div>
        <div>
          <div class="text-xs text-gray-500">Disponible</div>
          <div class="font-semibold" :class="available > 0 ? 'text-green-700' : 'text-red-600'">{{ money(available) }}</div>
        </div>
      </div>
      <p v-if="exceeded" class="mt-2 font-medium text-red-700">
        ⛔ El total de la preventa ({{ money(total) }}) supera el crédito disponible del cliente.
      </p>
    </template>
    <p v-else class="text-gray-600">Este cliente no tiene límite de crédito establecido.</p>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  client: { type: Object, default: null },
  total:  { type: Number, default: 0 },
})

const limit     = computed(() => Number(props.client?.credit_limit) || 0)
const pending   = computed(() => Number(props.client?.pending_balance) || 0)
const available = computed(() => Math.max(0, limit.value - pending.value))
const exceeded  = computed(() => creditExceeded(props.client, props.total))

const money = (value) => 'Bs ' + Number(value || 0).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
</script>

<script>
// true si el cliente tiene límite de crédito y el total lo supera
export const creditExceeded = (client, total) => {
  const limit = Number(client?.credit_limit) || 0
  if (limit <= 0) return false
  const available = limit - (Number(client?.pending_balance) || 0)
  return Number(total || 0) > available + 0.001
}
</script>
