import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'

const appName = import.meta.env.VITE_APP_NAME || 'Laravel'

// Aplicar tema al cargar - función global
function applyTheme(theme) {
  if (!theme) return
  
  const root = document.documentElement
  const body = document.body
  
  // Forzar remoción de la clase dark primero (sin delay)
  root.classList.remove('dark')
  body.classList.remove('dark')
  
  if (theme === 'dark') {
    root.classList.add('dark')
    body.classList.add('dark')
  } else if (theme === 'light') {
    // Para tema claro, asegurarse de que NO esté la clase dark
    // Ya se removió arriba, pero lo hacemos de nuevo para estar seguros
    root.classList.remove('dark')
    body.classList.remove('dark')
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
  }
}

// Hacer la función disponible globalmente
window.applyTheme = applyTheme

// Aplicar tema desde localStorage o servidor
const savedTheme = localStorage.getItem('theme')
if (savedTheme) {
  applyTheme(savedTheme)
} else {
  // Si no hay tema guardado, aplicar light por defecto
  applyTheme('light')
}

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
  setup({ el, App, props, plugin }) {
    const app = createApp({ render: () => h(App, props) })
      .use(plugin)
    
    // Aplicar tema del usuario si está disponible (tiene prioridad sobre localStorage)
    const userTheme = props.initialPage?.props?.auth?.user?.theme
    if (userTheme) {
      applyTheme(userTheme)
      localStorage.setItem('theme', userTheme)
    } else if (!savedTheme) {
      // Si no hay tema del usuario ni en localStorage, aplicar light
      applyTheme('light')
      localStorage.setItem('theme', 'light')
    }
    
    // Asegurar que el tema se mantenga después del mount
    const finalTheme = userTheme || savedTheme || 'light'
    setTimeout(() => {
      applyTheme(finalTheme)
    }, 0)
    
    return app.mount(el)
  },
  progress: {
    color: '#4B5563',
  },
})
