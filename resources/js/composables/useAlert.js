import { ref } from 'vue'

const alertState = ref({
  show: false,
  type: 'info',
  title: '',
  message: '',
  confirmText: 'Aceptar',
  onConfirm: null
})

export function useAlert() {
  const showAlert = (options) => {
    alertState.value = {
      show: true,
      type: options.type || 'info',
      title: options.title || 'Información',
      message: options.message || '',
      confirmText: options.confirmText || 'Aceptar',
      onConfirm: options.onConfirm || null
    }
  }

  const showConfirm = (options) => {
    showAlert({
      type: 'confirm',
      title: options.title || 'Confirmar',
      message: options.message || '¿Estás seguro?',
      confirmText: options.confirmText || 'Confirmar',
      onConfirm: options.onConfirm || null
    })
  }

  const showSuccess = (message, title = 'Éxito') => {
    showAlert({ type: 'success', title, message })
  }

  const showError = (message, title = 'Error') => {
    showAlert({ type: 'error', title, message })
  }

  const showWarning = (message, title = 'Advertencia') => {
    showAlert({ type: 'warning', title, message })
  }

  const showInfo = (message, title = 'Información') => {
    showAlert({ type: 'info', title, message })
  }

  const hideAlert = () => {
    alertState.value.show = false
  }

  const handleConfirm = () => {
    if (alertState.value.onConfirm) {
      alertState.value.onConfirm()
    }
  }

  return {
    alertState,
    showAlert,
    showConfirm,
    showSuccess,
    showError,
    showWarning,
    showInfo,
    hideAlert,
    handleConfirm
  }
}

// Exportar showAlert directamente para uso simple
export const showAlert = (type, title, message) => {
  alertState.value = {
    show: true,
    type: type || 'info',
    title: title || 'Información',
    message: message || '',
    confirmText: 'Aceptar',
    onConfirm: null
  }
}
