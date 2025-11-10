<template>
  <nav v-if="links && links.length > 0" class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
    <div class="flex flex-1 justify-between sm:hidden">
      <Link
        v-if="links[0].url"
        :href="links[0].url"
        class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
      >
        Anterior
      </Link>
      <Link
        v-if="links[links.length - 1].url"
        :href="links[links.length - 1].url"
        class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
      >
        Siguiente
      </Link>
    </div>
    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
      <div>
        <p class="text-sm text-gray-700">
          <template v-if="paginationData && paginationData.from && paginationData.to">
            Mostrando
            <span class="font-medium">{{ paginationData.from }}</span>
            a
            <span class="font-medium">{{ paginationData.to }}</span>
            de
            <span class="font-medium">{{ paginationData.total || 0 }}</span>
            resultados
          </template>
          <template v-else>
            <span class="font-medium">{{ paginationData?.total || 0 }}</span>
            resultados
          </template>
        </p>
      </div>
      <div>
        <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
          <template v-for="(link, index) in links" :key="index">
            <Link
              v-if="link.url"
              :href="link.url"
              :class="[
                'relative inline-flex items-center px-4 py-2 text-sm font-semibold',
                link.active
                  ? 'z-10 bg-primary-600 text-white focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600'
                  : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0',
                index === 0 ? 'rounded-l-md' : '',
                index === links.length - 1 ? 'rounded-r-md' : ''
              ]"
              v-html="getLinkLabel(link.label)"
            />
            <span
              v-else
              :class="[
                'relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-500 ring-1 ring-inset ring-gray-300 focus:outline-offset-0',
                index === 0 ? 'rounded-l-md' : '',
                index === links.length - 1 ? 'rounded-r-md' : ''
              ]"
              v-html="getLinkLabel(link.label)"
            />
          </template>
        </nav>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  links: {
    type: Array,
    required: true
  },
  paginationData: {
    type: Object,
    default: null
  }
})

const getLinkLabel = (label) => {
  if (!label) return ''
  
  // Traducir y limpiar los labels
  return label
    .replace(/&laquo;/g, '‹')
    .replace(/&raquo;/g, '›')
    .replace(/Previous/g, 'Anterior')
    .replace(/Next/g, 'Siguiente')
}
</script>
