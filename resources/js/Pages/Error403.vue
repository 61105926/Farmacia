<template>
  <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <div class="text-center">
        <ShieldX class="mx-auto h-16 w-16 text-red-500" />
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Acceso Denegado
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          No tienes permisos para acceder a esta página
        </p>
      </div>

      <Card class="mt-8">
        <CardContent class="py-8 text-center">
          <p class="text-gray-700 mb-6">
            Tu rol actual no tiene los permisos necesarios para ver este contenido.
            Si crees que esto es un error, contacta al administrador del sistema.
          </p>
          <div class="space-y-3">
            <Button @click="goBack" variant="outline" class="w-full">
              <ArrowLeft class="h-4 w-4 mr-2" />
              Volver Atrás
            </Button>
            <Button @click="goToDashboard" class="w-full">
              <Home class="h-4 w-4 mr-2" />
              Ir al Dashboard
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>
  </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import { Card, CardContent, Button } from '@/Components/ui'
import { ShieldX, ArrowLeft, Home } from 'lucide-vue-next'

const goBack = () => {
  window.history.back()
}

const goToDashboard = () => {
  router.visit('/dashboard')
}
</script>