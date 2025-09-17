<template>
  <aside class="bg-white shadow-lg border-r border-gray-200 flex flex-col">
    <!-- Logo Area -->
    <div class="flex items-center justify-between p-4 border-b border-gray-200">
      <div class="flex items-center space-x-2" v-if="!isCollapsed">
        <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
          <Pill class="w-5 h-5 text-white" />
        </div>
        <div>
          <h1 class="text-lg font-bold text-gray-900">Farmacia ERP</h1>
          <p class="text-xs text-gray-500">Sistema de Gestión</p>
        </div>
      </div>
      <div class="flex items-center justify-center w-8 h-8" v-else>
        <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
          <Pill class="w-5 h-5 text-white" />
        </div>
      </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 overflow-y-auto py-4">
      <div class="px-3">
        <!-- Main Menu -->
        <div class="mb-6">
          <h3 v-if="!isCollapsed" class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
            Principal
          </h3>
          <ul class="space-y-1">
            <SidebarItem
              :href="route('dashboard')"
              :active="$page.component === 'Dashboard'"
              :collapsed="isCollapsed"
              icon="LayoutDashboard"
              label="Dashboard"
            />
          </ul>
        </div>

        <!-- Gestión -->
        <div class="mb-6">
          <h3 v-if="!isCollapsed" class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
            Gestión
          </h3>
          <ul class="space-y-1">
            <SidebarItem
              href="/usuarios"
              :active="$page.url.startsWith('/usuarios')"
              :collapsed="isCollapsed"
              icon="Users"
              label="Usuarios"
              :permissions="['users.view']"
            />
            <SidebarItem
              href="/clientes"
              :active="$page.url.startsWith('/clientes')"
              :collapsed="isCollapsed"
              icon="UserCheck"
              label="Clientes"
              :permissions="['clients.view']"
            />
            <SidebarItem
              href="/productos"
              :active="$page.url.startsWith('/productos')"
              :collapsed="isCollapsed"
              icon="Package"
              label="Productos"
              :permissions="['products.view']"
            />
          </ul>
        </div>

        <!-- Operaciones -->
        <div class="mb-6">
          <h3 v-if="!isCollapsed" class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
            Operaciones
          </h3>
          <ul class="space-y-1">
            <SidebarItem
              href="/inventario"
              :active="$page.url.startsWith('/inventario')"
              :collapsed="isCollapsed"
              icon="Warehouse"
              label="Inventario"
              :permissions="['inventory.view']"
            />
            <SidebarItem
              href="/preventas"
              :active="$page.url.startsWith('/preventas')"
              :collapsed="isCollapsed"
              icon="FileText"
              label="Preventas"
              :permissions="['presales.view']"
            />
            <SidebarItem
              href="/ventas"
              :active="$page.url.startsWith('/ventas')"
              :collapsed="isCollapsed"
              icon="ShoppingCart"
              label="Ventas"
              :permissions="['sales.view']"
            />
            <SidebarItem
              href="/cuentas-por-cobrar"
              :active="$page.url.startsWith('/cuentas-por-cobrar')"
              :collapsed="isCollapsed"
              icon="CreditCard"
              label="Cuentas por Cobrar"
              :permissions="['receivables.view']"
            />
          </ul>
        </div>

        <!-- Reportes -->
        <div class="mb-6">
          <h3 v-if="!isCollapsed" class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
            Análisis
          </h3>
          <ul class="space-y-1">
            <SidebarItem
              href="/reportes"
              :active="$page.url.startsWith('/reportes')"
              :collapsed="isCollapsed"
              icon="BarChart"
              label="Reportes"
              :permissions="['reports.view']"
            />
            <SidebarItem
              href="/configuracion"
              :active="$page.url.startsWith('/configuracion')"
              :collapsed="isCollapsed"
              icon="Settings"
              label="Configuración"
              :permissions="['settings.view']"
            />
          </ul>
        </div>
      </div>
    </nav>

    <!-- Footer -->
    <div class="p-4 border-t border-gray-200">
      <div v-if="!isCollapsed" class="text-center">
        <p class="text-xs text-gray-500">Versión 1.0</p>
        <p class="text-xs text-gray-400">Farmacia ERP</p>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { Pill } from 'lucide-vue-next'
import SidebarItem from './SidebarItem.vue'

defineProps({
  isCollapsed: {
    type: Boolean,
    default: false
  }
})

defineEmits(['toggle'])

const route = (name) => {
  if (name === 'dashboard') return '/dashboard'
  return '/' + name
}
</script>