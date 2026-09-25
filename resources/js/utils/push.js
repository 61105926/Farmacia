import axios from 'axios'

// Notificaciones push (Web Push): el servicio web solo funciona con HTTPS o en localhost
export const isPushSupported = () =>
  window.isSecureContext &&
  'serviceWorker' in navigator &&
  'PushManager' in window &&
  'Notification' in window

const urlBase64ToUint8Array = (base64) => {
  const padding = '='.repeat((4 - (base64.length % 4)) % 4)
  const raw = window.atob((base64 + padding).replace(/-/g, '+').replace(/_/g, '/'))
  return Uint8Array.from([...raw].map((c) => c.charCodeAt(0)))
}

const getRegistration = async () => {
  await navigator.serviceWorker.register('/sw.js')
  return navigator.serviceWorker.ready
}

// Suscribe este navegador y guarda la suscripción en el servidor
export const subscribeToPush = async () => {
  const registration = await getRegistration()
  const { data } = await axios.get('/api/push/public-key')
  const applicationServerKey = urlBase64ToUint8Array(data.public_key)

  let subscription = await registration.pushManager.getSubscription()

  // Si la suscripción existente es de otra clave VAPID, se rehace
  const currentKey = subscription?.options?.applicationServerKey
  if (subscription && currentKey) {
    const same = new Uint8Array(currentKey).every((b, i) => b === applicationServerKey[i])
    if (!same) {
      await subscription.unsubscribe()
      subscription = null
    }
  }

  if (!subscription) {
    subscription = await registration.pushManager.subscribe({
      userVisibleOnly: true,
      applicationServerKey,
    })
  }

  await axios.post('/api/push/subscriptions', subscription.toJSON())
  return subscription
}

// Quita la suscripción de este navegador
export const unsubscribeFromPush = async () => {
  const registration = await navigator.serviceWorker.getRegistration('/sw.js')
  const subscription = await registration?.pushManager.getSubscription()
  if (!subscription) return

  await axios.delete('/api/push/subscriptions', { data: { endpoint: subscription.endpoint } })
  await subscription.unsubscribe()
}

export const sendTestPush = () => axios.post('/api/push/test')
