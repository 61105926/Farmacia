<template>
  <div class="min-h-screen bg-gray-100 flex items-center justify-center p-4">
    <div class="max-w-md w-full">
      <Card>
        <CardHeader>
          <CardTitle class="text-center">Farmacia ERP</CardTitle>
          <p class="text-gray-600 text-center">
            Sistema de gestión para distribuidora de medicamentos
          </p>
        </CardHeader>
        <CardContent>
          <div class="space-y-3 mb-6">
            <div class="flex items-center text-green-600">
              <CheckIcon class="h-4 w-4 mr-2" />
              <span class="text-sm">Laravel configurado</span>
            </div>
            <div class="flex items-center text-green-600">
              <CheckIcon class="h-4 w-4 mr-2" />
              <span class="text-sm">Inertia.js funcionando</span>
            </div>
            <div class="flex items-center text-green-600">
              <CheckIcon class="h-4 w-4 mr-2" />
              <span class="text-sm">Vue 3 activo</span>
            </div>
            <div class="flex items-center text-green-600">
              <CheckIcon class="h-4 w-4 mr-2" />
              <span class="text-sm">Tailwind CSS cargado</span>
            </div>
            <div class="flex items-center text-green-600">
              <CheckIcon class="h-4 w-4 mr-2" />
              <span class="text-sm">Shadcn-Vue instalado</span>
            </div>
          </div>

          <div class="space-y-4">
            <Button class="w-full" variant="default">
              Continuar con configuración
            </Button>
            <Button class="w-full" variant="outline">
              Ver documentación
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>
  </div>
</template>

<script setup>
import { Card, CardHeader, CardTitle, CardContent, Button } from '@/Components/ui'
import { Check as CheckIcon } from 'lucide-vue-next'
</script>