<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Farmacia ERP
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Sistema de Gestión para Distribuidora de Medicamentos
        </p>
      </div>

      <Card>
        <CardHeader>
          <CardTitle class="text-center">Iniciar Sesión</CardTitle>
        </CardHeader>
        <CardContent>
          <form class="space-y-6" @submit.prevent="submit">
            <div>
              <Label for="email">Correo Electrónico</Label>
              <Input
                id="email"
                v-model="form.email"
                type="email"
                autocomplete="email"
                required
                class="mt-1"
                :class="{ 'border-red-500': errors.email }"
              />
              <div v-if="errors.email" class="mt-1 text-sm text-red-600">
                {{ errors.email }}
              </div>
            </div>

            <div>
              <Label for="password">Contraseña</Label>
              <Input
                id="password"
                v-model="form.password"
                type="password"
                autocomplete="current-password"
                required
                class="mt-1"
                :class="{ 'border-red-500': errors.password }"
              />
              <div v-if="errors.password" class="mt-1 text-sm text-red-600">
                {{ errors.password }}
              </div>
            </div>

            <div class="flex items-center justify-between">
              <div class="flex items-center">
                <input
                  id="remember"
                  v-model="form.remember"
                  type="checkbox"
                  class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                />
                <Label for="remember" class="ml-2 block text-sm text-gray-900">
                  Recordarme
                </Label>
              </div>
            </div>

            <div>
              <Button
                type="submit"
                class="w-full"
                :disabled="processing"
              >
                <span v-if="processing">Iniciando sesión...</span>
                <span v-else>Iniciar Sesión</span>
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>

      <div class="text-center text-xs text-gray-500">
        Usuario de prueba: admin@farmacia.com | Contraseña: admin123
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { Card, CardHeader, CardTitle, CardContent, Button, Input, Label } from '@/Components/ui'

defineProps({
  errors: Object,
})

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const processing = ref(false)

const submit = () => {
  processing.value = true
  form.post('/login', {
    onFinish: () => {
      processing.value = false
      form.reset('password')
    },
  })
}
</script>