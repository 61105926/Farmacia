<template>
  <span
    class="inline-flex items-center font-medium rounded-full transition-colors"
    :class="[sizeClasses, variantClasses]"
  >
    <component
      v-if="icon && iconPosition === 'left'"
      :is="icon"
      :class="iconClasses"
    />

    <slot />

    <component
      v-if="icon && iconPosition === 'right'"
      :is="icon"
      :class="iconClasses"
    />

    <button
      v-if="dismissible"
      @click="$emit('dismiss')"
      class="ml-1 inline-flex items-center justify-center rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2"
      :class="dismissClasses"
    >
      <X :class="iconClasses" />
    </button>
  </span>
</template>

<script setup>
import { computed } from 'vue'
import { X } from 'lucide-vue-next'

const props = defineProps({
  variant: {
    type: String,
    default: 'default',
    validator: (value) => [
      'default', 'primary', 'secondary', 'success', 'warning', 'error', 'info'
    ].includes(value)
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  icon: {
    type: [Object, Function],
    default: null
  },
  iconPosition: {
    type: String,
    default: 'left',
    validator: (value) => ['left', 'right'].includes(value)
  },
  dismissible: {
    type: Boolean,
    default: false
  }
})

defineEmits(['dismiss'])

const sizeClasses = computed(() => {
  const sizes = {
    sm: 'px-2 py-0.5 text-xs',
    md: 'px-2.5 py-0.5 text-sm',
    lg: 'px-3 py-1 text-base'
  }
  return sizes[props.size]
})

const variantClasses = computed(() => {
  const variants = {
    default: 'bg-gray-100 text-gray-800 border border-gray-300',
    primary: 'bg-blue-100 text-blue-800 border border-blue-300',
    secondary: 'bg-gray-100 text-gray-700 border border-gray-300',
    success: 'bg-green-100 text-green-800 border border-green-300',
    warning: 'bg-yellow-100 text-yellow-800 border border-yellow-300',
    error: 'bg-red-100 text-red-800 border border-red-300',
    info: 'bg-blue-100 text-blue-800 border border-blue-300'
  }
  return variants[props.variant]
})

const iconClasses = computed(() => {
  const sizes = {
    sm: 'w-3 h-3',
    md: 'w-4 h-4',
    lg: 'w-5 h-5'
  }

  const spacing = props.iconPosition === 'left' ? 'mr-1' : 'ml-1'
  return `${sizes[props.size]} ${spacing}`
})

const dismissClasses = computed(() => {
  const variants = {
    default: 'text-gray-400 hover:text-gray-600 focus:ring-gray-500',
    primary: 'text-blue-400 hover:text-blue-600 focus:ring-blue-500',
    secondary: 'text-gray-400 hover:text-gray-600 focus:ring-gray-500',
    success: 'text-green-400 hover:text-green-600 focus:ring-green-500',
    warning: 'text-yellow-400 hover:text-yellow-600 focus:ring-yellow-500',
    error: 'text-red-400 hover:text-red-600 focus:ring-red-500',
    info: 'text-blue-400 hover:text-blue-600 focus:ring-blue-500'
  }
  return variants[props.variant]
})
</script>