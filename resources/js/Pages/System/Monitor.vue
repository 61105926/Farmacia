<template>
  <AdminLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Monitor del Sistema</h1>
          <p class="text-sm text-gray-600 mt-1">Monitoreo y optimización del sistema</p>
        </div>
        <div class="flex gap-2">
          <button
            @click="clearCache"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
          >
            Limpiar Caché
          </button>
          <button
            @click="optimizeDatabase"
            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors"
          >
            Optimizar BD
          </button>
        </div>
      </div>
    </template>

    <div class="space-y-6">
      <!-- Estadísticas del Sistema -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <Card>
          <CardContent class="p-6">
            <div class="flex items-center">
              <Users class="h-8 w-8 text-blue-600" />
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Usuarios</p>
                <p class="text-2xl font-bold text-gray-900">{{ stats.total_users || 0 }}</p>
                <p class="text-xs text-green-600">Activos: {{ stats.active_users || 0 }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-6">
            <div class="flex items-center">
              <Building2 class="h-8 w-8 text-green-600" />
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Clientes</p>
                <p class="text-2xl font-bold text-gray-900">{{ stats.total_clients || 0 }}</p>
                <p class="text-xs text-green-600">Activos: {{ stats.active_clients || 0 }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-6">
            <div class="flex items-center">
              <Package class="h-8 w-8 text-purple-600" />
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Productos</p>
                <p class="text-2xl font-bold text-gray-900">{{ stats.total_products || 0 }}</p>
                <p class="text-xs text-green-600">Activos: {{ stats.active_products || 0 }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-6">
            <div class="flex items-center">
              <Database class="h-8 w-8 text-orange-600" />
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Base de Datos</p>
                <p class="text-2xl font-bold text-gray-900">{{ stats.database_size || 'N/A' }}</p>
                <p class="text-xs text-gray-500">Tamaño total</p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Métricas de Rendimiento -->
      <Card>
        <CardHeader>
          <CardTitle>Métricas de Rendimiento</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="text-center">
              <p class="text-sm text-gray-600">Memoria en Uso</p>
              <p class="text-2xl font-bold text-blue-600">{{ performance.memory_usage?.toFixed(2) || 0 }} MB</p>
            </div>
            <div class="text-center">
              <p class="text-sm text-gray-600">Memoria Pico</p>
              <p class="text-2xl font-bold text-red-600">{{ performance.memory_peak?.toFixed(2) || 0 }} MB</p>
            </div>
            <div class="text-center">
              <p class="text-sm text-gray-600">Tiempo de Ejecución</p>
              <p class="text-2xl font-bold text-green-600">{{ performance.execution_time?.toFixed(3) || 0 }}s</p>
            </div>
          </div>
          <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <p class="text-sm text-gray-600">PHP Version</p>
              <p class="text-lg font-semibold">{{ performance.php_version || 'N/A' }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Laravel Version</p>
              <p class="text-lg font-semibold">{{ performance.laravel_version || 'N/A' }}</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Errores Recientes -->
      <Card>
        <CardHeader>
          <CardTitle>Errores Recientes</CardTitle>
        </CardHeader>
        <CardContent>
          <div v-if="errors.length === 0" class="text-center py-8">
            <CheckCircle class="h-12 w-12 text-green-500 mx-auto mb-4" />
            <p class="text-gray-600">No hay errores recientes</p>
          </div>
          <div v-else class="space-y-3">
            <div
              v-for="error in errors"
              :key="error.timestamp"
              class="bg-red-50 border border-red-200 rounded-md p-3"
            >
              <div class="flex items-start">
                <AlertCircle class="h-5 w-5 text-red-400 mt-0.5" />
                <div class="ml-3 flex-1">
                  <p class="text-sm text-red-800">{{ error.message }}</p>
                  <p class="text-xs text-red-600 mt-1">{{ error.timestamp }}</p>
                </div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Actividad de Usuarios -->
      <Card>
        <CardHeader>
          <CardTitle>Actividad Reciente de Usuarios</CardTitle>
        </CardHeader>
        <CardContent>
          <div v-if="users.length === 0" class="text-center py-8">
            <Users class="h-12 w-12 text-gray-400 mx-auto mb-4" />
            <p class="text-gray-600">No hay actividad reciente</p>
          </div>
          <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Usuario
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Email
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Último Acceso
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Registrado
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="user in users" :key="user.id">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ user.name }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ user.email }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ formatDate(user.last_login_at) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ formatDate(user.created_at) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Card from '@/Components/ui/Card.vue'
import CardContent from '@/Components/ui/CardContent.vue'
import CardHeader from '@/Components/ui/CardHeader.vue'
import CardTitle from '@/Components/ui/CardTitle.vue'
import { 
  Users, 
  Building2, 
  Package, 
  Database, 
  CheckCircle, 
  AlertCircle 
} from 'lucide-vue-next'

const props = defineProps({
  stats: Object,
  performance: Object,
  errors: Array,
  users: Array
})

const formatDate = (date) => {
  if (!date) return 'Nunca'
  return new Date(date).toLocaleDateString('es-BO')
}

const clearCache = () => {
  if (confirm('¿Estás seguro de que quieres limpiar el caché del sistema?')) {
    router.post('/sistema/limpiar-cache')
  }
}

const optimizeDatabase = () => {
  if (confirm('¿Estás seguro de que quieres optimizar la base de datos? Esto puede tomar varios minutos.')) {
    router.post('/sistema/optimizar-bd')
  }
}
</script>
