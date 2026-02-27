<template>
  <Link
    :href="href"
    :class="[
      'flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200',
      active
        ? 'bg-white/15 text-white shadow-sm backdrop-blur-sm border border-white/20'
        : 'text-white/60 hover:bg-white/10 hover:text-white'
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