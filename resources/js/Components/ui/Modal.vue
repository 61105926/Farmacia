<template>
  <Teleport to="body">
    <Transition
      enter-active-class="duration-300 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="duration-200 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="modelValue"
        class="fixed inset-0 z-50 overflow-y-auto"
        @click.self="handleBackdropClick"
      >
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>

        <!-- Modal Container -->
        <div class="relative min-h-full flex items-center justify-center p-4">
          <Transition
            enter-active-class="duration-300 ease-out"
            enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            enter-to-class="opacity-100 translate-y-0 sm:scale-100"
            leave-active-class="duration-200 ease-in"
            leave-from-class="opacity-100 translate-y-0 sm:scale-100"
            leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          >
            <div
              v-if="modelValue"
              class="relative bg-white rounded-lg shadow-xl transform transition-all"
              :class="sizeClasses"
            >
              <!-- Header -->
              <div
                v-if="$slots.header || title"
                class="flex items-center justify-between p-6 border-b border-gray-200"
              >
                <div>
                  <slot name="header">
                    <h3 class="text-lg font-semibold text-gray-900">{{ title }}</h3>
                    <p v-if="subtitle" class="text-sm text-gray-500 mt-1">{{ subtitle }}</p>
                  </slot>
                </div>
                <button
                  v-if="showCloseButton"
                  @click="closeModal"
                  class="text-gray-400 hover:text-gray-500 focus:outline-none focus:text-gray-500 transition-colors"
                >
                  <X class="w-6 h-6" />
                </button>
              </div>

              <!-- Body -->
              <div class="p-6" :class="bodyClass">
                <slot />
              </div>

              <!-- Footer -->
              <div
                v-if="$slots.footer"
                class="flex items-center justify-end space-x-3 p-6 border-t border-gray-200 bg-gray-50 rounded-b-lg"
              >
                <slot name="footer" />
              </div>
            </div>
          </Transition>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { computed, onMounted, onUnmounted, watch } from 'vue'
import { X } from 'lucide-vue-next'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: ''
  },
  subtitle: {
    type: String,
    default: ''
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl', '2xl', 'full'].includes(value)
  },
  closable: {
    type: Boolean,
    default: true
  },
  closeOnBackdrop: {
    type: Boolean,
    default: true
  },
  showCloseButton: {
    type: Boolean,
    default: true
  },
  bodyClass: {
    type: String,
    default: ''
  },
  persistent: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue', 'close', 'opened', 'closed'])

const sizeClasses = computed(() => {
  const sizes = {
    xs: 'max-w-xs w-full',
    sm: 'max-w-sm w-full',
    md: 'max-w-md w-full',
    lg: 'max-w-lg w-full',
    xl: 'max-w-xl w-full',
    '2xl': 'max-w-2xl w-full',
    full: 'max-w-full w-full h-full'
  }
  return sizes[props.size]
})

const closeModal = () => {
  if (props.closable && !props.persistent) {
    emit('update:modelValue', false)
    emit('close')
  }
}

const handleBackdropClick = () => {
  if (props.closeOnBackdrop) {
    closeModal()
  }
}

const handleEscapeKey = (event) => {
  if (event.key === 'Escape' && props.modelValue) {
    closeModal()
  }
}

// Lock body scroll when modal is open
watch(() => props.modelValue, (newValue) => {
  if (newValue) {
    document.body.style.overflow = 'hidden'
    emit('opened')
  } else {
    document.body.style.overflow = 'auto'
    emit('closed')
  }
})

onMounted(() => {
  document.addEventListener('keydown', handleEscapeKey)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleEscapeKey)
  document.body.style.overflow = 'auto'
})
</script>