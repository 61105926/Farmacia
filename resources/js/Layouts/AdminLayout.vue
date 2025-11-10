<template>
  <div :class="['min-h-screen flex', isDark ? 'bg-gray-900' : 'bg-white']">
    <!-- Sidebar -->
    <Sidebar
      :is-collapsed="sidebarCollapsed"
      @toggle="toggleSidebar"
      class="transition-all duration-300"
      :class="sidebarCollapsed ? 'w-16' : 'w-64'"
    />

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- Top Navbar -->
      <Navbar
        :sidebar-collapsed="sidebarCollapsed"
        @toggle-sidebar="toggleSidebar"
        @logout="handleLogout"
      />

      <!-- Page Content -->
      <main class="flex-1 overflow-x-hidden overflow-y-auto">
        <div class="container mx-auto px-6 py-8">
          <!-- Page Header -->
          <div class="mb-8" v-if="$slots.header">
            <slot name="header" />
          </div>

          <!-- Page Content -->
          <slot />
        </div>
      </main>
    </div>

    <!-- Global Modal Container -->
    <div id="modal-root"></div>

    <!-- Notifications Container -->
    <NotificationContainer />
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useForm } from '@inertiajs/vue3'
import Sidebar from '@/Components/layout/Sidebar.vue'
import Navbar from '@/Components/layout/Navbar.vue'
import NotificationContainer from '@/Components/layout/NotificationContainer.vue'

// Theme state - usar ref para que sea reactivo
const isDark = ref(document.documentElement.classList.contains('dark'))

// Sidebar state
const sidebarCollapsed = ref(false)

// Get initial sidebar state from localStorage
onMounted(() => {
  const savedState = localStorage.getItem('sidebar-collapsed')
  if (savedState !== null) {
    sidebarCollapsed.value = JSON.parse(savedState)
  }
  
  // Observar cambios en la clase dark del documento
  const observer = new MutationObserver(() => {
    isDark.value = document.documentElement.classList.contains('dark')
  })
  
  observer.observe(document.documentElement, {
    attributes: true,
    attributeFilter: ['class']
  })
  
  // Cleanup
  onUnmounted(() => {
    observer.disconnect()
  })
})

// Toggle sidebar
const toggleSidebar = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value
  localStorage.setItem('sidebar-collapsed', JSON.stringify(sidebarCollapsed.value))
}

// Handle logout
const logoutForm = useForm({})
const handleLogout = () => {
  logoutForm.post('/logout')
}

// Handle responsive sidebar
const handleResize = () => {
  if (window.innerWidth < 1024) {
    sidebarCollapsed.value = true
  }
}

onMounted(() => {
  window.addEventListener('resize', handleResize)
  handleResize()
})

onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
})
</script>