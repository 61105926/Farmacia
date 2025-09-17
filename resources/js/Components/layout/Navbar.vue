<template>
  <header class="bg-white shadow-sm border-b border-gray-200">
    <div class="flex items-center justify-between px-6 py-4">
      <!-- Left Section -->
      <div class="flex items-center space-x-4">
        <!-- Sidebar Toggle -->
        <button
          @click="$emit('toggleSidebar')"
          class="p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-300 transition-colors"
        >
          <Menu class="w-5 h-5" />
        </button>

        <!-- Breadcrumb -->
        <nav class="hidden sm:flex items-center space-x-2 text-sm">
          <Link
            href="/dashboard"
            class="text-gray-500 hover:text-gray-700 transition-colors"
          >
            Dashboard
          </Link>
          <ChevronRight class="w-4 h-4 text-gray-400" v-if="breadcrumb" />
          <span class="text-gray-900 font-medium" v-if="breadcrumb">{{ breadcrumb }}</span>
        </nav>
      </div>

      <!-- Center Section - Search -->
      <div class="flex-1 max-w-lg mx-4">
        <div class="relative">
          <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
          <input
            type="text"
            placeholder="Buscar productos, clientes, ventas..."
            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 text-sm"
            v-model="searchQuery"
            @keyup.enter="handleSearch"
          />
        </div>
      </div>

      <!-- Right Section -->
      <div class="flex items-center space-x-4">
        <!-- Notifications -->
        <div class="relative">
          <button
            class="p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-300 transition-colors relative"
            @click="toggleNotifications"
          >
            <Bell class="w-5 h-5" />
            <span
              v-if="unreadNotifications > 0"
              class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center"
            >
              {{ unreadNotifications > 9 ? '9+' : unreadNotifications }}
            </span>
          </button>

          <!-- Notifications Dropdown -->
          <div
            v-if="showNotifications"
            @click.outside="closeNotifications"
            class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
          >
            <div class="p-4 border-b border-gray-200">
              <h3 class="text-sm font-semibold text-gray-900">Notificaciones</h3>
            </div>
            <div class="max-h-80 overflow-y-auto">
              <div v-if="notifications.length === 0" class="p-4 text-center text-gray-500 text-sm">
                No hay notificaciones
              </div>
              <div v-else>
                <div
                  v-for="notification in notifications"
                  :key="notification.id"
                  class="p-4 border-b border-gray-100 hover:bg-gray-50 cursor-pointer"
                  @click="markAsRead(notification.id)"
                >
                  <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                      <div
                        class="w-2 h-2 rounded-full"
                        :class="notification.read ? 'bg-gray-300' : 'bg-blue-500'"
                      ></div>
                    </div>
                    <div class="flex-1 min-w-0">
                      <p class="text-sm font-medium text-gray-900">{{ notification.title }}</p>
                      <p class="text-sm text-gray-500 truncate">{{ notification.message }}</p>
                      <p class="text-xs text-gray-400 mt-1">{{ formatTime(notification.created_at) }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="p-3 border-t border-gray-200">
              <Link
                href="/notificaciones"
                class="text-sm text-blue-600 hover:text-blue-700 font-medium"
              >
                Ver todas las notificaciones
              </Link>
            </div>
          </div>
        </div>

        <!-- Settings -->
        <button
          @click="toggleSettings"
          class="p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-300 transition-colors"
        >
          <Settings class="w-5 h-5" />
        </button>

        <!-- User Menu -->
        <div class="relative">
          <button
            @click="toggleUserMenu"
            class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-300 transition-colors"
          >
            <Avatar :user="user" size="sm" />
            <div class="hidden md:block text-left">
              <p class="text-sm font-medium text-gray-900">{{ user.name }}</p>
              <p class="text-xs text-gray-500">{{ user.roles?.[0]?.name }}</p>
            </div>
            <ChevronDown class="w-4 h-4 text-gray-400" />
          </button>

          <!-- User Dropdown -->
          <div
            v-if="showUserMenu"
            @click.outside="closeUserMenu"
            class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
          >
            <div class="p-4 border-b border-gray-200">
              <div class="flex items-center space-x-3">
                <Avatar :user="user" size="md" />
                <div>
                  <p class="text-sm font-medium text-gray-900">{{ user.name }}</p>
                  <p class="text-xs text-gray-500">{{ user.email }}</p>
                </div>
              </div>
            </div>
            <div class="py-2">
              <Link
                href="/perfil"
                class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
              >
                <User class="w-4 h-4 mr-3" />
                Mi Perfil
              </Link>
              <Link
                href="/configuracion"
                class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
              >
                <Settings class="w-4 h-4 mr-3" />
                Configuración
              </Link>
              <div class="border-t border-gray-100 mt-2 pt-2">
                <button
                  @click="handleLogout"
                  class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors"
                >
                  <LogOut class="w-4 h-4 mr-3" />
                  Cerrar Sesión
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Settings Modal -->
    <Modal v-model="showSettingsModal" title="Configuración del Sistema">
      <div class="space-y-6">
        <!-- Theme Settings -->
        <div>
          <h3 class="text-lg font-medium text-gray-900 mb-4">Apariencia</h3>
          <div class="space-y-4">
            <div>
              <label class="text-sm font-medium text-gray-700">Tema</label>
              <div class="mt-2 grid grid-cols-3 gap-3">
                <button
                  v-for="theme in themes"
                  :key="theme.value"
                  @click="setTheme(theme.value)"
                  class="relative rounded-lg border p-4 text-center hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  :class="[
                    currentTheme === theme.value
                      ? 'border-blue-600 ring-2 ring-blue-600'
                      : 'border-gray-300'
                  ]"
                >
                  <component :is="theme.icon" class="mx-auto h-6 w-6 mb-2" :class="theme.iconColor" />
                  <div class="text-sm font-medium text-gray-900">{{ theme.name }}</div>
                  <div class="text-xs text-gray-500">{{ theme.description }}</div>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Language Settings -->
        <div>
          <h3 class="text-lg font-medium text-gray-900 mb-4">Idioma</h3>
          <select
            v-model="currentLanguage"
            @change="setLanguage"
            class="block w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500"
          >
            <option value="es">Español</option>
            <option value="en">English</option>
          </select>
        </div>

      </div>

      <template #footer>
        <div class="flex justify-end space-x-3">
          <button
            @click="showSettingsModal = false"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Cancelar
          </button>
          <button
            @click="saveSettings"
            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Guardar Configuración
          </button>
        </div>
      </template>
    </Modal>
  </header>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import {
  Menu,
  Search,
  Bell,
  Settings,
  ChevronRight,
  ChevronDown,
  User,
  LogOut,
  Sun,
  Moon,
  Monitor
} from 'lucide-vue-next'
import Avatar from '@/Components/ui/Avatar.vue'
import Modal from '@/Components/ui/Modal.vue'

defineProps({
  sidebarCollapsed: {
    type: Boolean,
    default: false
  }
})

defineEmits(['toggleSidebar', 'logout'])

const page = usePage()
const user = computed(() => page.props.auth.user)

// Search
const searchQuery = ref('')
const handleSearch = () => {
  if (searchQuery.value.trim()) {
    // Implement search functionality
    console.log('Searching for:', searchQuery.value)
  }
}

// Notifications
const showNotifications = ref(false)
const notifications = ref([
  {
    id: 1,
    title: 'Nuevo pedido recibido',
    message: 'Cliente ABC acaba de realizar un pedido por $2,500',
    created_at: '2025-09-17T10:30:00Z',
    read: false
  },
  {
    id: 2,
    title: 'Stock bajo',
    message: 'Paracetamol 500mg tiene solo 10 unidades en stock',
    created_at: '2025-09-17T09:15:00Z',
    read: false
  }
])

const unreadNotifications = computed(() =>
  notifications.value.filter(n => !n.read).length
)

const toggleNotifications = () => {
  showNotifications.value = !showNotifications.value
}

const closeNotifications = () => {
  showNotifications.value = false
}

const markAsRead = (id) => {
  const notification = notifications.value.find(n => n.id === id)
  if (notification) {
    notification.read = true
  }
}

// User Menu
const showUserMenu = ref(false)
const toggleUserMenu = () => {
  showUserMenu.value = !showUserMenu.value
}

const closeUserMenu = () => {
  showUserMenu.value = false
}

// Logout
const handleLogout = () => {
  showUserMenu.value = false
  if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
    const form = document.createElement('form')
    form.method = 'POST'
    form.action = '/logout'

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    if (csrfToken) {
      const tokenInput = document.createElement('input')
      tokenInput.type = 'hidden'
      tokenInput.name = '_token'
      tokenInput.value = csrfToken
      form.appendChild(tokenInput)
    }

    document.body.appendChild(form)
    form.submit()
  }
}

// Breadcrumb
const breadcrumb = computed(() => {
  const component = page.component
  const breadcrumbs = {
    'Dashboard': null,
    'Users/Index': 'Usuarios',
    'Clients/Index': 'Clientes',
    'Products/Index': 'Productos',
    'Inventory/Index': 'Inventario',
    'Presales/Index': 'Preventas',
    'Sales/Index': 'Ventas',
    'Receivables/Index': 'Cuentas por Cobrar',
    'Reports/Index': 'Reportes',
    'Settings/Index': 'Configuración'
  }
  return breadcrumbs[component] || null
})

// Utility functions
const formatTime = (timestamp) => {
  const date = new Date(timestamp)
  const now = new Date()
  const diff = now - date

  if (diff < 60000) return 'Hace un momento'
  if (diff < 3600000) return `Hace ${Math.floor(diff / 60000)} min`
  if (diff < 86400000) return `Hace ${Math.floor(diff / 3600000)} h`
  return date.toLocaleDateString()
}

// Settings Modal
const showSettingsModal = ref(false)
const currentTheme = ref(localStorage.getItem('theme') || 'light')
const currentLanguage = ref(localStorage.getItem('language') || 'es')

const themes = [
  {
    value: 'light',
    name: 'Claro',
    description: 'Tema claro',
    icon: Sun,
    iconColor: 'text-yellow-500'
  },
  {
    value: 'dark',
    name: 'Oscuro',
    description: 'Tema oscuro',
    icon: Moon,
    iconColor: 'text-gray-700'
  },
  {
    value: 'auto',
    name: 'Automático',
    description: 'Sigue el sistema',
    icon: Monitor,
    iconColor: 'text-blue-500'
  }
]

const toggleSettings = () => {
  showSettingsModal.value = true
}

const setTheme = (theme) => {
  currentTheme.value = theme
  localStorage.setItem('theme', theme)

  // Apply theme logic
  const root = document.documentElement
  if (theme === 'dark') {
    root.classList.add('dark')
    window.$notify?.success('Tema actualizado', 'Modo oscuro activado')
  } else if (theme === 'light') {
    root.classList.remove('dark')
    window.$notify?.success('Tema actualizado', 'Modo claro activado')
  } else {
    // Auto theme - follow system preference
    if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
      root.classList.add('dark')
    } else {
      root.classList.remove('dark')
    }
    window.$notify?.success('Tema actualizado', 'Modo automático activado')
  }
}

const setLanguage = () => {
  localStorage.setItem('language', currentLanguage.value)
  // Here you would implement language switching logic
  window.$notify?.info('Idioma actualizado', `Idioma cambiado a ${currentLanguage.value === 'es' ? 'Español' : 'English'}`)
}


const saveSettings = () => {
  showSettingsModal.value = false
  window.$notify?.success('Configuración guardada', 'La configuración se ha guardado correctamente')
}

// Click outside directive
const vClickOutside = {
  mounted(el, binding) {
    const handler = (e) => {
      if (!el.contains(e.target)) {
        binding.value()
      }
    }
    document.addEventListener('click', handler)
    el._clickOutside = handler
  },
  unmounted(el) {
    document.removeEventListener('click', el._clickOutside)
  }
}
</script>