<template>
  <AdminLayout>
    <div class="p-6">
      <!-- Header -->
      <div class="mb-6">
        <Link href="/usuarios" class="text-sm text-primary-700 hover:text-primary-800 mb-2 inline-block">
          ← Volver a usuarios
        </Link>
        <h1 class="text-2xl font-bold text-gray-900">Nuevo Usuario</h1>
        <p class="text-sm text-gray-600 mt-1">Complete los datos del nuevo usuario</p>
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
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                    :class="{ 'border-red-500': form.errors.email }"
                  />
                  <span v-if="form.errors.email" class="text-sm text-red-600">
                    {{ form.errors.email }}
                  </span>
                </div>

                <!-- Password -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Contraseña <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="form.password"
                    type="password"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                    :class="{ 'border-red-500': form.errors.password }"
                  />
                  <p class="text-xs text-gray-500 mt-1">Mínimo 8 caracteres</p>
                  <span v-if="form.errors.password" class="text-sm text-red-600">
                    {{ form.errors.password }}
                  </span>
                </div>

                <!-- Password Confirmation -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Confirmar Contraseña <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="form.password_confirmation"
                    type="password"
                    required
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
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Documento de Identidad
                    </label>
                    <input
                      v-model="form.document_number"
                      type="text"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500"
                    />
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
                  </select>
                </div>

                <!-- Send Welcome Email -->
                <div class="flex items-center">
                  <input
                    id="send-email"
                    v-model="form.send_welcome_email"
                    type="checkbox"
                    class="h-4 w-4 text-primary-700 focus:ring-primary-500 border-gray-300 rounded"
                  />
                  <label for="send-email" class="ml-3 text-sm text-gray-700">
                    Enviar email de bienvenida
                  </label>
                </div>
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
                <span v-else>Crear Usuario</span>
              </button>
              <Link
                href="/usuarios"
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
import { useForm, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardHeader, CardTitle, CardContent } from '@/Components/ui'


const props = defineProps({
  roles: Array,
  branches: {
    type: Array,
    default: () => [],
  },
})

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  phone: '',
  document_number: '',
  branch_id: '',
  role_ids: [],
  status: 'active',
  send_welcome_email: true,
})

const submit = () => {
  form.post("/usuarios")
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
