<template>
  <AdminLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Editar Farmacia</h1>
          <p class="text-gray-600 mt-1">{{ pharmacy.nombre_comercial }}</p>
        </div>
        <div class="flex items-center space-x-4">
          <Button @click="$inertia.get(route('pharmacies.show', pharmacy.id))" variant="outline">
            <Eye class="w-4 h-4 mr-2" />
            Ver Detalle
          </Button>
          <Button @click="$inertia.get(route('pharmacies.index'))" variant="outline">
            <ArrowLeft class="w-4 h-4 mr-2" />
            Volver a Lista
          </Button>
        </div>
      </div>
    </template>

    <form @submit.prevent="submit" class="space-y-8">
      <!-- Información Básica -->
      <Card>
        <CardHeader>
          <CardTitle>Información Básica</CardTitle>
        </CardHeader>
        <CardContent class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <Label for="codigo_cliente">Código Cliente</Label>
              <Input
                id="codigo_cliente"
                v-model="form.codigo_cliente"
                :error="form.errors.codigo_cliente"
                disabled
                class="bg-gray-100 cursor-not-allowed"
              />
              <p class="text-xs text-gray-500 mt-1">El código de cliente no puede ser modificado</p>
            </div>
            <div>
              <Label for="tipo_cliente">Tipo Cliente *</Label>
              <select
                id="tipo_cliente"
                v-model="form.tipo_cliente"
                class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                :class="{ 'border-red-500': form.errors.tipo_cliente }"
              >
                <option value="regular">Regular</option>
                <option value="mayorista">Mayorista</option>
                <option value="preferencial">Preferencial</option>
              </select>
              <div v-if="form.errors.tipo_cliente" class="mt-1 text-sm text-red-600">
                {{ form.errors.tipo_cliente }}
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <Label for="nombre_comercial">Nombre Comercial *</Label>
              <Input
                id="nombre_comercial"
                v-model="form.nombre_comercial"
                :error="form.errors.nombre_comercial"
                required
              />
            </div>
            <div>
              <Label for="razon_social">Razón Social *</Label>
              <Input
                id="razon_social"
                v-model="form.razon_social"
                :error="form.errors.razon_social"
                required
              />
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
              <Label for="tipo_documento">Tipo Documento *</Label>
              <select
                id="tipo_documento"
                v-model="form.tipo_documento"
                class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                :class="{ 'border-red-500': form.errors.tipo_documento }"
              >
                <option value="RUC">RUC</option>
                <option value="CI">Cédula</option>
                <option value="PASAPORTE">Pasaporte</option>
              </select>
              <div v-if="form.errors.tipo_documento" class="mt-1 text-sm text-red-600">
                {{ form.errors.tipo_documento }}
              </div>
            </div>
            <div>
              <Label for="numero_documento">Número Documento *</Label>
              <Input
                id="numero_documento"
                v-model="form.numero_documento"
                :error="form.errors.numero_documento"
                required
              />
            </div>
            <div class="flex items-center space-x-2 mt-6">
              <input
                id="activo"
                type="checkbox"
                v-model="form.activo"
                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
              />
              <Label for="activo">Farmacia Activa</Label>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Información de Contacto -->
      <Card>
        <CardHeader>
          <CardTitle>Información de Contacto</CardTitle>
        </CardHeader>
        <CardContent class="space-y-6">
          <div>
            <Label for="direccion">Dirección *</Label>
            <textarea
              id="direccion"
              v-model="form.direccion"
              rows="3"
              class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500"
              :class="{ 'border-red-500': form.errors.direccion }"
              required
            ></textarea>
            <div v-if="form.errors.direccion" class="mt-1 text-sm text-red-600">
              {{ form.errors.direccion }}
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
              <Label for="ciudad">Ciudad *</Label>
              <Input
                id="ciudad"
                v-model="form.ciudad"
                :error="form.errors.ciudad"
                required
              />
            </div>
            <div>
              <Label for="provincia">Provincia *</Label>
              <Input
                id="provincia"
                v-model="form.provincia"
                :error="form.errors.provincia"
                required
              />
            </div>
            <div>
              <Label for="codigo_postal">Código Postal</Label>
              <Input
                id="codigo_postal"
                v-model="form.codigo_postal"
                :error="form.errors.codigo_postal"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <Label for="telefono_principal">Teléfono Principal *</Label>
              <Input
                id="telefono_principal"
                v-model="form.telefono_principal"
                :error="form.errors.telefono_principal"
                required
              />
            </div>
            <div>
              <Label for="telefono_secundario">Teléfono Secundario</Label>
              <Input
                id="telefono_secundario"
                v-model="form.telefono_secundario"
                :error="form.errors.telefono_secundario"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <Label for="email_principal">Email Principal *</Label>
              <Input
                id="email_principal"
                type="email"
                v-model="form.email_principal"
                :error="form.errors.email_principal"
                required
              />
            </div>
            <div>
              <Label for="email_secundario">Email Secundario</Label>
              <Input
                id="email_secundario"
                type="email"
                v-model="form.email_secundario"
                :error="form.errors.email_secundario"
              />
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Persona de Contacto -->
      <Card>
        <CardHeader>
          <CardTitle>Persona de Contacto</CardTitle>
        </CardHeader>
        <CardContent class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <Label for="contacto_nombre">Nombre Contacto *</Label>
              <Input
                id="contacto_nombre"
                v-model="form.contacto_nombre"
                :error="form.errors.contacto_nombre"
                required
              />
            </div>
            <div>
              <Label for="contacto_cargo">Cargo</Label>
              <Input
                id="contacto_cargo"
                v-model="form.contacto_cargo"
                :error="form.errors.contacto_cargo"
              />
            </div>
          </div>
          <div>
            <Label for="contacto_telefono">Teléfono Contacto</Label>
            <Input
              id="contacto_telefono"
              v-model="form.contacto_telefono"
              :error="form.errors.contacto_telefono"
            />
          </div>
        </CardContent>
      </Card>

      <!-- Información Comercial -->
      <Card>
        <CardHeader>
          <CardTitle>Información Comercial</CardTitle>
        </CardHeader>
        <CardContent class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <Label for="limite_credito">Límite de Crédito</Label>
              <Input
                id="limite_credito"
                type="number"
                step="0.01"
                min="0"
                v-model="form.limite_credito"
                :error="form.errors.limite_credito"
              />
            </div>
            <div>
              <Label for="dias_credito">Días de Crédito</Label>
              <Input
                id="dias_credito"
                type="number"
                min="0"
                max="365"
                v-model="form.dias_credito"
                :error="form.errors.dias_credito"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <Label for="descuento_general">Descuento General (%)</Label>
              <Input
                id="descuento_general"
                type="number"
                step="0.01"
                min="0"
                max="100"
                v-model="form.descuento_general"
                :error="form.errors.descuento_general"
              />
            </div>
            <div>
              <Label for="zona_reparto">Zona de Reparto</Label>
              <Input
                id="zona_reparto"
                v-model="form.zona_reparto"
                :error="form.errors.zona_reparto"
              />
            </div>
          </div>

          <div>
            <Label for="horario_atencion">Horario de Atención</Label>
            <Input
              id="horario_atencion"
              v-model="form.horario_atencion"
              :error="form.errors.horario_atencion"
              placeholder="Ej: Lunes a Viernes 8:00 - 18:00"
            />
          </div>

          <div>
            <Label for="observaciones">Observaciones</Label>
            <textarea
              id="observaciones"
              v-model="form.observaciones"
              rows="4"
              class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500"
              :class="{ 'border-red-500': form.errors.observaciones }"
              placeholder="Información adicional sobre la farmacia..."
            ></textarea>
            <div v-if="form.errors.observaciones" class="mt-1 text-sm text-red-600">
              {{ form.errors.observaciones }}
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Botones de Acción -->
      <div class="flex justify-end space-x-4">
        <Button
          type="button"
          @click="$inertia.get(route('pharmacies.index'))"
          variant="outline"
        >
          Cancelar
        </Button>
        <Button
          type="submit"
          :disabled="form.processing"
        >
          <Loader v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" />
          {{ form.processing ? 'Actualizando...' : 'Actualizar Farmacia' }}
        </Button>
      </div>
    </form>
  </AdminLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardHeader, CardTitle, CardContent, Button, Input, Label } from '@/Components/ui'
import { ArrowLeft, Eye, Loader } from 'lucide-vue-next'

const props = defineProps({
  pharmacy: Object
})

const form = useForm({
  codigo_cliente: props.pharmacy.codigo_cliente,
  nombre_comercial: props.pharmacy.nombre_comercial,
  razon_social: props.pharmacy.razon_social,
  tipo_documento: props.pharmacy.tipo_documento,
  numero_documento: props.pharmacy.numero_documento,
  direccion: props.pharmacy.direccion,
  ciudad: props.pharmacy.ciudad,
  provincia: props.pharmacy.provincia,
  codigo_postal: props.pharmacy.codigo_postal,
  telefono_principal: props.pharmacy.telefono_principal,
  telefono_secundario: props.pharmacy.telefono_secundario,
  email_principal: props.pharmacy.email_principal,
  email_secundario: props.pharmacy.email_secundario,
  contacto_nombre: props.pharmacy.contacto_nombre,
  contacto_cargo: props.pharmacy.contacto_cargo,
  contacto_telefono: props.pharmacy.contacto_telefono,
  limite_credito: props.pharmacy.limite_credito,
  dias_credito: props.pharmacy.dias_credito,
  tipo_cliente: props.pharmacy.tipo_cliente,
  descuento_general: props.pharmacy.descuento_general,
  activo: props.pharmacy.activo,
  horario_atencion: props.pharmacy.horario_atencion,
  zona_reparto: props.pharmacy.zona_reparto,
  observaciones: props.pharmacy.observaciones
})

const submit = () => {
  form.put(route('pharmacies.update', props.pharmacy.id), {
    onSuccess: () => {
      window.$notify?.success(
        'Farmacia actualizada',
        'Los datos han sido actualizados exitosamente'
      )
    },
    onError: () => {
      window.$notify?.error(
        'Error al actualizar',
        'Por favor revisa los datos ingresados'
      )
    }
  })
}

const route = (name, params = null) => {
  const routes = {
    'pharmacies.index': '/pharmacies',
    'pharmacies.show': (id) => `/pharmacies/${id}`,
    'pharmacies.update': (id) => `/pharmacies/${id}`
  }

  const routePattern = routes[name]
  if (typeof routePattern === 'function') {
    return routePattern(params)
  }
  return routePattern
}
</script>