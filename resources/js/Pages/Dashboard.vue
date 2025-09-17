<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Header -->
    <header class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
            <p class="text-gray-600">Farmacia ERP - Sistema de Gestión</p>
          </div>
          <div class="flex items-center space-x-4">
            <div class="text-right">
              <p class="text-sm font-medium text-gray-900">{{ user.name }}</p>
              <p class="text-xs text-gray-500">{{ user.roles[0]?.name }}</p>
            </div>
            <Button variant="outline" @click="logout">
              <LogOut class="h-4 w-4 mr-2" />
              Cerrar Sesión
            </Button>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <!-- Welcome Message -->
        <div class="mb-8">
          <Card>
            <CardHeader>
              <CardTitle>Bienvenido al Sistema ERP</CardTitle>
            </CardHeader>
            <CardContent>
              <p class="text-gray-600 mb-4">
                Fase 1 completada exitosamente. El sistema base está listo con:
              </p>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-center text-green-600">
                  <CheckIcon class="h-5 w-5 mr-2" />
                  <span>Sistema de usuarios y roles configurado</span>
                </div>
                <div class="flex items-center text-green-600">
                  <CheckIcon class="h-5 w-5 mr-2" />
                  <span>Autenticación con Inertia.js funcionando</span>
                </div>
                <div class="flex items-center text-green-600">
                  <CheckIcon class="h-5 w-5 mr-2" />
                  <span>Layout base implementado</span>
                </div>
                <div class="flex items-center text-green-600">
                  <CheckIcon class="h-5 w-5 mr-2" />
                  <span>Permisos y middleware activos</span>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
          <Card>
            <CardContent class="p-6">
              <div class="flex items-center">
                <Users class="h-8 w-8 text-blue-500" />
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-500">Usuarios</p>
                  <p class="text-2xl font-bold">1</p>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent class="p-6">
              <div class="flex items-center">
                <Package class="h-8 w-8 text-green-500" />
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-500">Productos</p>
                  <p class="text-2xl font-bold">0</p>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent class="p-6">
              <div class="flex items-center">
                <ShoppingCart class="h-8 w-8 text-purple-500" />
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-500">Ventas</p>
                  <p class="text-2xl font-bold">0</p>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent class="p-6">
              <div class="flex items-center">
                <DollarSign class="h-8 w-8 text-yellow-500" />
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-500">Ingresos</p>
                  <p class="text-2xl font-bold">$0</p>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Next Steps -->
        <Card>
          <CardHeader>
            <CardTitle>Próximos Pasos - Fase 2</CardTitle>
          </CardHeader>
          <CardContent>
            <p class="text-gray-600 mb-4">
              Estás listo para continuar con la Fase 2: Gestión de Clientes y Productos
            </p>
            <div class="space-y-2">
              <div class="flex items-center text-gray-600">
                <Circle class="h-4 w-4 mr-2" />
                <span>Módulo de gestión de clientes</span>
              </div>
              <div class="flex items-center text-gray-600">
                <Circle class="h-4 w-4 mr-2" />
                <span>Catálogo de productos</span>
              </div>
              <div class="flex items-center text-gray-600">
                <Circle class="h-4 w-4 mr-2" />
                <span>CRUD completo para ambos módulos</span>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </main>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { Card, CardHeader, CardTitle, CardContent, Button } from '@/Components/ui'
import {
  Check as CheckIcon,
  LogOut,
  Users,
  Package,
  ShoppingCart,
  DollarSign,
  Circle
} from 'lucide-vue-next'

defineProps({
  user: Object,
})

const logoutForm = useForm({})

const logout = () => {
  logoutForm.post('/logout')
}
</script>