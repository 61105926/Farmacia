<template>
  <div
    v-if="show"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
    @click="closeOnBackdrop && close()"
  >
    <div
      class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4"
      @click.stop
    >
      <!-- Header -->
      <div class="flex items-center justify-between p-6 border-b">
        <div class="flex items-center">
          <component
            :is="iconComponent"
            :class="iconClass"
            class="h-6 w-6 mr-3"
          />
          <h3 class="text-lg font-semibold text-gray-900">
            {{ title }}
          </h3>
        </div>
        <button
          @click="close"
          class="text-gray-400 hover:text-gray-600 transition-colors"
        >
          <X class="h-6 w-6" />
        </button>
      </div>

      <!-- Content -->
      <div class="p-6">
        <p class="text-gray-600 mb-6">{{ message }}</p>
        
        <!-- Actions -->
        <div class="flex justify-end space-x-3">
          <button
            v-if="type === 'confirm'"
            @click="close"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors"
          >
            Cancelar
          </button>
          <button
            @click="confirm"
            :class="confirmButtonClass"
            class="px-4 py-2 text-sm font-medium text-white rounded-md transition-colors"
          >
            {{ confirmText }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { AlertTriangle, Info, CheckCircle, XCircle, X } from 'lucide-vue-next'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  type: {
    type: String,
    default: 'info',
    validator: (value) => ['info', 'success', 'warning', 'error', 'confirm'].includes(value)
  },
  title: {
    type: String,
    required: true
  },
  message: {
    type: String,
    required: true
  },
  confirmText: {
    type: String,
    default: 'Aceptar'
  },
  closeOnBackdrop: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['confirm', 'close'])

const iconComponent = computed(() => {
  const icons = {
    info: Info,
    success: CheckCircle,
    warning: AlertTriangle,
    error: XCircle,
    confirm: AlertTriangle
  }
  return icons[props.type]
})

const iconClass = computed(() => {
  const classes = {
    info: 'text-blue-500',
    success: 'text-green-500',
    warning: 'text-yellow-500',
    error: 'text-red-500',
    confirm: 'text-yellow-500'
  }
  return classes[props.type]
})

const confirmButtonClass = computed(() => {
  const classes = {
    info: 'bg-blue-600 hover:bg-blue-700',
    success: 'bg-green-600 hover:bg-green-700',
    warning: 'bg-yellow-600 hover:bg-yellow-700',
    error: 'bg-red-600 hover:bg-red-700',
    confirm: 'bg-blue-600 hover:bg-blue-700'
  }
  return classes[props.type]
})

const confirm = () => {
  emit('confirm')
  emit('close')
}

const close = () => {
  emit('close')
}
</script>
