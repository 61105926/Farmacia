import { ref } from 'vue'

export function useDebouncedRef(fn, delay = 300) {
  const timeout = ref(null)

  return () => {
    clearTimeout(timeout.value)
    timeout.value = setTimeout(() => {
      fn()
    }, delay)
  }
}
