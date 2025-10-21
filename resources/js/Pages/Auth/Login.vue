<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary-50 to-accent-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <div class="flex justify-center mb-6">
          <img
            :src="logoNombre"
            alt="Farmacia Pando Central"
            class="h-20 object-contain"
          />
        </div>
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
              <label for="email" class="text-sm font-medium leading-none">Correo Electrónico</label>
              <input
                id="email"
                v-model="form.email"
                type="email"
                autocomplete="email"
                required
                class="mt-1 flex h-10 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm ring-offset-white placeholder:text-slate-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                :class="{ 'border-red-500': form.errors.email }"
              />
              <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">
                {{ form.errors.email }}
              </div>
            </div>

            <div>
              <label for="password" class="text-sm font-medium leading-none">Contraseña</label>
              <input
                id="password"
                v-model="form.password"
                type="password"
                autocomplete="current-password"
                required
                class="mt-1 flex h-10 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm ring-offset-white placeholder:text-slate-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                :class="{ 'border-red-500': form.errors.password }"
              />
              <div v-if="form.errors.password" class="mt-1 text-sm text-red-600">
                {{ form.errors.password }}
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
                <label for="remember" class="ml-2 block text-sm text-gray-900">
                  Recordarme
                </label>
              </div>
            </div>

            <div>
              <button
                type="submit"
                class="w-full inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary-700 text-white hover:bg-primary-800 h-10 px-4 py-2"
                :disabled="processing"
              >
                <span v-if="processing">Iniciando sesión...</span>
                <span v-else>Iniciar Sesión</span>
              </button>
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
import { Card, CardHeader, CardTitle, CardContent } from '@/Components/ui'
import logoNombreImg from '@/../assets/images/logo-nombre.jpeg'

const logoNombre = logoNombreImg

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const processing = ref(false)

const submit = () => {
  processing.value = true

  console.log('Form data before submit:', {
    email: form.email,
    password: form.password,
    remember: form.remember
  })

  form.post('/login', {
    onFinish: () => {
      processing.value = false
      form.reset('password')
    },
    onError: (errors) => {
      console.log('Form errors:', errors)
    }
  })
}
</script>