<template>
  <AdminLayout>
    <div class="p-6">
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Configuración</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
          Personaliza tu experiencia en el sistema
        </p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Sidebar de navegación -->
        <div class="lg:col-span-1">
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <nav class="space-y-2">
              <button
                v-for="section in sections"
                :key="section.id"
                @click="activeSection = section.id"
                :class="[
                  'w-full text-left px-4 py-2 rounded-md transition-colors',
                  activeSection === section.id
                    ? 'bg-primary-600 text-white'
                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                ]"
              >
                <div class="flex items-center gap-3">
                  <component :is="section.icon" class="w-5 h-5" />
                  <span>{{ section.name }}</span>
                </div>
              </button>
            </nav>
          </div>
        </div>

        <!-- Contenido principal -->
        <div class="lg:col-span-2">
          <!-- Apariencia -->
          <div v-if="activeSection === 'appearance'" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Apariencia</h2>
            
            <!-- Tema -->
            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                Tema
              </label>
              <div class="grid grid-cols-3 gap-4">
                <button
                  v-for="theme in themes"
                  :key="theme.value"
                  @click="updateTheme(theme.value)"
                  :class="[
                    'relative rounded-lg border-2 p-4 text-center transition-all hover:scale-105',
                    form.theme === theme.value
                      ? 'border-primary-600 bg-primary-50 dark:bg-primary-900/20'
                      : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'
                  ]"
                >
                  <component :is="theme.icon" class="mx-auto h-8 w-8 mb-2" :class="theme.iconColor" />
                  <div class="text-sm font-medium text-gray-900 dark:text-white">{{ theme.name }}</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ theme.description }}</div>
                  <div v-if="form.theme === theme.value" class="absolute top-2 right-2">
                    <Check class="w-5 h-5 text-primary-600" />
                  </div>
                </button>
              </div>
            </div>

            <!-- Idioma -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Idioma
              </label>
              <select
                v-model="form.language"
                @change="saveSettings"
                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500"
              >
                <option value="es">Español</option>
                <option value="en">English</option>
              </select>
            </div>
          </div>

          <!-- Notificaciones -->
          <div v-if="activeSection === 'notifications'" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Notificaciones Push</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
              Activa las notificaciones push para recibir alertas en tiempo real
            </p>

            <div class="space-y-4">
              <!-- Estado de permisos -->
              <div v-if="!pushPermissionGranted && !pushPermissionDenied" class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                <div class="flex items-start gap-3">
                  <Bell class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5" />
                  <div class="flex-1">
                    <h3 class="text-sm font-medium text-blue-900 dark:text-blue-200 mb-1">
                      Permisos de notificación
                    </h3>
                    <p class="text-xs text-blue-700 dark:text-blue-300 mb-3">
                      Para recibir notificaciones push, necesitas permitir las notificaciones en tu navegador.
                    </p>
                    <button
                      @click="requestPushPermission"
                      :disabled="requestingPermission"
                      class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-md transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      {{ requestingPermission ? 'Solicitando...' : 'Activar Notificaciones' }}
                    </button>
                  </div>
                </div>
              </div>

              <!-- Permiso denegado -->
              <div v-if="pushPermissionDenied" class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                <div class="flex items-start gap-3">
                  <Bell class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5" />
                  <div class="flex-1">
                    <h3 class="text-sm font-medium text-red-900 dark:text-red-200 mb-1">
                      Permisos denegados
                    </h3>
                    <p class="text-xs text-red-700 dark:text-red-300">
                      Las notificaciones están bloqueadas. Por favor, habilítalas en la configuración de tu navegador.
                    </p>
                  </div>
                </div>
              </div>

              <!-- Notificaciones activas -->
              <div v-if="pushPermissionGranted" class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                <div class="flex items-start gap-3">
                  <Bell class="w-5 h-5 text-green-600 dark:text-green-400 mt-0.5" />
                  <div class="flex-1">
                    <h3 class="text-sm font-medium text-green-900 dark:text-green-200 mb-1">
                      Notificaciones activas
                    </h3>
                    <p class="text-xs text-green-700 dark:text-green-300">
                      Recibirás notificaciones push cuando ocurran eventos importantes en el sistema.
                    </p>
                  </div>
                </div>
              </div>

              <!-- Toggle de notificaciones -->
              <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                <label class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer">
                  <div class="flex items-center gap-3">
                    <Bell class="w-5 h-5 text-gray-500" />
                    <div>
                      <div class="text-sm font-medium text-gray-900 dark:text-white">Activar Notificaciones Push</div>
                      <div class="text-xs text-gray-500 dark:text-gray-400">Recibir notificaciones en tiempo real</div>
                    </div>
                  </div>
                  <input
                    type="checkbox"
                    v-model="form.notification_settings.push"
                    @change="saveSettings"
                    :disabled="!pushPermissionGranted"
                    class="rounded border-gray-300 text-primary-600 focus:ring-primary-500 disabled:opacity-50"
                  />
                </label>
              </div>

              <!-- Módulos para notificaciones -->
              <div v-if="pushPermissionGranted && form.notification_settings.push" class="border-t border-gray-200 dark:border-gray-700 pt-4">
                <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Módulos con notificaciones</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                  Selecciona en qué módulos deseas recibir notificaciones
                </p>
                <div class="space-y-3">
                  <label 
                    v-for="module in notificationModules" 
                    :key="module.id"
                    class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer"
                  >
                    <div class="flex items-center gap-3">
                      <component :is="module.icon" class="w-5 h-5 text-gray-500" />
                      <div>
                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ module.name }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ module.description }}</div>
                      </div>
                    </div>
                    <input
                      type="checkbox"
                      v-model="form.notification_settings.modules"
                      :value="module.id"
                      @change="saveSettings"
                      class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                    />
                  </label>
                </div>
              </div>

              <!-- Test de notificación -->
              <div v-if="pushPermissionGranted && form.notification_settings.push" class="pt-4">
                <button
                  @click="testNotification"
                  class="w-full px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm rounded-md transition-colors"
                >
                  Probar Notificación
                </button>
              </div>
            </div>
          </div>

          <!-- Preferencias -->
          <div v-if="activeSection === 'preferences'" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Preferencias</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
              Personaliza cómo se muestra la información en el sistema
            </p>

            <div class="space-y-6">
              <!-- Items por página -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Elementos por página
                </label>
                <select
                  v-model.number="form.preferences.items_per_page"
                  @change="saveSettings"
                  class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500"
                >
                  <option :value="10">10</option>
                  <option :value="15">15</option>
                  <option :value="25">25</option>
                  <option :value="50">50</option>
                  <option :value="100">100</option>
                </select>
              </div>

              <!-- Formato de fecha -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Formato de fecha
                </label>
                <select
                  v-model="form.preferences.date_format"
                  @change="saveSettings"
                  class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500"
                >
                  <option value="d/m/Y">DD/MM/YYYY (10/11/2025)</option>
                  <option value="Y-m-d">YYYY-MM-DD (2025-11-10)</option>
                  <option value="d-m-Y">DD-MM-YYYY (10-11-2025)</option>
                  <option value="m/d/Y">MM/DD/YYYY (11/10/2025)</option>
                </select>
              </div>

              <!-- Formato de hora -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Formato de hora
                </label>
                <select
                  v-model="form.preferences.time_format"
                  @change="saveSettings"
                  class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500"
                >
                  <option value="H:i">24 horas (14:30)</option>
                  <option value="h:i A">12 horas (02:30 PM)</option>
                </select>
              </div>

              <!-- Símbolo de moneda -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Símbolo de moneda
                </label>
                <input
                  type="text"
                  v-model="form.preferences.currency_symbol"
                  @change="saveSettings"
                  class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500"
                  placeholder="Bs"
                />
              </div>

              <!-- Mostrar tooltips -->
              <div>
                <label class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer">
                  <div>
                    <div class="text-sm font-medium text-gray-900 dark:text-white">Mostrar tooltips</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Mostrar información adicional al pasar el mouse</div>
                  </div>
                  <input
                    type="checkbox"
                    v-model="form.preferences.show_tooltips"
                    @change="saveSettings"
                    class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                  />
                </label>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { 
  Palette, 
  Bell, 
  Settings, 
  Sun, 
  Moon, 
  Monitor,
  Check,
  ShoppingCart,
  Package,
  CreditCard,
  BarChart,
  Users,
  UserCheck
} from 'lucide-vue-next'
import { useAlert } from '@/composables/useAlert'

const { showAlert } = useAlert()

const props = defineProps({
  settings: {
    type: Object,
    required: true
  }
})

const activeSection = ref('appearance')

const sections = [
  { id: 'appearance', name: 'Apariencia', icon: Palette },
  { id: 'notifications', name: 'Notificaciones', icon: Bell },
  { id: 'preferences', name: 'Preferencias', icon: Settings },
]

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
    iconColor: 'text-gray-700 dark:text-gray-300'
  },
  {
    value: 'auto',
    name: 'Automático',
    description: 'Sigue el sistema',
    icon: Monitor,
    iconColor: 'text-blue-500'
  }
]

const form = useForm({
  theme: props.settings.theme || 'light',
  language: props.settings.language || 'es',
  notification_settings: {
    push: props.settings.notification_settings?.push ?? false,
    modules: props.settings.notification_settings?.modules ?? [],
  },
  preferences: {
    items_per_page: props.settings.preferences?.items_per_page ?? 15,
    date_format: props.settings.preferences?.date_format ?? 'd/m/Y',
    time_format: props.settings.preferences?.time_format ?? 'H:i',
    currency_symbol: props.settings.preferences?.currency_symbol ?? 'Bs',
    show_tooltips: props.settings.preferences?.show_tooltips ?? true,
  }
})

const notificationModules = [
  {
    id: 'sales',
    name: 'Ventas',
    description: 'Notificaciones sobre nuevas ventas y actualizaciones',
    icon: ShoppingCart
  },
  {
    id: 'presales',
    name: 'Preventas',
    description: 'Notificaciones sobre preventas creadas o confirmadas',
    icon: Package
  },
  {
    id: 'inventory',
    name: 'Inventario',
    description: 'Alertas de stock bajo y movimientos de productos',
    icon: Package
  },
  {
    id: 'payments',
    name: 'Pagos',
    description: 'Notificaciones sobre pagos recibidos y cuentas por cobrar',
    icon: CreditCard
  },
  {
    id: 'clients',
    name: 'Clientes',
    description: 'Notificaciones sobre actualizaciones de clientes',
    icon: UserCheck
  },
  {
    id: 'reports',
    name: 'Reportes',
    description: 'Notificaciones sobre reportes generados',
    icon: BarChart
  }
]

const pushPermissionGranted = ref(false)
const pushPermissionDenied = ref(false)
const requestingPermission = ref(false)

const checkPushPermission = () => {
  if (!('Notification' in window)) {
    return
  }

  if (Notification.permission === 'granted') {
    pushPermissionGranted.value = true
    pushPermissionDenied.value = false
  } else if (Notification.permission === 'denied') {
    pushPermissionGranted.value = false
    pushPermissionDenied.value = true
  } else {
    pushPermissionGranted.value = false
    pushPermissionDenied.value = false
  }
}

const requestPushPermission = async () => {
  if (!('Notification' in window)) {
    showAlert({
      type: 'error',
      title: 'No compatible',
      message: 'Tu navegador no soporta notificaciones push'
    })
    return
  }

  requestingPermission.value = true

  try {
    const permission = await Notification.requestPermission()
    
    if (permission === 'granted') {
      pushPermissionGranted.value = true
      pushPermissionDenied.value = false
      form.notification_settings.push = true
      saveSettings()
      
      showAlert({
        type: 'success',
        title: 'Permisos concedidos',
        message: 'Las notificaciones push están ahora activas'
      })
    } else {
      pushPermissionGranted.value = false
      pushPermissionDenied.value = true
      
      showAlert({
        type: 'error',
        title: 'Permisos denegados',
        message: 'No se pueden mostrar notificaciones sin permisos'
      })
    }
  } catch (error) {
    console.error('Error al solicitar permisos:', error)
    showAlert({
      type: 'error',
      title: 'Error',
      message: 'No se pudieron solicitar los permisos'
    })
  } finally {
    requestingPermission.value = false
  }
}

const testNotification = () => {
  if (!pushPermissionGranted.value || !form.notification_settings.push) {
    return
  }

  if ('Notification' in window && Notification.permission === 'granted') {
    new Notification('Notificación de Prueba', {
      body: '¡Las notificaciones push están funcionando correctamente!',
      icon: '/assets/images/logo.jpeg',
      badge: '/assets/images/logo.jpeg',
      tag: 'test-notification',
      requireInteraction: false,
    })
  }
}

const updateTheme = (theme) => {
  form.theme = theme
  // Aplicar tema inmediatamente (múltiples veces para asegurar)
  applyTheme(theme)
  // Aplicar de nuevo después de un pequeño delay para forzar
  setTimeout(() => {
    applyTheme(theme)
  }, 50)
  setTimeout(() => {
    applyTheme(theme)
  }, 200)
  // Luego guardar
  saveSettings()
}

const applyTheme = (theme) => {
  if (!theme) return
  
  // Usar la función global si está disponible
  if (window.applyTheme) {
    window.applyTheme(theme)
    localStorage.setItem('theme', theme)
    return
  }
  
  // Fallback local
  const root = document.documentElement
  const body = document.body
  
  // Forzar remoción de la clase dark primero (sin delay)
  root.classList.remove('dark')
  body.classList.remove('dark')
  
  if (theme === 'dark') {
    root.classList.add('dark')
    body.classList.add('dark')
    localStorage.setItem('theme', 'dark')
  } else if (theme === 'light') {
    // Para tema claro, asegurarse de que NO esté la clase dark
    root.classList.remove('dark')
    body.classList.remove('dark')
    localStorage.setItem('theme', 'light')
  } else if (theme === 'auto') {
    // Auto theme - follow system preference
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
    if (prefersDark) {
      root.classList.add('dark')
      body.classList.add('dark')
    } else {
      root.classList.remove('dark')
      body.classList.remove('dark')
    }
    localStorage.setItem('theme', 'auto')
  }
}

const saveSettings = () => {
  form.put(route('config.update'), {
    preserveScroll: true,
    onSuccess: (page) => {
      showAlert({
        type: 'success',
        title: 'Configuración guardada',
        message: 'Tus preferencias se han guardado correctamente'
      })
      
      // Aplicar tema inmediatamente después de guardar
      if (form.theme) {
        // Pequeño delay para asegurar que el DOM está listo
        setTimeout(() => {
          applyTheme(form.theme)
        }, 100)
      }
    },
    onError: () => {
      showAlert({
        type: 'error',
        title: 'Error',
        message: 'No se pudo guardar la configuración'
      })
    }
  })
}

onMounted(() => {
  // Aplicar tema al cargar
  if (form.theme) {
    applyTheme(form.theme)
  }
  
  // Verificar permisos de notificación
  checkPushPermission()
})
</script>

