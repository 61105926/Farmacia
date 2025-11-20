<template>
  <AdminLayout>
    <div class="p-6">
      <!-- Header -->
      <div class="mb-6">
        <Link href="/usuarios" class="text-sm text-primary-700 hover:text-primary-800 mb-2 inline-block">
          ← Volver a usuarios
        </Link>
        <h1 class="text-2xl font-bold text-gray-900">Editar Usuario</h1>
        <p class="text-sm text-gray-600 mt-1">{{ user.name }}</p>
      </div>

      <form @submit.prevent="submit">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Main Info -->
          <div class="lg:col-span-2">
            <Card>
              <CardHeader>
                <CardTitle>Información Personal</CardTitle>
              </CardHeader>
              <CardContent class="space-y-4">
                <!-- Name -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nombre Completo <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="form.name"
                    type="text"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    :class="{ 'border-red-500': form.errors.name }"
                  />
                  <span v-if="form.errors.name" class="text-sm text-red-600">
                    {{ form.errors.name }}
                  </span>
                </div>

                <!-- Email -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Email <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="form.email"
                    type="email"
                    required
                    @blur="validateEmail"
                    placeholder="ejemplo@correo.com"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                    :class="{ 'border-red-500': form.errors.email || emailError }"
                  />
                  <span v-if="form.errors.email" class="text-sm text-red-600">
                    {{ form.errors.email }}
                  </span>
                  <span v-if="emailError && !form.errors.email" class="text-sm text-red-600">
                    {{ emailError }}
                  </span>
                </div>

                <!-- Password (Optional) -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nueva Contraseña
                  </label>
                  <input
                    v-model="form.password"
                    type="password"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                    :class="{ 'border-red-500': form.errors.password }"
                  />
                  <p class="text-xs text-gray-500 mt-1">Dejar en blanco para mantener la contraseña actual</p>
                  <span v-if="form.errors.password" class="text-sm text-red-600">
                    {{ form.errors.password }}
                  </span>
                </div>

                <!-- Password Confirmation -->
                <div v-if="form.password">
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Confirmar Contraseña
                  </label>
                  <input
                    v-model="form.password_confirmation"
                    type="password"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                  />
                </div>

                <!-- Phone and Document -->
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Teléfono
                    </label>
                    <input
                      v-model="form.phone"
                      type="tel"
                      @input="validatePhone"
                      @keypress="onlyNumbersAndPhoneChars"
                      placeholder="Ej: 591-3-8421234"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                      :class="{ 'border-red-500': form.errors.phone }"
                    />
                    <span v-if="form.errors.phone" class="text-sm text-red-600">
                      {{ form.errors.phone }}
                    </span>
                    <p class="text-xs text-gray-500 mt-1">Solo números, espacios, guiones y paréntesis</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Documento de Identidad
                    </label>
                    <input
                      v-model="form.document_number"
                      type="text"
                      @keypress="onlyNumbers"
                      placeholder="12345678"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                      :class="{ 'border-red-500': form.errors.document_number }"
                    />
                    <span v-if="form.errors.document_number" class="text-sm text-red-600">
                      {{ form.errors.document_number }}
                    </span>
                    <p class="text-xs text-gray-500 mt-1">Solo números</p>
                  </div>
                </div>

                <!-- Branch -->
                <div v-if="branches.length > 0">
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Sucursal
                  </label>
                  <select
                    v-model="form.branch_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                  >
                    <option value="">Sin asignar</option>
                    <option v-for="branch in branches" :key="branch.id" :value="branch.id">
                      {{ branch.name }}
                    </option>
                  </select>
                </div>
              </CardContent>
            </Card>

            <!-- Login Stats -->
            <Card class="mt-6">
              <CardHeader>
                <CardTitle>Estadísticas de Acceso</CardTitle>
              </CardHeader>
              <CardContent class="space-y-3">
                <div>
                  <p class="text-sm text-gray-600">Último Acceso</p>
                  <p class="font-medium">{{ user.last_login_at ? formatDate(user.last_login_at) : 'Nunca' }}</p>
                </div>
                <div v-if="user.last_login_ip">
                  <p class="text-sm text-gray-600">IP del Último Acceso</p>
                  <p class="font-medium">{{ user.last_login_ip }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-600">Intentos Fallidos</p>
                  <p class="font-medium">{{ user.failed_login_attempts || 0 }}</p>
                </div>
              </CardContent>
            </Card>
          </div>

          <!-- Roles & Settings -->
          <div>
            <Card class="mb-6">
              <CardHeader>
                <CardTitle>Roles y Permisos</CardTitle>
              </CardHeader>
              <CardContent class="space-y-3">
                <div v-for="role in roles" :key="role.id" class="flex items-start">
                  <input
                    :id="`role-${role.id}`"
                    v-model="form.role_ids"
                    type="checkbox"
                    :value="role.id"
                    class="mt-1 h-4 w-4 text-primary-700 focus:ring-primary-500 border-gray-300 rounded"
                  />
                  <label :for="`role-${role.id}`" class="ml-3 cursor-pointer">
                    <span class="block text-sm font-medium text-gray-900">{{ role.name }}</span>
                    <span class="block text-xs text-gray-500">{{ getRoleDescription(role.name) }}</span>
                  </label>
                </div>
                <span v-if="form.errors.role_ids" class="text-sm text-red-600">
                  {{ form.errors.role_ids }}
                </span>
              </CardContent>
            </Card>

            <Card class="mb-6">
              <CardHeader>
                <CardTitle>Configuración</CardTitle>
              </CardHeader>
              <CardContent class="space-y-4">
                <!-- Status -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Estado
                  </label>
                  <select
                    v-model="form.status"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                  >
                    <option value="active">Activo</option>
                    <option value="inactive">Inactivo</option>
                    <option value="blocked">Bloqueado</option>
                  </select>
                </div>
              </CardContent>
            </Card>

            <!-- Quick Actions -->
            <Card class="mb-6" v-if="user.status === 'blocked' || user.failed_login_attempts > 0">
              <CardHeader>
                <CardTitle>Acciones Rápidas</CardTitle>
              </CardHeader>
              <CardContent class="space-y-2">
                <button
                  v-if="user.status === 'blocked'"
                  type="button"
                  @click="unblockUser"
                  class="w-full px-3 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700 text-sm"
                >
                  Desbloquear Usuario
                </button>
                <button
                  type="button"
                  @click="resetPassword"
                  class="w-full px-3 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm"
                >
                  Restablecer Contraseña
                </button>
              </CardContent>
            </Card>

            <!-- Actions -->
            <div class="flex flex-col space-y-2">
              <button
                type="submit"
                :disabled="form.processing"
                class="w-full px-4 py-2 bg-primary-700 text-white rounded-md hover:bg-primary-800 disabled:opacity-50 transition-colors"
              >
                <span v-if="form.processing">Guardando...</span>
                <span v-else>Actualizar Usuario</span>
              </button>
              <Link
                :href="`/usuarios/${user.id}`"
                class="w-full px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-center transition-colors"
              >
                Cancelar
              </Link>
            </div>
          </div>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useForm, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardHeader, CardTitle, CardContent } from '@/Components/ui'

const emailError = ref('')

const props = defineProps({
  user: Object,
  roles: Array,
  branches: {
    type: Array,
    default: () => [],
  },
})

const form = useForm({
  name: props.user.name,
  email: props.user.email,
  password: '',
  password_confirmation: '',
  phone: props.user.phone,
  document_number: props.user.document_number,
  branch_id: props.user.branch_id || '',
  role_ids: props.user.roles.map(role => role.id),
  status: props.user.status,
})

// Validaciones
const onlyNumbers = (event) => {
  const char = String.fromCharCode(event.which)
  if (!/[0-9]/.test(char)) {
    event.preventDefault()
  }
}

const onlyNumbersAndPhoneChars = (event) => {
  const char = String.fromCharCode(event.which)
  // Permitir números, espacios, guiones, paréntesis, + y punto
  if (!/[0-9\s\-\(\)\+\.]/.test(char)) {
    event.preventDefault()
  }
}

const validatePhone = () => {
  // Limpiar caracteres no permitidos
  form.phone = form.phone.replace(/[^0-9\s\-\(\)\+\.]/g, '')
}

const validateEmail = () => {
  emailError.value = ''
  if (form.email && form.email.trim() !== '') {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    if (!emailRegex.test(form.email)) {
      emailError.value = 'Por favor ingrese un email válido'
    }
  }
}

const submit = () => {
  emailError.value = ''
  form.put(`/usuarios/${props.user.id}`)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('es-CO', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const unblockUser = () => {
  if (confirm('¿Está seguro de desbloquear este usuario?')) {
    router.post(`/usuarios/${props.user.id}/desbloquear`)
  }
}

const resetPassword = () => {
  if (confirm('¿Está seguro de restablecer la contraseña de este usuario? Se enviará un email con las instrucciones.')) {
    router.post(`/usuarios/${props.user.id}/restablecer-password`)
  }
}

const getRoleDescription = (roleName) => {
  const descriptions = {
    'super-admin': 'Acceso total al sistema',
    'administrador': 'Administrador con permisos limitados',
    'vendedor': 'Vendedor con permisos de ventas',
    'usuario': 'Usuario básico del sistema',
  }
  return descriptions[roleName] || 'Sin descripción'
}
</script>
