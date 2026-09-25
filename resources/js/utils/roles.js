// Nombre visible de los roles (el nombre interno no cambia porque el código filtra por él)
const ROLE_LABELS = {
  'vendedor-ventas': 'Ventas',
  'vendedor-preventas': 'Preventas',
}

export const roleLabel = (name) => ROLE_LABELS[name] || name
