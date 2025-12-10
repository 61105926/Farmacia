<template>
  <header class="bg-gradient-to-r from-accent-500 to-accent-600 shadow-sm border-b border-accent-700">
    <div class="flex items-center justify-between px-6 py-4">
      <!-- Left Section -->
      <div class="flex items-center space-x-4">
        <!-- Sidebar Toggle -->
        <button
          @click="$emit('toggleSidebar')"
          class="p-2 rounded-lg text-primary-800 hover:text-primary-900 hover:bg-accent-400 focus:outline-none focus:ring-2 focus:ring-primary-700 transition-colors"
        >
          <Menu class="w-5 h-5" />
        </button>

        <!-- Logo Branding (hidden on mobile) -->
        <div class="hidden lg:flex items-center">
          <img
            :src="logoIcon"
            alt="Farmacia Logo"
            class="h-8 w-8 object-contain rounded-lg"
          />
        </div>

        <!-- Breadcrumb -->
        <nav class="hidden sm:flex items-center space-x-2 text-sm">
          <Link
            href="/dashboard"
            class="text-primary-800 hover:text-primary-900 transition-colors font-medium"
          >
            Dashboard
          </Link>
          <ChevronRight class="w-4 h-4 text-primary-700" v-if="breadcrumb" />
          <span class="text-primary-900 font-semibold" v-if="breadcrumb">{{ breadcrumb }}</span>
        </nav>
      </div>

      <!-- Center Section - System Title -->
      <div class="flex-1 max-w-2xl mx-4">
        <div class="text-center">
          <p class="text-2xl font-bold text-primary-600 uppercase tracking-wide">
            SISPANDO
          </p>
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
              v-if="unreadCount > 0"
              class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center"
            >
              {{ unreadCount > 9 ? '9+' : unreadCount }}
            </span>
          </button>

          <!-- Notifications Dropdown -->
          <div
            v-if="showNotifications"
            @click.outside="closeNotifications"
            class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
          >
            <div class="p-4 border-b border-gray-200 flex items-center justify-between">
              <h3 class="text-sm font-semibold text-gray-900">Notificaciones</h3>
              <button
                v-if="unreadNotifications > 0"
                @click="markAllAsRead"
                class="text-xs text-blue-600 hover:text-blue-700 font-medium"
              >
                Marcar todas como leídas
              </button>
            </div>
            <div class="max-h-80 overflow-y-auto">
              <div v-if="loadingNotifications" class="p-4 text-center text-gray-500 text-sm">
                Cargando...
              </div>
              <div v-else-if="notifications.length === 0" class="p-4 text-center text-gray-500 text-sm">
                No hay notificaciones
              </div>
              <div v-else>
                <div
                  v-for="notification in notifications"
                  :key="notification.id"
                  class="p-4 border-b border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors"
                  @click="markAsRead(notification)"
                >
                  <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                      <div
                        class="w-2 h-2 rounded-full mt-2"
                        :class="notification.read ? 'bg-gray-300' : 'bg-blue-500'"
                      ></div>
                    </div>
                    <div class="flex-1 min-w-0">
                      <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-gray-900">{{ notification.title }}</p>
                        <p v-if="notification.user && isAdmin" class="text-xs text-gray-400">
                          {{ notification.user.name }}
                        </p>
                      </div>
                      <p class="text-sm text-gray-500 truncate">{{ notification.message }}</p>
                      <p class="text-xs text-gray-400 mt-1">{{ formatTime(notification.created_at) }}</p>
                    </div>
                  </div>
                </div>
              </div>
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
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import axios from 'axios'
import {
  Menu,
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
import logoIconImg from '@/../assets/images/logo.jpeg'

const logoIcon = logoIconImg

defineProps({
  sidebarCollapsed: {
    type: Boolean,
    default: false
  }
})

defineEmits(['toggleSidebar', 'logout'])

const page = usePage()
const user = computed(() => page.props.auth.user)
const isAdmin = computed(() => {
  const roles = page.props.auth?.roles || []
  return roles.some(role => ['super-admin', 'Administrador', 'administrador'].includes(role))
})

// Notifications
const showNotifications = ref(false)
const notifications = ref([])
const loadingNotifications = ref(false)
let notificationInterval = null

// Obtener notificaciones del backend
const fetchNotifications = async () => {
  if (!user.value) return
  
  try {
    loadingNotifications.value = true
    const response = await axios.get('/api/notifications')
    notifications.value = response.data.notifications || []
  } catch (error) {
    console.error('Error al obtener notificaciones:', error)
  } finally {
    loadingNotifications.value = false
  }
}

// Obtener notificaciones iniciales desde props
const initialNotifications = computed(() => page.props.notifications?.recent || [])
const unreadCount = computed(() => page.props.notifications?.unread_count || 0)

// Sincronizar notificaciones iniciales
watch(initialNotifications, (newNotifications) => {
  if (newNotifications && newNotifications.length > 0) {
    notifications.value = newNotifications
  }
}, { immediate: true })

const unreadNotifications = computed(() =>
  notifications.value.filter(n => !n.read).length
)

const toggleNotifications = async () => {
  showNotifications.value = !showNotifications.value
  if (showNotifications.value) {
    // Recargar notificaciones al abrir el dropdown
    await fetchNotifications()
  }
}

const closeNotifications = () => {
  showNotifications.value = false
}

const markAsRead = async (notification) => {
  if (notification.read) return
  
  try {
    await axios.post(`/api/notifications/${notification.id}/read`)
    notification.read = true
    // Actualizar contador
    await fetchNotifications()
  } catch (error) {
    console.error('Error al marcar notificación como leída:', error)
  }
}

const markAllAsRead = async () => {
  try {
    await axios.post('/api/notifications/read-all')
    notifications.value.forEach(n => n.read = true)
    await fetchNotifications()
  } catch (error) {
    console.error('Error al marcar todas como leídas:', error)
  }
}

// Actualizar notificaciones periódicamente cada 30 segundos
onMounted(() => {
  if (user.value) {
    fetchNotifications()
    notificationInterval = setInterval(fetchNotifications, 30000) // 30 segundos
  }
})

onUnmounted(() => {
  if (notificationInterval) {
    clearInterval(notificationInterval)
  }
})

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
    router.post('/logout')
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

  // Usar la función global applyTheme si está disponible
  if (window.applyTheme) {
    window.applyTheme(theme)
  } else {
    // Fallback: Apply theme logic - modificar tanto root como body
    const root = document.documentElement
    const body = document.body
    
    // Siempre remover primero
    root.classList.remove('dark')
    body.classList.remove('dark')
    
    if (theme === 'dark') {
      root.classList.add('dark')
      body.classList.add('dark')
    } else if (theme === 'light') {
      // Asegurarse de que se remueve la clase dark
      root.classList.remove('dark')
      body.classList.remove('dark')
    } else {
      // Auto theme - follow system preference
      const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
      if (prefersDark) {
        root.classList.add('dark')
        body.classList.add('dark')
      } else {
        root.classList.remove('dark')
        body.classList.remove('dark')
      }
    }
  }
  
  // Mostrar notificación
  const messages = {
    dark: 'Modo oscuro activado',
    light: 'Modo claro activado',
    auto: 'Modo automático activado'
  }
  window.$notify?.success('Tema actualizado', messages[theme] || 'Tema actualizado')
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