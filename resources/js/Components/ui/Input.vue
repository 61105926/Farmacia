<template>
  <input
    :value="modelValue"
    @input="$emit('update:modelValue', $event.target.value)"
    :class="cn(
      'flex h-10 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm ring-offset-white file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-slate-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
      error ? 'border-red-500 focus-visible:ring-red-500' : '',
      $attrs.class ?? ''
    )"
    v-bind="$attrs"
  />
  <div v-if="error" class="mt-1 text-sm text-red-600">
    {{ error }}
  </div>
</template>

<script setup>
import { cn } from '@/lib/utils'

const props = defineProps({
  modelValue: [String, Number],
  error: String
})

const emit = defineEmits(['update:modelValue'])
</script>