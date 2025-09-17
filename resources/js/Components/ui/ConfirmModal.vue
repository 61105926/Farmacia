<template>
  <Teleport to="body">
    <div
      v-if="show"
      class="fixed inset-0 z-50 overflow-y-auto"
      @click="handleBackdropClick"
    >
      <!-- Backdrop -->
      <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" />

      <!-- Modal -->
      <div class="flex min-h-full items-center justify-center p-4">
        <div
          class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6"
          @click.stop
        >
          <!-- Icon -->
          <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full" :class="iconBgClass">
            <component :is="iconComponent" class="h-6 w-6" :class="iconClass" />
          </div>

          <!-- Content -->
          <div class="mt-3 text-center sm:mt-5">
            <h3 class="text-base font-semibold leading-6 text-gray-900">
              {{ title }}
            </h3>
            <div class="mt-2">
              <p class="text-sm text-gray-500 whitespace-pre-line">
                {{ message }}
              </p>
            </div>
          </div>

          <!-- Actions -->
          <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
            <button
              type="button"
              class="inline-flex w-full justify-center rounded-md px-3 py-2 text-sm font-semibold text-white shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 sm:col-start-2"
              :class="confirmButtonClass"
              @click="handleConfirm"
            >
              {{ confirmText }}
            </button>
            <button
              type="button"
              class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:col-start-1 sm:mt-0"
              @click="handleCancel"
            >
              {{ cancelText }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { computed } from 'vue'
import { AlertTriangle, Trash2, CheckCircle } from 'lucide-vue-next'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: '¿Estás seguro?'
  },
  message: {
    type: String,
    default: 'Esta acción no se puede deshacer.'
  },
  type: {
    type: String,
    default: 'warning', // warning, danger, success, info
    validator: (value) => ['warning', 'danger', 'success', 'info'].includes(value)
  },
  confirmText: {
    type: String,
    default: 'Confirmar'
  },
  cancelText: {
    type: String,
    default: 'Cancelar'
  }
})

const emit = defineEmits(['confirm', 'cancel', 'close'])

const iconComponent = computed(() => {
  const icons = {
    warning: AlertTriangle,
    danger: Trash2,
    success: CheckCircle,
    info: AlertTriangle
  }
  return icons[props.type] || AlertTriangle
})

const iconBgClass = computed(() => {
  const classes = {
    warning: 'bg-yellow-100',
    danger: 'bg-red-100',
    success: 'bg-green-100',
    info: 'bg-blue-100'
  }
  return classes[props.type] || 'bg-yellow-100'
})

const iconClass = computed(() => {
  const classes = {
    warning: 'text-yellow-600',
    danger: 'text-red-600',
    success: 'text-green-600',
    info: 'text-blue-600'
  }
  return classes[props.type] || 'text-yellow-600'
})

const confirmButtonClass = computed(() => {
  const classes = {
    warning: 'bg-yellow-600 hover:bg-yellow-500 focus-visible:outline-yellow-600',
    danger: 'bg-red-600 hover:bg-red-500 focus-visible:outline-red-600',
    success: 'bg-green-600 hover:bg-green-500 focus-visible:outline-green-600',
    info: 'bg-blue-600 hover:bg-blue-500 focus-visible:outline-blue-600'
  }
  return classes[props.type] || 'bg-yellow-600 hover:bg-yellow-500 focus-visible:outline-yellow-600'
})

const handleConfirm = () => {
  emit('confirm')
  emit('close')
}

const handleCancel = () => {
  emit('cancel')
  emit('close')
}

const handleBackdropClick = () => {
  emit('cancel')
  emit('close')
}
</script>