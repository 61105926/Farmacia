<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md p-8">
      <div class="flex justify-center mb-6">
        <img
          :src="logoNombre"
          alt="Farmacia Pando Central"
          class="h-16 object-contain"
        />
      </div>
      <h2 class="text-2xl font-bold mb-6 text-center">Login Simple</h2>

      <form @submit.prevent="handleSubmit">
        <div class="mb-4">
          <label class="block text-sm font-medium mb-2">Email:</label>
          <input
            v-model="email"
            type="email"
            class="w-full border rounded px-3 py-2"
            required
          />
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium mb-2">Password:</label>
          <input
            v-model="password"
            type="password"
            class="w-full border rounded px-3 py-2"
            required
          />
        </div>

        <div class="mb-4">
          <label class="flex items-center">
            <input v-model="remember" type="checkbox" class="mr-2" />
            Remember me
          </label>
        </div>

        <button
          type="submit"
          class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600"
          :disabled="loading"
        >
          {{ loading ? 'Loading...' : 'Login' }}
        </button>
      </form>

      <div v-if="errors.length" class="mt-4 text-red-600">
        <ul>
          <li v-for="error in errors" :key="error">{{ error }}</li>
        </ul>
      </div>

      <div class="mt-4 text-xs text-gray-500">
        Debug: {{ formData }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import logoNombreImg from '@/../assets/images/logo-nombre.jpeg'

const logoNombre = logoNombreImg

const email = ref('admin@farmacia.com')
const password = ref('admin123')
const remember = ref(false)
const loading = ref(false)
const errors = ref([])

const formData = computed(() => ({
  email: email.value,
  password: password.value,
  remember: remember.value
}))

const handleSubmit = () => {
  loading.value = true
  errors.value = []

  console.log('Submitting form with data:', formData.value)

  router.post('/login', formData.value, {
    onSuccess: () => {
      console.log('Login successful')
    },
    onError: (responseErrors) => {
      console.log('Login errors:', responseErrors)
      errors.value = Object.values(responseErrors).flat()
    },
    onFinish: () => {
      loading.value = false
    }
  })
}
</script>