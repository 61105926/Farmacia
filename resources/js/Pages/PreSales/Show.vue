<template>
  <AdminLayout>
    <div class="p-6">
      <!-- Header -->
      <div class="mb-6 flex items-center justify-between">
        <div>
          <Link href="/preventas" class="text-sm text-primary-700 hover:text-primary-800 mb-2 inline-block flex items-center">
            <ArrowLeft class="h-4 w-4 mr-1" />
            Volver a preventas
          </Link>
          <h1 class="text-2xl font-bold text-gray-900">Detalle de Preventa</h1>
          <p class="text-sm text-gray-600 mt-1">Código: {{ presale.code }}</p>
        </div>
        <div class="flex items-center gap-2">
          <!-- Imprimir -->
          <Button
            variant="outline"
            @click="printPresale"
            title="Imprimir preventa"
          >
            <Printer class="h-4 w-4 mr-2" />
            Imprimir
          </Button>

          <!-- Editar (solo si es borrador) -->
          <Button
            v-if="presale.status === 'draft' && can('presales.update')"
            variant="outline"
            @click="editPresale"
            title="Editar preventa"
          >
            <Edit class="h-4 w-4 mr-2" />
            Editar
          </Button>

          <!-- Aprobar (solo si es borrador) -->
          <Button
            v-if="presale.status === 'draft' && can('presales.approve')"
            @click="approvePresale"
            title="Aprobar preventa"
            class="bg-green-600 hover:bg-green-700"
          >
            <CheckCircle class="h-4 w-4 mr-2" />
            Aprobar
          </Button>

          <!-- Convertir a Venta (solo si está confirmada) -->
          <Button
            v-if="presale.status === 'confirmed' && can('presales.convert')"
            @click="convertToSale"
            title="Convertir a venta"
            class="bg-blue-600 hover:bg-blue-700"
          >
            <ShoppingCart class="h-4 w-4 mr-2" />
            Convertir a Venta
          </Button>

          <!-- Cancelar -->
          <Button
            v-if="presale.status !== 'cancelled' && can('presales.cancel')"
            @click="cancelPresale"
            title="Cancelar preventa"
            class="bg-red-600 hover:bg-red-700"
          >
            <XCircle class="h-4 w-4 mr-2" />
            Cancelar
          </Button>
        </div>
      </div>

      <!-- Status Banner -->
      <div class="mb-6">
        <div :class="getStatusBannerClass(presale.status)" class="rounded-lg p-4">
          <div class="flex items-center">
            <component :is="getStatusIcon(presale.status)" class="h-6 w-6 mr-3" />
            <div>
              <h3 class="text-lg font-semibold">{{ getStatusTitle(presale.status) }}</h3>
              <p class="text-sm opacity-90">{{ getStatusDescription(presale.status) }}</p>
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Client Information -->
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center">
                <Building2 class="h-5 w-5 mr-2" />
                Información del Cliente
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <Label class="text-sm font-medium text-gray-500">Razón Social</Label>
                  <p class="text-sm text-gray-900">{{ presale.client?.business_name || 'N/A' }}</p>
                </div>
                <div>
                  <Label class="text-sm font-medium text-gray-500">Nombre Comercial</Label>
                  <p class="text-sm text-gray-900">{{ presale.client?.trade_name || 'N/A' }}</p>
                </div>
                <div>
                  <Label class="text-sm font-medium text-gray-500">Email</Label>
                  <p class="text-sm text-gray-900">{{ presale.client?.email || 'N/A' }}</p>
                </div>
                <div>
                  <Label class="text-sm font-medium text-gray-500">Teléfono</Label>
                  <p class="text-sm text-gray-900">{{ presale.client?.phone || 'N/A' }}</p>
                </div>
                <div class="md:col-span-2">
                  <Label class="text-sm font-medium text-gray-500">Dirección</Label>
                  <p class="text-sm text-gray-900">{{ presale.client?.address || 'N/A' }}</p>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Products -->
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center">
                <Package class="h-5 w-5 mr-2" />
                Productos
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div class="overflow-x-auto">
                <table class="w-full">
                  <thead>
                    <tr class="border-b">
                      <th class="text-left py-2">Producto</th>
                      <th class="text-center py-2">Cantidad</th>
                      <th class="text-right py-2">Precio Unit.</th>
                      <th class="text-right py-2">Descuento</th>
                      <th class="text-right py-2">Total</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="item in presale.items" :key="item.id" class="border-b">
                      <td class="py-3">
                        <div>
                          <p class="font-medium">
                            {{ item.product?.description || 'N/A' }}
                          </p>
                          <p class="text-sm text-gray-500">{{ item.product?.code || 'N/A' }}</p>
                        </div>
                      </td>
                      <td class="text-center py-3">{{ item.quantity }}</td>
                      <td class="text-right py-3">{{ formatCurrency(item.unit_price) }}</td>
                      <td class="text-right py-3">{{ formatCurrency(item.discount_amount) }}</td>
                      <td class="text-right py-3 font-medium">{{ formatCurrency(item.total) }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </CardContent>
          </Card>

          <!-- Notes -->
          <Card v-if="presale.notes">
            <CardHeader>
              <CardTitle class="flex items-center">
                <FileText class="h-5 w-5 mr-2" />
                Observaciones
              </CardTitle>
            </CardHeader>
            <CardContent>
              <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ presale.notes }}</p>
            </CardContent>
          </Card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Document Info -->
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center">
                <FileText class="h-5 w-5 mr-2" />
                Información del Documento
              </CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
              <div>
                <Label class="text-sm font-medium text-gray-500">Código</Label>
                <p class="text-sm text-gray-900 font-mono">{{ presale.code }}</p>
              </div>
              <div>
                <Label class="text-sm font-medium text-gray-500">Fecha de Creación</Label>
                <p class="text-sm text-gray-900">{{ formatDate(presale.created_at) }}</p>
              </div>
              <div>
                <Label class="text-sm font-medium text-gray-500">Estado</Label>
                <Badge :variant="getStatusVariant(presale.status)">
                  {{ getStatusText(presale.status) }}
                </Badge>
              </div>
              <div>
                <Label class="text-sm font-medium text-gray-500">Vendedor</Label>
                <p class="text-sm text-gray-900">{{ presale.salesperson?.name || 'N/A' }}</p>
              </div>
              <div v-if="presale.delivery_date">
                <Label class="text-sm font-medium text-gray-500">Fecha de Entrega</Label>
                <p class="text-sm text-gray-900">{{ formatDate(presale.delivery_date) }}</p>
              </div>
              <div v-if="presale.confirmed_at">
                <Label class="text-sm font-medium text-gray-500">Fecha de Confirmación</Label>
                <p class="text-sm text-gray-900">{{ formatDate(presale.confirmed_at) }}</p>
              </div>
            </CardContent>
          </Card>

          <!-- Totals -->
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center">
                <Calculator class="h-5 w-5 mr-2" />
                Totales
              </CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Subtotal:</span>
                <span class="text-sm font-medium">{{ formatCurrency(presale.subtotal) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Descuento Total:</span>
                <span class="text-sm font-medium text-red-600">-{{ formatCurrency(presale.total_discount) }}</span>
              </div>
              <div class="border-t pt-3">
                <div class="flex justify-between">
                  <span class="text-lg font-semibold">Total:</span>
                  <span class="text-lg font-bold text-primary-600">{{ formatCurrency(presale.total) }}</span>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Actions -->
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center">
                <Settings class="h-5 w-5 mr-2" />
                Acciones
              </CardTitle>
            </CardHeader>
            <CardContent class="space-y-2">
              <Button
                variant="outline"
                class="w-full justify-start"
                @click="printPresale"
              >
                <Printer class="h-4 w-4 mr-2" />
                Imprimir Preventa
              </Button>

              <Button
                v-if="presale.status === 'draft' && can('presales.update')"
                variant="outline"
                class="w-full justify-start"
                @click="editPresale"
              >
                <Edit class="h-4 w-4 mr-2" />
                Editar Preventa
              </Button>

              <Button
                v-if="presale.status === 'draft' && can('presales.approve')"
                class="w-full justify-start bg-green-600 hover:bg-green-700"
                @click="approvePresale"
              >
                <CheckCircle class="h-4 w-4 mr-2" />
                Aprobar Preventa
              </Button>

              <Button
                v-if="presale.status === 'confirmed' && can('presales.convert')"
                class="w-full justify-start bg-blue-600 hover:bg-blue-700"
                @click="convertToSale"
              >
                <ShoppingCart class="h-4 w-4 mr-2" />
                Convertir a Venta
              </Button>

              <Button
                v-if="presale.status !== 'cancelled' && can('presales.cancel')"
                variant="outline"
                class="w-full justify-start text-red-600 border-red-300 hover:bg-red-50"
                @click="cancelPresale"
              >
                <XCircle class="h-4 w-4 mr-2" />
                Cancelar Preventa
              </Button>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>

    <!-- Alert Dialog -->
    <AlertDialog
      :show="alertState.show"
      :type="alertState.type"
      :title="alertState.title"
      :message="alertState.message"
      :confirm-text="alertState.confirmText"
      @confirm="handleConfirm"
      @close="hideAlert"
    />
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Card from '@/Components/ui/Card.vue'
import CardHeader from '@/Components/ui/CardHeader.vue'
import CardTitle from '@/Components/ui/CardTitle.vue'
import CardContent from '@/Components/ui/CardContent.vue'
import Button from '@/Components/ui/Button.vue'
import Badge from '@/Components/ui/Badge.vue'
import Label from '@/Components/ui/Label.vue'
import AlertDialog from '@/Components/ui/AlertDialog.vue'
import { usePermissions } from '@/composables/usePermissions'
import { useAlert } from '@/composables/useAlert'
import {
  ArrowLeft,
  Printer,
  Edit,
  CheckCircle,
  ShoppingCart,
  XCircle,
  Building2,
  Package,
  FileText,
  Calculator,
  Settings,
  Clock,
  CheckCircle2,
  X,
  AlertCircle
} from 'lucide-vue-next'

const props = defineProps({
  presale: {
    type: Object,
    required: true
  }
})

const { can } = usePermissions()
const { alertState, showAlert, showConfirm, hideAlert, handleConfirm } = useAlert()

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
  return new Date(date).toLocaleDateString('es-BO')
}

const getStatusVariant = (status) => {
  const variants = {
    draft: 'secondary',
    confirmed: 'success',
    converted: 'primary',
    cancelled: 'destructive'
  }
  return variants[status] || 'secondary'
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

const getStatusBannerClass = (status) => {
  const classes = {
    draft: 'bg-yellow-50 border-yellow-200 text-yellow-800',
    confirmed: 'bg-green-50 border-green-200 text-green-800',
    converted: 'bg-blue-50 border-blue-200 text-blue-800',
    cancelled: 'bg-red-50 border-red-200 text-red-800'
  }
  return classes[status] || 'bg-gray-50 border-gray-200 text-gray-800'
}

const getStatusIcon = (status) => {
  const icons = {
    draft: Clock,
    confirmed: CheckCircle2,
    converted: ShoppingCart,
    cancelled: X
  }
  return icons[status] || AlertCircle
}

const getStatusTitle = (status) => {
  const titles = {
    draft: 'Preventa en Borrador',
    confirmed: 'Preventa Confirmada',
    converted: 'Preventa Convertida',
    cancelled: 'Preventa Cancelada'
  }
  return titles[status] || 'Estado Desconocido'
}

const getStatusDescription = (status) => {
  const descriptions = {
    draft: 'Esta preventa está en estado borrador y puede ser editada.',
    confirmed: 'Esta preventa ha sido confirmada y está lista para ser convertida en venta.',
    converted: 'Esta preventa ha sido convertida en una venta.',
    cancelled: 'Esta preventa ha sido cancelada.'
  }
  return descriptions[status] || 'Estado no definido.'
}

// Actions
const printPresale = () => {
  window.open(`/preventas/${props.presale.id}/imprimir`, '_blank')
}

const editPresale = () => {
  router.visit(`/preventas/${props.presale.id}/editar`)
}

const approvePresale = () => {
  showConfirm(
    'Aprobar Preventa',
    '¿Estás seguro de que quieres aprobar esta preventa?',
    'Aprobar',
    () => {
      router.post(`/preventas/${props.presale.id}/aprobar`, {}, {
        onSuccess: () => {
          showAlert('success', 'Preventa aprobada', 'La preventa ha sido aprobada exitosamente.')
        },
        onError: (errors) => {
          showAlert('error', 'Error', 'No se pudo aprobar la preventa.')
        }
      })
    }
  )
}

const convertToSale = () => {
  showConfirm(
    'Convertir a Venta',
    '¿Estás seguro de que quieres convertir esta preventa en una venta?',
    'Convertir',
    () => {
      router.post(`/preventas/${props.presale.id}/convertir`, {}, {
        onSuccess: () => {
          showAlert('success', 'Preventa convertida', 'La preventa ha sido convertida en venta exitosamente.')
        },
        onError: (errors) => {
          showAlert('error', 'Error', 'No se pudo convertir la preventa.')
        }
      })
    }
  )
}

const cancelPresale = () => {
  showConfirm(
    'Cancelar Preventa',
    '¿Estás seguro de que quieres cancelar esta preventa?',
    'Cancelar',
    () => {
      router.post(`/preventas/${props.presale.id}/rechazar`, {}, {
        onSuccess: () => {
          showAlert('success', 'Preventa cancelada', 'La preventa ha sido cancelada exitosamente.')
        },
        onError: (errors) => {
          showAlert('error', 'Error', 'No se pudo cancelar la preventa.')
        }
      })
    }
  )
}
</script>