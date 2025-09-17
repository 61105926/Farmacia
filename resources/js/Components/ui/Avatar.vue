<template>
  <div
    class="relative inline-flex items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-purple-600 text-white font-medium select-none"
    :class="sizeClasses"
  >
    <img
      v-if="user.avatar"
      :src="user.avatar"
      :alt="user.name"
      class="w-full h-full rounded-full object-cover"
    />
    <span v-else class="text-white">{{ initials }}</span>

    <!-- Online Status Indicator -->
    <div
      v-if="showStatus"
      class="absolute bottom-0 right-0 block rounded-full ring-2 ring-white"
      :class="statusClasses"
    ></div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  user: {
    type: Object,
    required: true
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl'].includes(value)
  },
  showStatus: {
    type: Boolean,
    default: false
  },
  status: {
    type: String,
    default: 'online',
    validator: (value) => ['online', 'offline', 'away', 'busy'].includes(value)
  }
})

const initials = computed(() => {
  if (!props.user.name) return '?'

  const names = props.user.name.split(' ')
  if (names.length === 1) {
    return names[0].charAt(0).toUpperCase()
  }
  return (names[0].charAt(0) + names[names.length - 1].charAt(0)).toUpperCase()
})

const sizeClasses = computed(() => {
  const sizes = {
    xs: 'w-6 h-6 text-xs',
    sm: 'w-8 h-8 text-sm',
    md: 'w-10 h-10 text-base',
    lg: 'w-12 h-12 text-lg',
    xl: 'w-16 h-16 text-xl'
  }
  return sizes[props.size]
})

const statusClasses = computed(() => {
  const statuses = {
    online: 'bg-green-400 w-3 h-3',
    offline: 'bg-gray-400 w-3 h-3',
    away: 'bg-yellow-400 w-3 h-3',
    busy: 'bg-red-400 w-3 h-3'
  }
  return statuses[props.status]
})
</script>