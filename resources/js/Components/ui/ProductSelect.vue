<template>
  <div class="relative" ref="container">
    <!-- Input visible: muestra el producto seleccionado o permite buscar -->
    <div
      class="w-full flex items-center border rounded-md bg-white cursor-pointer"
      :class="[
        error ? 'border-red-500' : 'border-gray-300',
        disabled ? 'bg-gray-100 cursor-not-allowed' : 'hover:border-primary-400'
      ]"
      @click="!disabled && openDropdown()"
    >
      <input
        ref="searchInput"
        v-model="search"
        :placeholder="selectedLabel || placeholder"
        :disabled="disabled"
        class="flex-1 px-3 py-2 text-sm bg-transparent outline-none rounded-md"
        :class="{ 'text-gray-400': !modelValue && !search, 'text-gray-900': modelValue || search }"
        @input="open = true"
        @focus="open = true"
        @keydown.escape="close"
        @keydown.enter.prevent="selectHighlighted"
        @keydown.arrow-down.prevent="highlightNext"
        @keydown.arrow-up.prevent="highlightPrev"
      />
      <button
        v-if="modelValue && !disabled"
        type="button"
        @click.stop="clear"
        class="px-2 text-gray-400 hover:text-gray-600"
        tabindex="-1"
      >✕</button>
      <span class="px-2 text-gray-400 text-xs">▼</span>
    </div>

    <!-- Dropdown -->
    <div
      v-if="open && !disabled"
      class="absolute z-50 min-w-full w-max max-w-xl mt-1 bg-white border border-gray-200 rounded-md shadow-lg max-h-64 overflow-y-auto"
    >
      <div v-if="filtered.length === 0" class="px-3 py-3 text-sm text-gray-400 text-center">
        Sin resultados
      </div>
      <button
        v-for="(product, idx) in filtered"
        :key="product.id"
        type="button"
        class="w-full text-left px-3 py-2 text-sm transition-colors flex items-start gap-2"
        :class="{
          'bg-primary-100': modelValue == product.id,
          'bg-gray-100': highlighted === idx && modelValue != product.id,
          'opacity-50 cursor-not-allowed': isBlocked(product),
          'hover:bg-primary-50': !isBlocked(product),
        }"
        @mousedown.prevent="!isBlocked(product) && select(product)"
        @mouseover="highlighted = idx"
      >
        <div class="flex-1 min-w-0">
          <div class="font-medium" :class="isBlocked(product) ? 'text-gray-400' : 'text-gray-900'">{{ product.description || product.name }}</div>
          <div v-if="product.description" class="text-xs text-gray-500">{{ product.name }}</div>
          <div class="text-xs flex gap-2 mt-0.5 flex-wrap">
            <span v-if="isExpired(product)" class="text-red-600 font-semibold">⛔ Vencido</span>
            <span v-else-if="product.expiry_date" class="text-gray-400">Vence: {{ formatExpiry(product.expiry_date) }}</span>
            <span v-if="product.sku" class="text-gray-500">Lote: {{ product.sku }}</span>
            <span v-else-if="product.code" class="text-gray-500">{{ product.code }}</span>
            <span v-if="product.stock_quantity !== undefined">
              · Stock:
              <span :class="product.stock_quantity <= 0 ? 'text-red-600 font-semibold' : 'text-green-600'">
                {{ product.stock_quantity }}
              </span>
              <span v-if="product.stock_quantity <= 0" class="text-red-600 font-semibold"> ⛔ Sin stock</span>
            </span>
          </div>
        </div>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
  modelValue: { default: '' },
  products:   { type: Array, default: () => [] },
  placeholder:{ type: String, default: 'Buscar producto...' },
  disabled:   { type: Boolean, default: false },
  error:      { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue', 'change'])

const isExpired = (product) => {
  if (!product.expiry_date) return false
  const expiry = new Date(product.expiry_date + 'T00:00:00')
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  return expiry < today
}

const isBlocked = (product) => {
  return isExpired(product) || (product.stock_quantity !== undefined && product.stock_quantity <= 0)
}

const formatExpiry = (date) => {
  if (!date) return ''
  return new Date(date + 'T00:00:00').toLocaleDateString('es-BO', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

const search      = ref('')
const open        = ref(false)
const highlighted = ref(0)
const container   = ref(null)
const searchInput = ref(null)

const selectedProduct = computed(() => props.products.find(p => p.id == props.modelValue))

const selectedLabel = computed(() => {
  if (!selectedProduct.value) return ''
  const p = selectedProduct.value
  const main = p.description || p.name || ''
  const sub  = p.description && p.name && p.description !== p.name ? ` · ${p.name}` : ''
  const lote = p.sku ? ` (Lote: ${p.sku})` : (p.code ? ` (${p.code})` : '')
  return main + sub + lote
})

const filtered = computed(() => {
  const q = search.value.toLowerCase().trim()
  if (!q) return props.products
  return props.products.filter(p => {
    const name = (p.name || p.description || '').toLowerCase()
    const code = (p.code || '').toLowerCase()
    const desc = (p.description || '').toLowerCase()
    return name.includes(q) || code.includes(q) || desc.includes(q)
  })
})

function openDropdown() {
  open.value = true
  search.value = ''
  highlighted.value = 0
  searchInput.value?.focus()
}

function close() {
  open.value = false
  // Si no hay selección y hay búsqueda, limpiar
  if (!props.modelValue) search.value = ''
  else search.value = ''
}

function select(product) {
  emit('update:modelValue', product.id)
  emit('change', product)
  search.value = ''
  open.value = false
}

function clear() {
  emit('update:modelValue', '')
  emit('change', null)
  search.value = ''
}

function selectHighlighted() {
  const item = filtered.value[highlighted.value]
  if (item) select(item)
}

function highlightNext() {
  if (highlighted.value < filtered.value.length - 1) highlighted.value++
}

function highlightPrev() {
  if (highlighted.value > 0) highlighted.value--
}

// Resetear búsqueda al cambiar selección externa
watch(() => props.modelValue, (val) => {
  if (!val) search.value = ''
})

// Cerrar al hacer click fuera
function onClickOutside(e) {
  if (container.value && !container.value.contains(e.target)) {
    close()
  }
}

onMounted(() => document.addEventListener('mousedown', onClickOutside))
onBeforeUnmount(() => document.removeEventListener('mousedown', onClickOutside))
</script>
