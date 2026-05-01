<template>
  <div class="min-h-screen flex items-center justify-center bg-[#e8ede0] py-12 px-4">
    <div class="w-full max-w-sm space-y-6">

      <!-- Logo + Título -->
      <div class="flex flex-col items-center space-y-3">
        <img
          :src="logoNombre"
          alt="Farmacias Pando Central"
          class="h-20 object-contain"
        />
        <h1 class="text-4xl font-extrabold text-gray-900 tracking-wide">SISPANDO</h1>
        <p class="text-xs font-semibold text-gray-600 tracking-widest text-center uppercase">
          Gestión de Preventas, Ventas y Cuentas por Cobrar
        </p>
      </div>

      <!-- Card -->
      <div class="bg-white rounded-xl shadow-md px-8 py-8 space-y-5">
        <h2 class="text-xl font-bold text-gray-900 text-center">Iniciar Sesión</h2>

        <form @submit.prevent="submit" class="space-y-4">
          <!-- Email -->
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
              Correo Electrónico
            </label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              autocomplete="email"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-green-700"
              :class="{ 'border-red-500': form.errors.email }"
            />
            <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</p>
          </div>

          <!-- Contraseña -->
          <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
              Contraseña
            </label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              autocomplete="current-password"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-green-700"
              :class="{ 'border-red-500': form.errors.password }"
            />
            <p v-if="form.errors.password" class="mt-1 text-xs text-red-600">{{ form.errors.password }}</p>
          </div>

          <!-- Recordarme -->
          <div class="flex items-center gap-2">
            <input
              id="remember"
              v-model="form.remember"
              type="checkbox"
              class="h-4 w-4 rounded border-gray-300 text-green-700 focus:ring-green-700"
            />
            <label for="remember" class="text-sm text-gray-700">Recordarme</label>
          </div>

          <!-- Botón -->
          <button
            type="submit"
            :disabled="form.processing"
            class="w-full py-2.5 bg-[#2d5c1e] hover:bg-[#234816] text-white font-semibold rounded-md text-sm transition-colors disabled:opacity-50"
          >
            {{ form.processing ? 'Iniciando sesión...' : 'Iniciar Sesión' }}
          </button>
        </form>
      </div>

    </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import logoNombreImg from '@/../assets/images/logo-nombre.jpeg'

const logoNombre = logoNombreImg

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const submit = () => {
  form.post('/login', {
    onFinish: () => form.reset('password'),
  })
}
</script>
