<template>
  <AdminLayout>
    <!-- Page Header -->
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
          <p class="text-gray-600 mt-1">Bienvenido al sistema de gestión farmacéutica</p>
        </div>
        <div class="flex items-center space-x-4">
          <Badge variant="success" size="md">
            <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
            Sistema Activo
          </Badge>
        </div>
      </div>
    </template>

    <!-- Dashboard Content -->
    <div class="space-y-6">
      <!-- Welcome Message -->
      <Card>
        <CardHeader>
          <CardTitle>Bienvenido al Sistema ERP</CardTitle>
        </CardHeader>
        <CardContent>
          <!-- <p class="text-gray-600 mb-4">
            Sistema completamente funcional con diseño profesional. Funcionalidades implementadas:
          </p> -->
    
        </CardContent>
      </Card>

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

     
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardHeader, CardTitle, CardContent, Button, Badge } from '@/Components/ui'
import {
  Check as CheckIcon,
  Users,
  Package,
  ShoppingCart,
  DollarSign,
  Circle
} from 'lucide-vue-next'

defineProps({
  user: Object,
})

const testNotification = () => {
  if (window.$notify) {
    window.$notify.success('¡Éxito!', 'El sistema de notificaciones está funcionando correctamente.')
  }
}
</script>