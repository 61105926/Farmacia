<template>
  <div
    :class="[
      'relative w-full rounded-lg border p-4',
      variantClasses[variant]
    ]"
    role="alert"
  >
    <slot />
  </div>
</template>

<script setup>
const props = defineProps({
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'destructive', 'success', 'warning'].includes(value)
  }
})

const variantClasses = {
  default: 'bg-background text-foreground border-gray-200',
  destructive: 'border-red-500/50 text-red-600 dark:border-red-500 [&>svg]:text-red-600',
  success: 'border-green-500/50 bg-green-50 text-green-900 dark:border-green-500 [&>svg]:text-green-600',
  warning: 'border-yellow-500/50 bg-yellow-50 text-yellow-900 dark:border-yellow-500 [&>svg]:text-yellow-600'
}
</script>
