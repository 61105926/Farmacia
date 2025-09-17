import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

export function usePermissions() {
  const page = usePage()

  const user = computed(() => page.props.auth.user)

  const hasPermission = (permission) => {
    if (!user.value) return false

    // Si es administrador, tiene todos los permisos
    if (hasRole('Administrador')) return true

    // Verificar permisos específicos
    return user.value.permissions?.some(p => p.name === permission) || false
  }

  const hasRole = (roleName) => {
    if (!user.value) return false
    return user.value.roles?.some(role => role.name === roleName) || false
  }

  const hasAnyRole = (roleNames) => {
    if (!user.value) return false
    return roleNames.some(roleName => hasRole(roleName))
  }

  const hasAnyPermission = (permissions) => {
    if (!user.value) return false
    return permissions.some(permission => hasPermission(permission))
  }

  return {
    user,
    hasPermission,
    hasRole,
    hasAnyRole,
    hasAnyPermission
  }
}