<template>
  <div :class="cn('rounded-lg border bg-white text-slate-950 shadow-sm', $attrs.class ?? '')">
    <slot />
  </div>
</template>

<script setup>
import { cn } from '@/lib/utils'
</script>