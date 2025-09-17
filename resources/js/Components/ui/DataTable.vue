<template>
  <div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <!-- Header with Search and Actions -->
    <div class="p-6 border-b border-gray-200">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
        <div>
          <h3 v-if="title" class="text-lg font-semibold text-gray-900">{{ title }}</h3>
          <p v-if="subtitle" class="text-sm text-gray-500 mt-1">{{ subtitle }}</p>
        </div>
        <div class="flex items-center space-x-3">
          <!-- Search -->
          <div class="relative" v-if="searchable">
            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
            <input
              v-model="searchQuery"
              type="text"
              :placeholder="searchPlaceholder"
              class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm w-64"
            />
          </div>

          <!-- Filter -->
          <button
            v-if="filterable"
            @click="showFilters = !showFilters"
            class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <Filter class="w-4 h-4 mr-2" />
            Filtros
            <ChevronDown class="w-4 h-4 ml-1" :class="{ 'rotate-180': showFilters }" />
          </button>

          <!-- Actions -->
          <slot name="actions" />
        </div>
      </div>

      <!-- Filters Panel -->
      <div v-if="showFilters && filterable" class="mt-4 p-4 bg-gray-50 rounded-lg">
        <slot name="filters" :filters="filters" :updateFilter="updateFilter" />
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <!-- Header -->
        <thead class="bg-gray-50">
          <tr>
            <!-- Select All Checkbox -->
            <th v-if="selectable" class="px-6 py-3 text-left">
              <input
                type="checkbox"
                :checked="allSelected"
                @change="toggleSelectAll"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
            </th>
            <!-- Column Headers -->
            <th
              v-for="column in columns"
              :key="column.key"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
              @click="sort(column.key)"
              :class="{ 'bg-gray-100': sortKey === column.key }"
            >
              <div class="flex items-center space-x-1">
                <span>{{ column.title }}</span>
                <div class="flex flex-col" v-if="column.sortable !== false">
                  <ChevronUp
                    class="w-3 h-3 text-gray-400"
                    :class="{ 'text-blue-500': sortKey === column.key && sortOrder === 'asc' }"
                  />
                  <ChevronDown
                    class="w-3 h-3 text-gray-400 -mt-1"
                    :class="{ 'text-blue-500': sortKey === column.key && sortOrder === 'desc' }"
                  />
                </div>
              </div>
            </th>
            <!-- Actions Column -->
            <th v-if="$slots.actions" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
              Acciones
            </th>
          </tr>
        </thead>

        <!-- Body -->
        <tbody class="bg-white divide-y divide-gray-200">
          <!-- Loading State -->
          <tr v-if="loading">
            <td :colspan="columnCount" class="px-6 py-12 text-center">
              <div class="flex items-center justify-center">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                <span class="ml-3 text-gray-500">Cargando...</span>
              </div>
            </td>
          </tr>

          <!-- Empty State -->
          <tr v-else-if="paginatedData.length === 0">
            <td :colspan="columnCount" class="px-6 py-12 text-center">
              <div class="text-gray-500">
                <slot name="empty">
                  <div class="flex flex-col items-center">
                    <Package class="w-12 h-12 text-gray-300 mb-4" />
                    <h3 class="text-sm font-medium text-gray-900 mb-1">No hay datos</h3>
                    <p class="text-sm text-gray-500">No se encontraron registros para mostrar.</p>
                  </div>
                </slot>
              </div>
            </td>
          </tr>

          <!-- Data Rows -->
          <tr
            v-else
            v-for="(item, index) in paginatedData"
            :key="getItemKey(item, index)"
            class="hover:bg-gray-50 transition-colors"
            :class="{ 'bg-blue-50': selectedItems.includes(getItemKey(item, index)) }"
          >
            <!-- Select Checkbox -->
            <td v-if="selectable" class="px-6 py-4">
              <input
                type="checkbox"
                :checked="selectedItems.includes(getItemKey(item, index))"
                @change="toggleSelectItem(getItemKey(item, index))"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
            </td>

            <!-- Data Columns -->
            <td
              v-for="column in columns"
              :key="column.key"
              class="px-6 py-4 whitespace-nowrap text-sm"
              :class="column.class || 'text-gray-900'"
            >
              <slot
                :name="`cell(${column.key})`"
                :item="item"
                :value="getNestedValue(item, column.key)"
                :index="index"
              >
                {{ formatValue(getNestedValue(item, column.key), column.format) }}
              </slot>
            </td>

            <!-- Actions Column -->
            <td v-if="$slots.actions" class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <slot name="actions" :item="item" :index="index" />
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="paginated && !loading" class="px-6 py-4 border-t border-gray-200">
      <div class="flex items-center justify-between">
        <div class="text-sm text-gray-500">
          Mostrando {{ ((currentPage - 1) * perPage) + 1 }} a {{ Math.min(currentPage * perPage, totalItems) }} de {{ totalItems }} resultados
        </div>
        <div class="flex items-center space-x-2">
          <select
            v-model="perPage"
            class="border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option v-for="size in pageSizes" :key="size" :value="size">{{ size }} por página</option>
          </select>

          <div class="flex items-center space-x-1">
            <button
              @click="goToPage(1)"
              :disabled="currentPage === 1"
              class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <ChevronsLeft class="w-4 h-4" />
            </button>
            <button
              @click="goToPage(currentPage - 1)"
              :disabled="currentPage === 1"
              class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <ChevronLeft class="w-4 h-4" />
            </button>

            <span class="px-3 py-1 text-sm">{{ currentPage }} de {{ totalPages }}</span>

            <button
              @click="goToPage(currentPage + 1)"
              :disabled="currentPage === totalPages"
              class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <ChevronRight class="w-4 h-4" />
            </button>
            <button
              @click="goToPage(totalPages)"
              :disabled="currentPage === totalPages"
              class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <ChevronsRight class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import {
  Search,
  Filter,
  ChevronDown,
  ChevronUp,
  ChevronLeft,
  ChevronRight,
  ChevronsLeft,
  ChevronsRight,
  Package
} from 'lucide-vue-next'

const props = defineProps({
  title: String,
  subtitle: String,
  data: {
    type: Array,
    default: () => []
  },
  columns: {
    type: Array,
    required: true
  },
  loading: {
    type: Boolean,
    default: false
  },
  searchable: {
    type: Boolean,
    default: true
  },
  searchPlaceholder: {
    type: String,
    default: 'Buscar...'
  },
  filterable: {
    type: Boolean,
    default: false
  },
  selectable: {
    type: Boolean,
    default: false
  },
  paginated: {
    type: Boolean,
    default: true
  },
  itemKey: {
    type: String,
    default: 'id'
  }
})

const emit = defineEmits(['selection-changed', 'sort-changed', 'page-changed'])

// Search
const searchQuery = ref('')

// Sorting
const sortKey = ref('')
const sortOrder = ref('asc')

// Filters
const showFilters = ref(false)
const filters = ref({})

// Selection
const selectedItems = ref([])

// Pagination
const currentPage = ref(1)
const perPage = ref(10)
const pageSizes = [5, 10, 25, 50, 100]

// Computed
const columnCount = computed(() => {
  let count = props.columns.length
  if (props.selectable) count++
  if (props.$slots?.actions) count++
  return count
})

const filteredData = computed(() => {
  let data = [...props.data]

  // Apply search
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    data = data.filter(item =>
      props.columns.some(column =>
        String(getNestedValue(item, column.key) || '').toLowerCase().includes(query)
      )
    )
  }

  // Apply filters
  Object.entries(filters.value).forEach(([key, value]) => {
    if (value !== null && value !== '') {
      data = data.filter(item => {
        const itemValue = getNestedValue(item, key)
        return String(itemValue).toLowerCase().includes(String(value).toLowerCase())
      })
    }
  })

  // Apply sorting
  if (sortKey.value) {
    data.sort((a, b) => {
      const aVal = getNestedValue(a, sortKey.value)
      const bVal = getNestedValue(b, sortKey.value)

      if (aVal < bVal) return sortOrder.value === 'asc' ? -1 : 1
      if (aVal > bVal) return sortOrder.value === 'asc' ? 1 : -1
      return 0
    })
  }

  return data
})

const totalItems = computed(() => filteredData.value.length)
const totalPages = computed(() => Math.ceil(totalItems.value / perPage.value))

const paginatedData = computed(() => {
  if (!props.paginated) return filteredData.value

  const start = (currentPage.value - 1) * perPage.value
  const end = start + perPage.value
  return filteredData.value.slice(start, end)
})

const allSelected = computed(() => {
  if (paginatedData.value.length === 0) return false
  return paginatedData.value.every((item, index) =>
    selectedItems.value.includes(getItemKey(item, index))
  )
})

// Methods
const getNestedValue = (obj, path) => {
  return path.split('.').reduce((current, key) => current?.[key], obj)
}

const getItemKey = (item, index) => {
  return getNestedValue(item, props.itemKey) || index
}

const formatValue = (value, format) => {
  if (value === null || value === undefined) return '-'

  if (format === 'currency') {
    return new Intl.NumberFormat('es-DO', {
      style: 'currency',
      currency: 'DOP'
    }).format(value)
  }

  if (format === 'date') {
    return new Date(value).toLocaleDateString('es-DO')
  }

  if (format === 'datetime') {
    return new Date(value).toLocaleString('es-DO')
  }

  return value
}

const sort = (key) => {
  if (sortKey.value === key) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortKey.value = key
    sortOrder.value = 'asc'
  }

  emit('sort-changed', { key: sortKey.value, order: sortOrder.value })
}

const updateFilter = (key, value) => {
  filters.value[key] = value
  currentPage.value = 1
}

const toggleSelectAll = () => {
  if (allSelected.value) {
    selectedItems.value = selectedItems.value.filter(id =>
      !paginatedData.value.some((item, index) => getItemKey(item, index) === id)
    )
  } else {
    paginatedData.value.forEach((item, index) => {
      const key = getItemKey(item, index)
      if (!selectedItems.value.includes(key)) {
        selectedItems.value.push(key)
      }
    })
  }

  emit('selection-changed', selectedItems.value)
}

const toggleSelectItem = (key) => {
  const index = selectedItems.value.indexOf(key)
  if (index > -1) {
    selectedItems.value.splice(index, 1)
  } else {
    selectedItems.value.push(key)
  }

  emit('selection-changed', selectedItems.value)
}

const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
    emit('page-changed', page)
  }
}

// Watchers
watch(searchQuery, () => {
  currentPage.value = 1
})

watch(perPage, () => {
  currentPage.value = 1
})
</script>