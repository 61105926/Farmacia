<template>
  <AdminLayout>
    <template #header>
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Monitor del Sistema</h1>
        <p class="text-sm text-gray-600 mt-1">Monitoreo y optimización del sistema</p>
      </div>
    </template>

    <div class="space-y-6">
      <!-- Estadísticas del Sistema -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
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
      </div>

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
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Card from '@/Components/ui/Card.vue'
import CardContent from '@/Components/ui/CardContent.vue'
import CardHeader from '@/Components/ui/CardHeader.vue'
import CardTitle from '@/Components/ui/CardTitle.vue'
import { 
  Users, 
  Building2, 
  Package, 
  CheckCircle, 
  AlertCircle 
} from 'lucide-vue-next'

const props = defineProps({
  stats: Object,
  errors: Array,
  users: Array
})

const formatDate = (date) => {
  if (!date) return 'Nunca'
  const parsed = new Date(String(date).replace(' ', 'T'))
  return isNaN(parsed) ? 'Nunca' : parsed.toLocaleDateString('es-BO')
}
</script>
