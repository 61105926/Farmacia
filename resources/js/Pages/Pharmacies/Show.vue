<template>
  <AdminLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">{{ pharmacy.nombre_comercial }}</h1>
          <p class="text-gray-600 mt-1">{{ pharmacy.razon_social }}</p>
        </div>
        <div class="flex items-center space-x-4">
          <Button @click="$inertia.get(route('pharmacies.edit', pharmacy.id))" variant="outline">
            <Edit class="w-4 h-4 mr-2" />
            Editar
          </Button>
          <Button @click="$inertia.get(route('pharmacies.index'))" variant="outline">
            <ArrowLeft class="w-4 h-4 mr-2" />
            Volver
          </Button>
        </div>
      </div>
    </template>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Información Principal -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Datos Básicos -->
        <Card>
          <CardHeader>
            <CardTitle>Información Básica</CardTitle>
          </CardHeader>
          <CardContent class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="text-sm font-medium text-gray-500">Código Cliente</label>
                <p class="font-mono font-medium">{{ pharmacy.codigo_cliente }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-500">Tipo Cliente</label>
                <div class="mt-1">
                  <Badge :variant="getTipoClienteBadgeVariant(pharmacy.tipo_cliente)">
                    {{ getTipoClienteLabel(pharmacy.tipo_cliente) }}
                  </Badge>
                </div>
              </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="text-sm font-medium text-gray-500">{{ pharmacy.tipo_documento }}</label>
                <p class="font-medium">{{ pharmacy.numero_documento }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-500">Estado</label>
                <div class="mt-1">
                  <Badge :variant="pharmacy.activo ? 'success' : 'error'">
                    {{ pharmacy.activo ? 'Activo' : 'Inactivo' }}
                  </Badge>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Contacto -->
        <Card>
          <CardHeader>
            <CardTitle>Información de Contacto</CardTitle>
          </CardHeader>
          <CardContent class="space-y-4">
            <div>
              <label class="text-sm font-medium text-gray-500">Dirección</label>
              <p>{{ pharmacy.direccion }}</p>
              <p class="text-sm text-gray-600">
                {{ pharmacy.ciudad }}, {{ pharmacy.provincia }}
                <span v-if="pharmacy.codigo_postal"> - {{ pharmacy.codigo_postal }}</span>
              </p>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="text-sm font-medium text-gray-500">Teléfonos</label>
                <p>{{ pharmacy.telefono_principal }}</p>
                <p v-if="pharmacy.telefono_secundario" class="text-sm text-gray-600">
                  {{ pharmacy.telefono_secundario }}
                </p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-500">Emails</label>
                <p>{{ pharmacy.email_principal }}</p>
                <p v-if="pharmacy.email_secundario" class="text-sm text-gray-600">
                  {{ pharmacy.email_secundario }}
                </p>
              </div>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Persona de Contacto</label>
              <p>{{ pharmacy.contacto_nombre }}</p>
              <p v-if="pharmacy.contacto_cargo" class="text-sm text-gray-600">{{ pharmacy.contacto_cargo }}</p>
              <p v-if="pharmacy.contacto_telefono" class="text-sm text-gray-600">{{ pharmacy.contacto_telefono }}</p>
            </div>
          </CardContent>
        </Card>

        <!-- Información Comercial -->
        <Card>
          <CardHeader>
            <CardTitle>Información Comercial</CardTitle>
          </CardHeader>
          <CardContent class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="text-sm font-medium text-gray-500">Límite de Crédito</label>
                <p class="text-lg font-semibold text-green-600">
                  ${{ Number(pharmacy.limite_credito).toLocaleString() }}
                </p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-500">Días de Crédito</label>
                <p class="text-lg font-semibold">{{ pharmacy.dias_credito }} días</p>
              </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="text-sm font-medium text-gray-500">Descuento General</label>
                <p class="text-lg font-semibold">{{ pharmacy.descuento_general }}%</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-500">Zona de Reparto</label>
                <p>{{ pharmacy.zona_reparto || 'No definida' }}</p>
              </div>
            </div>
            <div v-if="pharmacy.horario_atencion">
              <label class="text-sm font-medium text-gray-500">Horario de Atención</label>
              <p>{{ pharmacy.horario_atencion }}</p>
            </div>
            <div v-if="pharmacy.observaciones">
              <label class="text-sm font-medium text-gray-500">Observaciones</label>
              <p class="whitespace-pre-wrap">{{ pharmacy.observaciones }}</p>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <!-- Stats Rápidas -->
        <Card>
          <CardHeader>
            <CardTitle>Estadísticas</CardTitle>
          </CardHeader>
          <CardContent class="space-y-4">
            <div class="text-center p-4 bg-blue-50 rounded-lg">
              <p class="text-2xl font-bold text-blue-600">
                ${{ Number(pharmacy.total_compras).toLocaleString() }}
              </p>
              <p class="text-sm text-gray-600">Total Compras</p>
            </div>
            <div class="text-center p-4 bg-green-50 rounded-lg">
              <p class="text-2xl font-bold text-green-600">
                {{ pharmacy.dias_ultimo_pedido || '-' }}
              </p>
              <p class="text-sm text-gray-600">Días último pedido</p>
            </div>
          </CardContent>
        </Card>

        <!-- Acciones Rápidas -->
        <Card>
          <CardHeader>
            <CardTitle>Acciones</CardTitle>
          </CardHeader>
          <CardContent class="space-y-3">
            <Button class="w-full" variant="outline">
              <FileText class="w-4 h-4 mr-2" />
              Crear Pedido
            </Button>
            <Button class="w-full" variant="outline">
              <Receipt class="w-4 h-4 mr-2" />
              Ver Facturas
            </Button>
            <Button class="w-full" variant="outline">
              <CreditCard class="w-4 h-4 mr-2" />
              Estado de Cuenta
            </Button>
            <Button
              @click="toggleStatus"
              class="w-full"
              :variant="pharmacy.activo ? 'error' : 'success'"
            >
              <component :is="pharmacy.activo ? XCircle : CheckCircle" class="w-4 h-4 mr-2" />
              {{ pharmacy.activo ? 'Desactivar' : 'Activar' }}
            </Button>
          </CardContent>
        </Card>
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
import { Card, CardHeader, CardTitle, CardContent, Button, Badge, ConfirmModal } from '@/Components/ui'
import {
  ArrowLeft,
  Edit,
  FileText,
  Receipt,
  CreditCard,
  CheckCircle,
  XCircle
} from 'lucide-vue-next'

const props = defineProps({
  pharmacy: Object
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

const toggleStatus = () => {
  const action = props.pharmacy.activo ? 'desactivar' : 'activar'
  const actionPast = props.pharmacy.activo ? 'desactivada' : 'activada'

  confirmModal.value = {
    show: true,
    title: `${action.charAt(0).toUpperCase() + action.slice(1)} Farmacia`,
    message: `¿Estás seguro de ${action} la farmacia "${props.pharmacy.nombre_comercial}"?`,
    type: props.pharmacy.activo ? 'warning' : 'success',
    confirmText: action.charAt(0).toUpperCase() + action.slice(1),
    action: () => {
      router.post($route('pharmacies.toggle-status', props.pharmacy.id), {}, {
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
    'pharmacies.edit': (id) => `/pharmacies/${id}/edit`,
    'pharmacies.toggle-status': (id) => `/pharmacies/${id}/toggle-status`
  }

  const routePattern = routes[name]
  if (typeof routePattern === 'function') {
    return routePattern(params)
  }
  return routePattern
}
</script>