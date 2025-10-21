<template>
  <aside class="bg-gradient-to-b from-primary-700 to-primary-900 shadow-lg border-r border-primary-800 flex flex-col">
    <!-- Logo Area -->
    <div class="flex items-center justify-between p-4 border-b border-primary-800 bg-accent-500">
      <div class="flex items-center space-x-2" v-if="!isCollapsed">
        <img
          :src="logoNombre"
          alt="Farmacia Pando Central"
          class="h-12 object-contain"
        />
      </div>
      <div class="flex items-center justify-center" v-else>
        <img
          :src="logoIcon"
          alt="Farmacia Logo"
          class="w-10 h-10 object-contain rounded-lg"
        />
      </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 overflow-y-auto py-4">
      <div class="px-3">
        <!-- Main Menu -->
        <div class="mb-6">
          <h3 v-if="!isCollapsed" class="px-3 text-xs font-semibold text-accent-400 uppercase tracking-wider mb-2">
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
          <h3 v-if="!isCollapsed" class="px-3 text-xs font-semibold text-accent-400 uppercase tracking-wider mb-2">
            Gestión
          </h3>
          <ul class="space-y-1">
            <SidebarItem
              href="/usuarios"
              :active="$page.url.startsWith('/usuarios')"
              :collapsed="isCollapsed"
              icon="Users"
              label="Usuarios"
              :permissions="['users.index']"
            />
            <SidebarItem
              href="/clientes"
              :active="$page.url.startsWith('/clientes')"
              :collapsed="isCollapsed"
              icon="UserCheck"
              label="Clientes"
              :permissions="['clients.index']"
            />
            <SidebarItem
              href="/productos"
              :active="$page.url.startsWith('/productos')"
              :collapsed="isCollapsed"
              icon="Package"
              label="Productos"
              :permissions="['products.index']"
            />
          </ul>
        </div>

        <!-- Operaciones -->
        <div class="mb-6">
          <h3 v-if="!isCollapsed" class="px-3 text-xs font-semibold text-accent-400 uppercase tracking-wider mb-2">
            Operaciones
          </h3>
          <ul class="space-y-1">
            <SidebarItem
              href="/inventario"
              :active="$page.url.startsWith('/inventario')"
              :collapsed="isCollapsed"
              icon="Warehouse"
              label="Inventario"
              :permissions="['inventory.index']"
            />
            <SidebarItem
              href="/preventas"
              :active="$page.url.startsWith('/preventas')"
              :collapsed="isCollapsed"
              icon="FileText"
              label="Preventas"
              :permissions="['presales.index']"
            />
            <SidebarItem
              href="/ventas"
              :active="$page.url.startsWith('/ventas')"
              :collapsed="isCollapsed"
              icon="ShoppingCart"
              label="Ventas"
              :permissions="['sales.index']"
            />
            <SidebarItem
              href="/cuentas-por-cobrar"
              :active="$page.url.startsWith('/cuentas-por-cobrar')"
              :collapsed="isCollapsed"
              icon="CreditCard"
              label="Cuentas por Cobrar"
              :permissions="['receivables.index']"
            />
          </ul>
        </div>

        <!-- Reportes -->
        <div class="mb-6">
          <h3 v-if="!isCollapsed" class="px-3 text-xs font-semibold text-accent-400 uppercase tracking-wider mb-2">
            Análisis
          </h3>
          <ul class="space-y-1">
            <SidebarItem
              href="/reportes"
              :active="$page.url.startsWith('/reportes')"
              :collapsed="isCollapsed"
              icon="BarChart"
              label="Reportes"
              :permissions="['reports.index']"
            />
            <SidebarItem
              href="/configuracion"
              :active="$page.url.startsWith('/configuracion')"
              :collapsed="isCollapsed"
              icon="Settings"
              label="Configuración"
              :permissions="['config.index']"
            />
            <SidebarItem
              href="/sistema/monitor"
              :active="$page.url.startsWith('/sistema')"
              :collapsed="isCollapsed"
              icon="Monitor"
              label="Monitor Sistema"
              :permissions="['system.monitor']"
            />
          </ul>
        </div>
      </div>
    </nav>

    <!-- Footer -->
    <div class="p-4 border-t border-primary-800 bg-primary-800">
      <div v-if="!isCollapsed" class="text-center">
        <p class="text-xs text-accent-400">Versión 1.0</p>
        <p class="text-xs text-accent-300">Farmacia ERP</p>
      </div>
    </div>
  </aside>
</template>

<script setup>
import SidebarItem from './SidebarItem.vue'
import logoNombreImg from '@/../assets/images/logo-nombre.jpeg'
import logoIconImg from '@/../assets/images/logo.jpeg'

defineProps({
  isCollapsed: {
    type: Boolean,
    default: false
  }
})

defineEmits(['toggle'])

const logoNombre = logoNombreImg
const logoIcon = logoIconImg

const route = (name) => {
  if (name === 'dashboard') return '/dashboard'
  return '/' + name
}
</script>