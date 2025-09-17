<template>
  <li v-if="canAccess">
    <Link
      :href="href"
      class="group flex items-center rounded-lg text-sm font-medium transition-colors"
      :class="[
        active
          ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-700'
          : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900',
        collapsed ? 'px-2 py-2 justify-center' : 'px-3 py-2'
      ]"
    >
      <component
        :is="iconComponent"
        class="flex-shrink-0 transition-colors"
        :class="[
          active ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500',
          collapsed ? 'w-6 h-6' : 'w-5 h-5 mr-3'
        ]"
      />
      <span v-if="!collapsed" class="truncate">{{ label }}</span>

      <!-- Tooltip for collapsed state -->
      <div
        v-if="collapsed"
        class="absolute left-full ml-6 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 whitespace-nowrap z-50"
      >
        {{ label }}
      </div>
    </Link>
  </li>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { usePermissions } from '@/composables/usePermissions'
import {
  LayoutDashboard,
  Users,
  UserCheck,
  Package,
  Warehouse,
  FileText,
  ShoppingCart,
  CreditCard,
  BarChart,
  Settings
} from 'lucide-vue-next'

const props = defineProps({
  href: {
    type: String,
    required: true
  },
  active: {
    type: Boolean,
    default: false
  },
  collapsed: {
    type: Boolean,
    default: false
  },
  icon: {
    type: String,
    required: true
  },
  label: {
    type: String,
    required: true
  },
  permissions: {
    type: Array,
    default: () => []
  }
})

const { hasAnyPermission } = usePermissions()

const iconComponents = {
  LayoutDashboard,
  Users,
  UserCheck,
  Package,
  Warehouse,
  FileText,
  ShoppingCart,
  CreditCard,
  BarChart,
  Settings
}

const iconComponent = computed(() => iconComponents[props.icon] || LayoutDashboard)

const canAccess = computed(() => {
  if (props.permissions.length === 0) return true
  return hasAnyPermission(props.permissions)
})
</script>