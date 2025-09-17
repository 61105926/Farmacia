<template>
  <AdminLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Gestión de Clientes</h1>
          <p class="text-gray-600 mt-1">Administra las farmacias que son clientes de la distribuidora</p>
        </div>
        <div class="flex items-center space-x-4">
          <Button @click="exportData" variant="outline">
            <Download class="w-4 h-4 mr-2" />
            Exportar
          </Button>
          <Button @click="$inertia.visit(route('pharmacies.create'))">
            <Plus class="w-4 h-4 mr-2" />
            Nueva Farmacia
          </Button>
        </div>
      </div>
    </template>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <Card>
        <CardContent class="p-6">
          <div class="flex items-center">
            <div class="p-2 bg-blue-100 rounded-lg">
              <Store class="h-8 w-8 text-blue-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Total Clientes</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats.total }}</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardContent class="p-6">
          <div class="flex items-center">
            <div class="p-2 bg-green-100 rounded-lg">
              <CheckCircle class="h-8 w-8 text-green-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Activos</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats.activos }}</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardContent class="p-6">
          <div class="flex items-center">
            <div class="p-2 bg-red-100 rounded-lg">
              <XCircle class="h-8 w-8 text-red-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Inactivos</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats.inactivos }}</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardContent class="p-6">
          <div class="flex items-center">
            <div class="p-2 bg-yellow-100 rounded-lg">
              <CreditCard class="h-8 w-8 text-yellow-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Con Crédito</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats.con_credito }}</p>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Filters -->
    <Card class="mb-6">
      <CardContent class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <Input
              v-model="searchForm.search"
              placeholder="Buscar farmacia..."
              @input="handleSearch"
            >
              <template #prefix>
                <Search class="w-4 h-4 text-gray-400" />
              </template>
            </Input>
          </div>
          <div>
            <select
              v-model="searchForm.tipo_cliente"
              @change="handleSearch"
              class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500"
            >
              <option value="">Todos los tipos</option>
              <option value="regular">Regular</option>
              <option value="mayorista">Mayorista</option>
              <option value="preferencial">Preferencial</option>
            </select>
          </div>
          <div>
            <select
              v-model="searchForm.activo"
              @change="handleSearch"
              class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500"
            >
              <option value="">Todos los estados</option>
              <option value="1">Activos</option>
              <option value="0">Inactivos</option>
            </select>
          </div>
          <div>
            <Button @click="clearFilters" variant="outline" class="w-full">
              <RotateCcw class="w-4 h-4 mr-2" />
              Limpiar Filtros
            </Button>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Data Table -->
    <Card class="shadow-lg">
      <CardContent class="p-0">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gradient-to-r from-blue-50 to-indigo-50">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b-2 border-blue-200">
                  <div class="flex items-center space-x-1">
                    <span>Código</span>
                  </div>
                </th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b-2 border-blue-200">
                  <div class="flex items-center space-x-1">
                    <Store class="w-4 h-4" />
                    <span>Farmacia</span>
                  </div>
                </th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b-2 border-blue-200 hidden md:table-cell">
                  Contacto
                </th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b-2 border-blue-200">
                  Tipo
                </th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b-2 border-blue-200 hidden lg:table-cell">
                  <div class="flex items-center space-x-1">
                    <CreditCard class="w-4 h-4" />
                    <span>Límite Crédito</span>
                  </div>
                </th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b-2 border-blue-200 hidden xl:table-cell">
                  <div class="flex items-center space-x-1">
                    <span>📅</span>
                    <span>Días Crédito</span>
                  </div>
                </th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b-2 border-blue-200">
                  Estado
                </th>
                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider border-b-2 border-blue-200">
                  Acciones
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
              <tr v-for="pharmacy in pharmacies.data" :key="pharmacy.id"
                  class="hover:bg-blue-50 transition-colors duration-150 cursor-pointer"
                  @click="viewPharmacy(pharmacy.id)">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="w-3 h-3 rounded-full mr-3"
                         :class="pharmacy.activo ? 'bg-green-400' : 'bg-red-400'"></div>
                    <span class="font-mono text-sm font-semibold text-blue-600 bg-blue-50 px-2 py-1 rounded">
                      {{ pharmacy.codigo_cliente }}
                    </span>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-400 to-purple-500 flex items-center justify-center">
                        <span class="text-white font-bold text-sm">
                          {{ pharmacy.nombre_comercial.charAt(0).toUpperCase() }}
                        </span>
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-semibold text-gray-900">{{ pharmacy.nombre_comercial }}</div>
                      <div class="text-xs text-gray-500">{{ pharmacy.razon_social }}</div>
                      <div class="text-xs text-blue-600 md:hidden">{{ pharmacy.telefono_principal }}</div>
                      <div class="text-xs text-purple-600 xl:hidden mt-1">
                        📅 {{ pharmacy.dias_credito }} {{ pharmacy.dias_credito === 1 ? 'día' : 'días' }} de crédito
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap hidden md:table-cell">
                  <div>
                    <div class="text-sm font-medium text-gray-900 flex items-center">
                      <span class="w-2 h-2 bg-green-400 rounded-full mr-2"></span>
                      {{ pharmacy.contacto_nombre }}
                    </div>
                    <div class="text-sm text-gray-500 flex items-center mt-1">
                      <span class="mr-1">📞</span>
                      {{ pharmacy.telefono_principal }}
                    </div>
                    <div class="text-sm text-gray-500 flex items-center mt-1">
                      <span class="mr-1">✉️</span>
                      {{ pharmacy.email_principal }}
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <Badge
                    :variant="getTipoClienteBadgeVariant(pharmacy.tipo_cliente)"
                    size="sm"
                    class="font-semibold"
                  >
                    {{ getTipoClienteLabel(pharmacy.tipo_cliente) }}
                  </Badge>
                </td>
                <td class="px-6 py-4 whitespace-nowrap hidden lg:table-cell">
                  <div class="text-center">
                    <div class="inline-flex items-center px-3 py-2 rounded-lg bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200">
                      <div class="text-center">
                        <div class="font-bold text-xl text-green-700">
                          ${{ Number(pharmacy.limite_credito).toLocaleString() }}
                        </div>
                        <div class="text-xs text-green-600 font-medium mt-1">
                          Límite de Crédito
                        </div>
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap hidden xl:table-cell">
                  <div class="text-center">
                    <div class="inline-flex items-center px-3 py-2 rounded-lg bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200">
                      <div class="text-center">
                        <div class="font-bold text-xl text-blue-700">
                          {{ pharmacy.dias_credito }}
                        </div>
                        <div class="text-xs text-blue-600 font-medium mt-1">
                          {{ pharmacy.dias_credito === 1 ? 'Día' : 'Días' }}
                        </div>
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex justify-center">
                    <div class="inline-flex items-center px-3 py-2 rounded-full text-sm font-bold"
                         :class="pharmacy.activo
                           ? 'bg-gradient-to-r from-green-100 to-emerald-100 text-green-800 border-2 border-green-300 shadow-sm'
                           : 'bg-gradient-to-r from-red-100 to-pink-100 text-red-800 border-2 border-red-300 shadow-sm'">
                      <div class="w-2 h-2 rounded-full mr-2 animate-pulse"
                           :class="pharmacy.activo ? 'bg-green-500' : 'bg-red-500'"></div>
                      <component :is="pharmacy.activo ? CheckCircle : XCircle" class="w-4 h-4 mr-2" />
                      {{ pharmacy.activo ? 'ACTIVO' : 'INACTIVO' }}
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap" @click.stop>
                  <div class="flex items-center justify-center space-x-1">
                    <Button
                      @click="viewPharmacy(pharmacy.id)"
                      size="sm"
                      variant="ghost"
                      class="text-blue-600 hover:text-blue-800 hover:bg-blue-50"
                      title="Ver detalle"
                    >
                      <Eye class="w-4 h-4" />
                    </Button>
                    <Button
                      @click="editPharmacy(pharmacy.id)"
                      size="sm"
                      variant="ghost"
                      class="text-green-600 hover:text-green-800 hover:bg-green-50"
                      title="Editar"
                    >
                      <Edit class="w-4 h-4" />
                    </Button>
                    <Button
                      @click="deletePharmacy(pharmacy)"
                      size="sm"
                      variant="ghost"
                      class="text-red-600 hover:text-red-800 hover:bg-red-50"
                      title="Eliminar"
                    >
                      <Trash2 class="w-4 h-4" />
                    </Button>
                    <Button
                      @click="toggleStatus(pharmacy)"
                      size="sm"
                      variant="ghost"
                      :class="pharmacy.activo ? 'text-orange-600 hover:text-orange-800 hover:bg-orange-50' : 'text-green-600 hover:text-green-800 hover:bg-green-50'"
                      :title="pharmacy.activo ? 'Desactivar' : 'Activar'"
                    >
                      <component :is="pharmacy.activo ? XCircle : CheckCircle" class="w-4 h-4" />
                    </Button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Empty state -->
          <div v-if="!pharmacies.data || pharmacies.data.length === 0" class="text-center py-12">
            <Store class="mx-auto h-12 w-12 text-gray-400" />
            <h3 class="mt-2 text-sm font-medium text-gray-900">No hay farmacias</h3>
            <p class="mt-1 text-sm text-gray-500">Comienza creando una nueva farmacia cliente.</p>
            <div class="mt-6">
              <Button @click="$inertia.visit(route('pharmacies.create'))" class="inline-flex items-center">
                <Plus class="w-4 h-4 mr-2" />
                Nueva Farmacia
              </Button>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Pagination -->
    <div class="mt-6 flex items-center justify-between">
      <div class="text-sm text-gray-500">
        Mostrando {{ pharmacies.from || 0 }} a {{ pharmacies.to || 0 }} de {{ pharmacies.total || 0 }} resultados
      </div>
      <div v-if="pharmacies.links" class="flex items-center space-x-2">
        <Button
          v-for="link in pharmacies.links"
          :key="link.label"
          @click="link.url ? router.get(link.url) : null"
          :variant="link.active ? 'default' : 'outline'"
          :disabled="!link.url"
          size="sm"
        >
          {{ link.label.replace('&laquo;', '‹').replace('&raquo;', '›') }}
        </Button>
      </div>
    </div>

    <!-- Confirm Modal -->
    <ConfirmModal
      :show="confirmModal.show"
      :title="confirmModal.title"
      :message="confirmModal.message"
      :type="confirmModal.type"
      :confirm-text="confirmModal.confirmText"
      @confirm="handleConfirm"
      @cancel="handleCancel"
      @close="handleCancel"
    />
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardHeader, CardTitle, CardContent, Button, Badge, Input, ConfirmModal } from '@/Components/ui'
import {
  Store,
  CheckCircle,
  XCircle,
  CreditCard,
  Search,
  Plus,
  Download,
  RotateCcw,
  Eye,
  Edit,
  Trash2
} from 'lucide-vue-next'

const props = defineProps({
  pharmacies: Object,
  filters: Object,
  stats: Object,
  sort: Object
})

const searchForm = ref({
  search: props.filters.search || '',
  tipo_cliente: props.filters.tipo_cliente || '',
  activo: props.filters.activo || '',
  zona_reparto: props.filters.zona_reparto || ''
})

// Modal de confirmación
const confirmModal = ref({
  show: false,
  title: '',
  message: '',
  type: 'warning',
  confirmText: 'Confirmar',
  action: null
})


const handleSearch = () => {
  router.get(route('pharmacies.index'), searchForm.value, {
    preserveState: true,
    replace: true
  })
}

const handleSort = (field, direction) => {
  router.get(route('pharmacies.index'), {
    ...searchForm.value,
    sort: field,
    direction: direction
  }, {
    preserveState: true,
    replace: true
  })
}

const clearFilters = () => {
  searchForm.value = {
    search: '',
    tipo_cliente: '',
    activo: '',
    zona_reparto: ''
  }
  handleSearch()
}

const viewPharmacy = (id) => {
  router.get(route('pharmacies.show', id))
}

const editPharmacy = (id) => {
  router.get(route('pharmacies.edit', id))
}

const deletePharmacy = (pharmacy) => {
  confirmModal.value = {
    show: true,
    title: 'Eliminar Farmacia',
    message: `¿Estás seguro de que deseas eliminar la farmacia "${pharmacy.nombre_comercial}"?\n\nEsta acción desactivará permanentemente el cliente y no se podrá deshacer.`,
    type: 'danger',
    confirmText: 'Eliminar',
    action: () => {
      router.delete(route('pharmacies.destroy', pharmacy.id), {
        preserveScroll: true,
        onSuccess: () => {
          window.$notify?.success(
            'Farmacia eliminada',
            `La farmacia "${pharmacy.nombre_comercial}" ha sido eliminada correctamente`
          )
        },
        onError: () => {
          window.$notify?.error(
            'Error al eliminar',
            'No se pudo eliminar la farmacia. Inténtalo de nuevo.'
          )
        }
      })
    }
  }
}

const toggleStatus = (pharmacy) => {
  const action = pharmacy.activo ? 'desactivar' : 'activar'
  const actionPast = pharmacy.activo ? 'desactivada' : 'activada'

  confirmModal.value = {
    show: true,
    title: `${action.charAt(0).toUpperCase() + action.slice(1)} Farmacia`,
    message: `¿Estás seguro de ${action} la farmacia "${pharmacy.nombre_comercial}"?`,
    type: pharmacy.activo ? 'warning' : 'success',
    confirmText: action.charAt(0).toUpperCase() + action.slice(1),
    action: () => {
      router.post(route('pharmacies.toggle-status', pharmacy.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
          window.$notify?.success(
            'Estado actualizado',
            `Farmacia ${actionPast} correctamente`
          )
        }
      })
    }
  }
}

const exportData = () => {
  router.get(route('pharmacies.export'), searchForm.value)
}

// Funciones del modal
const handleConfirm = () => {
  if (confirmModal.value.action) {
    confirmModal.value.action()
  }
  confirmModal.value.show = false
}

const handleCancel = () => {
  confirmModal.value.show = false
}

const getTipoClienteBadgeVariant = (tipo) => {
  const variants = {
    regular: 'default',
    mayorista: 'primary',
    preferencial: 'success'
  }
  return variants[tipo] || 'default'
}

const getTipoClienteLabel = (tipo) => {
  const labels = {
    regular: 'Regular',
    mayorista: 'Mayorista',
    preferencial: 'Preferencial'
  }
  return labels[tipo] || tipo
}

const route = (name, params = null) => {
  const routes = {
    'pharmacies.index': '/pharmacies',
    'pharmacies.create': '/pharmacies/create',
    'pharmacies.show': (id) => `/pharmacies/${id}`,
    'pharmacies.edit': (id) => `/pharmacies/${id}/edit`,
    'pharmacies.destroy': (id) => `/pharmacies/${id}`,
    'pharmacies.toggle-status': (id) => `/pharmacies/${id}/toggle-status`,
    'pharmacies.export': '/pharmacies/export'
  }

  const routePattern = routes[name]
  if (typeof routePattern === 'function') {
    return routePattern(params)
  }
  return routePattern
}
</script>