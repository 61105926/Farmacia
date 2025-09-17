<template>
  <div class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 space-y-4">
    <Transition
      v-for="notification in notifications"
      :key="notification.id"
      enter-active-class="transform ease-out duration-300 transition"
      enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
      enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
      leave-active-class="transition ease-in duration-100"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        class="w-96 md:w-[500px] lg:w-[600px] bg-white shadow-xl rounded-xl pointer-events-auto ring-1 ring-black ring-opacity-10 overflow-hidden border border-gray-100"
      >
        <div class="p-5">
          <div class="flex items-start">
            <div class="flex-shrink-0">
              <component
                :is="getIcon(notification.type)"
                class="h-7 w-7"
                :class="getIconColor(notification.type)"
              />
            </div>
            <div class="ml-4 w-0 flex-1 pt-0.5">
              <p class="text-base font-semibold text-gray-900">{{ notification.title }}</p>
              <p class="mt-2 text-sm text-gray-600 leading-relaxed">{{ notification.message }}</p>
            </div>
            <div class="ml-4 flex-shrink-0 flex">
              <button
                @click="removeNotification(notification.id)"
                class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              >
                <X class="h-5 w-5" />
              </button>
            </div>
          </div>
        </div>
        <!-- Progress bar for auto-dismiss -->
        <div
          v-if="notification.autoDismiss"
          class="h-1 bg-gray-200"
        >
          <div
            class="h-full bg-blue-600 transition-all linear"
            :style="{ width: `${100 - (notification.timeRemaining / notification.duration) * 100}%` }"
          ></div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { CheckCircle, AlertCircle, XCircle, Info, X } from 'lucide-vue-next'

const notifications = ref([])
let notificationId = 0

const addNotification = (notification) => {
  const id = ++notificationId
  const newNotification = {
    id,
    type: 'info',
    title: '',
    message: '',
    duration: 5000,
    autoDismiss: true,
    timeRemaining: 5000,
    ...notification
  }

  notifications.value.push(newNotification)

  if (newNotification.autoDismiss) {
    const interval = setInterval(() => {
      newNotification.timeRemaining -= 100
      if (newNotification.timeRemaining <= 0) {
        removeNotification(id)
        clearInterval(interval)
      }
    }, 100)
  }

  return id
}

const removeNotification = (id) => {
  const index = notifications.value.findIndex(n => n.id === id)
  if (index > -1) {
    notifications.value.splice(index, 1)
  }
}

const getIcon = (type) => {
  const icons = {
    success: CheckCircle,
    error: XCircle,
    warning: AlertCircle,
    info: Info
  }
  return icons[type] || Info
}

const getIconColor = (type) => {
  const colors = {
    success: 'text-green-400',
    error: 'text-red-400',
    warning: 'text-yellow-400',
    info: 'text-blue-400'
  }
  return colors[type] || 'text-blue-400'
}

// Global notification methods
const showSuccess = (title, message) => {
  return addNotification({ type: 'success', title, message })
}

const showError = (title, message) => {
  return addNotification({ type: 'error', title, message, autoDismiss: false })
}

const showWarning = (title, message) => {
  return addNotification({ type: 'warning', title, message })
}

const showInfo = (title, message) => {
  return addNotification({ type: 'info', title, message })
}

// Make methods globally available
onMounted(() => {
  window.$notify = {
    success: showSuccess,
    error: showError,
    warning: showWarning,
    info: showInfo,
    remove: removeNotification
  }
})

onUnmounted(() => {
  delete window.$notify
})

// Expose methods for direct component usage
defineExpose({
  addNotification,
  removeNotification,
  showSuccess,
  showError,
  showWarning,
  showInfo
})
</script>