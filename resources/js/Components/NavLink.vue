<template>
  <Link
    :href="href"
    :class="[
      'flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-150',
      active
        ? 'bg-blue-50 text-blue-700'
        : 'text-gray-700 hover:bg-gray-100'
    ]"
  >
    <slot />
  </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  href: {
    type: String,
    required: true,
  },
  active: {
    type: Boolean,
    default: false,
  },
})
</script>