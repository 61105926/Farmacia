<template>
  <AdminLayout>
    <div class="p-6">
      <div class="mb-6 flex items-center justify-between">
        <div>
          <Link href="/lotes" class="text-sm text-primary-600 hover:underline mb-1 block">← Todos los lotes</Link>
          <h1 class="text-2xl font-bold text-gray-900">{{ product.name }}</h1>
          <p class="text-sm text-gray-500">{{ product.code }} — Lotes en orden FIFO</p>
        </div>
        <Link
          href="/inventario/crear"
          class="px-4 py-2 bg-primary-700 text-white rounded-md hover:bg-primary-800 transition-colors"
        >
          + Nuevo Ingreso
        </Link>
      </div>

      <!-- Resumen del producto -->
      <div class="grid grid-cols-3 gap-4 mb-6">
        <Card>
          <CardContent class="p-4">
            <p class="text-sm text-gray-500">Stock Total</p>
            <p class="text-2xl font-bold text-gray-900">{{ totalStock }}</p>
          </CardContent>
        </Card>
        <Card>
          <CardContent class="p-4">
            <p class="text-sm text-gray-500">Lotes Activos</p>
            <p class="text-2xl font-bold text-green-600">{{ activeBatches.length }}</p>
          </CardContent>
        </Card>
        <Card>
          <CardContent class="p-4">
            <p class="text-sm text-gray-500">Próximo en Usar (FIFO)</p>
            <p class="text-lg font-bold text-primary-700">{{ nextBatch?.batch_number || '—' }}</p>
          </CardContent>
        </Card>
      </div>

      <!-- Lista de lotes -->
      <Card>
        <CardContent class="p-0">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr>
                <th class="text-left px-4 py-3 font-semibold text-gray-600">Orden FIFO</th>
                <th class="text-left px-4 py-3 font-semibold text-gray-600">N° Lote</th>
                <th class="text-left px-4 py-3 font-semibold text-gray-600">Proveedor</th>
                <th class="text-center px-4 py-3 font-semibold text-gray-600">Fecha Ingreso</th>
                <th class="text-center px-4 py-3 font-semibold text-gray-600">Vencimiento</th>
                <th class="text-center px-4 py-3 font-semibold text-gray-600">Inicial</th>
                <th class="text-center px-4 py-3 font-semibold text-gray-600">Restante</th>
                <th class="text-center px-4 py-3 font-semibold text-gray-600">Uso %</th>
                <th class="text-center px-4 py-3 font-semibold text-gray-600">Estado</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-if="batches.length === 0">
                <td colspan="9" class="text-center py-12 text-gray-400">Sin lotes registrados</td>
              </tr>
              <tr
                v-for="(batch, idx) in batches"
                :key="batch.id"
                class="hover:bg-gray-50 transition-colors"
                :class="{
                  'bg-primary-50 border-l-4 border-l-primary-500': idx === 0 && batch.status === 'active',
                  'bg-orange-50': isExpiringSoon(batch.expiry_date),
                  'bg-red-50': isExpired(batch.expiry_date),
                }"
              >
                <td class="px-4 py-3 text-center">
                  <span v-if="batch.status === 'active'" class="w-6 h-6 rounded-full bg-primary-600 text-white text-xs flex items-center justify-center font-bold mx-auto">
                    {{ activeBatchIndex(batch.id) + 1 }}
                  </span>
                  <span v-else class="text-gray-300">—</span>
                </td>
                <td class="px-4 py-3 font-mono font-semibold text-gray-800">
                  {{ batch.batch_number }}
                  <span v-if="idx === 0 && batch.status === 'active'" class="ml-2 text-xs bg-primary-100 text-primary-700 px-1 py-0.5 rounded">Próximo</span>
                </td>
                <td class="px-4 py-3 text-gray-600">{{ batch.supplier || '—' }}</td>
                <td class="px-4 py-3 text-center text-gray-600">{{ formatDate(batch.entry_date) }}</td>
                <td class="px-4 py-3 text-center">
                  <span v-if="!batch.expiry_date" class="text-gray-400">—</span>
                  <span v-else :class="expiryClass(batch.expiry_date)">{{ formatDate(batch.expiry_date) }}</span>
                </td>
                <td class="px-4 py-3 text-center text-gray-600">{{ batch.initial_quantity }}</td>
                <td class="px-4 py-3 text-center font-bold" :class="batch.remaining_quantity === 0 ? 'text-gray-300' : 'text-gray-900'">
                  {{ batch.remaining_quantity }}
                </td>
                <td class="px-4 py-3 text-center">
                  <div class="w-full bg-gray-200 rounded-full h-2">
                    <div
                      class="h-2 rounded-full"
                      :class="usageColor(batch)"
                      :style="`width:${usagePct(batch)}%`"
                    ></div>
                  </div>
                  <span class="text-xs text-gray-500">{{ usagePct(batch) }}%</span>
                </td>
                <td class="px-4 py-3 text-center">
                  <span class="px-2 py-1 rounded-full text-xs font-semibold" :class="statusClass(batch.status)">
                    {{ statusLabel(batch.status) }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </CardContent>
      </Card>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardContent } from '@/Components/ui'

const props = defineProps({
  product: { type: Object, required: true },
  batches: { type: Array,  default: () => [] },
})

const activeBatches = computed(() => props.batches.filter(b => b.status === 'active' && b.remaining_quantity > 0))
const nextBatch     = computed(() => activeBatches.value[0])
const totalStock    = computed(() => activeBatches.value.reduce((s, b) => s + b.remaining_quantity, 0))

function activeBatchIndex(id) {
  return activeBatches.value.findIndex(b => b.id === id)
}

function formatDate(d) {
  if (!d) return '—'
  try { return new Date(d).toLocaleDateString('es-BO') } catch { return d }
}

function isExpiringSoon(expiry) {
  if (!expiry) return false
  const diff = (new Date(expiry) - new Date()) / 86400000
  return diff > 0 && diff <= 30
}

function isExpired(expiry) {
  return expiry && new Date(expiry) < new Date()
}

function expiryClass(expiry) {
  if (isExpired(expiry)) return 'text-red-600 font-semibold'
  if (isExpiringSoon(expiry)) return 'text-orange-600 font-semibold'
  return 'text-gray-600'
}

function usagePct(batch) {
  if (!batch.initial_quantity) return 0
  return Math.round((batch.remaining_quantity / batch.initial_quantity) * 100)
}

function usageColor(batch) {
  const pct = usagePct(batch)
  if (pct > 50) return 'bg-green-500'
  if (pct > 20) return 'bg-orange-400'
  return 'bg-red-400'
}

function statusClass(status) {
  return { active: 'bg-green-100 text-green-700', depleted: 'bg-gray-100 text-gray-500', expired: 'bg-red-100 text-red-600' }[status] || 'bg-gray-100 text-gray-500'
}

function statusLabel(status) {
  return { active: 'Activo', depleted: 'Agotado', expired: 'Vencido' }[status] || status
}
</script>
