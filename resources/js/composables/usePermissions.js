import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

export function usePermissions() {
  const page = usePage()

  const user = computed(() => page.props.auth?.user)
  const permissions = computed(() => page.props.auth?.permissions || [])
  const roles = computed(() => page.props.auth?.roles || [])

  const can = (permission) => {
    if (!user.value) return false

    // Si es super-admin, tiene todos los permisos
    if (roles.value.includes('super-admin')) return true

    // Verificar permisos específicos (permissions es array de strings)
    return permissions.value.includes(permission)
  }

  const hasRole = (roleName) => {
    if (!user.value) return false
    return roles.value.includes(roleName)
  }

  const isSuperAdmin = computed(() => {
    return roles.value.includes('super-admin')
  })

  const hasAnyRole = (roleNames) => {
    if (!user.value) return false
    return roleNames.some(roleName => hasRole(roleName))
  }

  const hasAnyPermission = (permissionsList) => {
    if (!user.value) return false
    return permissionsList.some(permission => can(permission))
  }

  const hasAllPermissions = (permissionsList) => {
    if (!user.value) return false
    return permissionsList.every(permission => can(permission))
  }

  const canAccessModule = (module) => {
    if (!user.value) return false
    
    // Permisos básicos para cada módulo
      const modulePermissions = {
        'dashboard': ['dashboard.view'],
        'users': ['users.view', 'users.create', 'users.update', 'users.delete'],
        'clients': ['clients.view', 'clients.create', 'clients.update', 'clients.delete'],
        'products': ['products.view', 'products.create', 'products.update', 'products.delete'],
        'inventory': ['inventory.view', 'inventory.update'],
        'sales': ['sales.view', 'sales.create', 'sales.update', 'sales.delete'],
        'presales': ['presales.view', 'presales.create', 'presales.update', 'presales.delete'],
        'receivables': ['receivables.view', 'receivables.update'],
        'reports': ['reports.view'],
        'settings': ['settings.view', 'settings.update'],
      }

    const requiredPermissions = modulePermissions[module] || []
    return hasAnyPermission(requiredPermissions)
  }

  const getUserPermissionsByModule = (module) => {
    if (!user.value) return []
    
    return permissions.value.filter(permission => 
      permission.startsWith(`${module}.`)
    )
  }

  const getAvailableModules = () => {
    const modules = [
      { key: 'dashboard', name: 'Dashboard', icon: 'LayoutDashboard' },
      { key: 'users', name: 'Usuarios', icon: 'Users' },
      { key: 'clients', name: 'Clientes', icon: 'Building2' },
      { key: 'products', name: 'Productos', icon: 'Package' },
      { key: 'inventory', name: 'Inventario', icon: 'Warehouse' },
      { key: 'sales', name: 'Ventas', icon: 'ShoppingCart' },
      { key: 'presales', name: 'Preventas', icon: 'FileText' },
      { key: 'receivables', name: 'Cuentas por Cobrar', icon: 'CreditCard' },
      { key: 'reports', name: 'Reportes', icon: 'BarChart3' },
      { key: 'settings', name: 'Configuración', icon: 'Settings' },
    ]

    return modules.filter(module => canAccessModule(module.key))
  }

  return {
    user,
    permissions,
    roles,
    can,
    hasRole,
    hasAnyRole,
    hasAnyPermission,
    hasAllPermissions,
    canAccessModule,
    getUserPermissionsByModule,
    getAvailableModules,
    isSuperAdmin
  }
}