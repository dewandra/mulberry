<template>
  <div class="min-h-screen bg-gray-50">
    <Head :title="title" />
    
    <!-- Sidebar -->
    <aside class="fixed inset-y-0 left-0 w-64 bg-white border-r border-gray-200 flex flex-col z-50">
      <!-- Logo -->
      <div class="h-16 flex items-center px-6 border-b border-gray-200">
        <h1 class="text-xl font-bold text-gray-900" style="font-family: 'Sora', sans-serif;">
          ACT<span class="text-blue-600">.</span>
        </h1>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
        <NavLink href="/dashboard" :active="route().current('dashboard')">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
          </svg>
          Dashboard
        </NavLink>

        <template v-if="canManageUsers">
          <div class="pt-6 pb-2">
            <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">
              Management
            </p>
          </div>

          <NavLink href="/users" :active="route().current('users.*')">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            Users
          </NavLink>

          <NavLink href="/clients" :active="route().current('clients.*')">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            Clients
          </NavLink>
        </template>
      </nav>

      <!-- User Menu -->
      <div class="border-t border-gray-200 p-4">
        <div class="flex items-center space-x-3 mb-3">
          <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
            <span class="text-sm font-semibold text-blue-600">
              {{ userInitials }}
            </span>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900 truncate">
              {{ $page.props.auth.user.full_name }}
            </p>
            <p class="text-xs text-gray-500 truncate">
              {{ $page.props.auth.user.email }}
            </p>
          </div>
        </div>
        <button 
          @click="logout"
          class="w-full flex items-center justify-center space-x-2 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
          </svg>
          <span>Logout</span>
        </button>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="pl-64">
      <!-- Header -->
      <header class="bg-white border-b border-gray-200 sticky top-0 z-40">
        <div class="px-8 py-4">
          <div class="flex items-center justify-between">
            <div>
              <h2 class="text-2xl font-bold text-gray-900" style="font-family: 'Sora', sans-serif;">
                {{ title }}
              </h2>
              <p v-if="subtitle" class="text-sm text-gray-600 mt-1">
                {{ subtitle }}
              </p>
            </div>
            <div v-if="$slots.actions" class="flex items-center space-x-3">
              <slot name="actions" />
            </div>
          </div>
        </div>
      </header>

      <!-- Flash Messages -->
      <div v-if="$page.props.flash.success || $page.props.flash.error" class="px-8 py-4">
        <div v-if="$page.props.flash.success" 
             class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center space-x-3 animate-fade-in">
          <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          </svg>
          <span>{{ $page.props.flash.success }}</span>
        </div>
        <div v-if="$page.props.flash.error" 
             class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg flex items-center space-x-3 animate-fade-in">
          <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
          </svg>
          <span>{{ $page.props.flash.error }}</span>
        </div>
      </div>

      <!-- Page Content -->
      <div class="px-8 py-6">
        <slot />
      </div>
    </main>
  </div>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3'
import { computed } from 'vue'
import NavLink from '@/Components/NavLink.vue'

defineProps({
  title: String,
  subtitle: String,
})

const userInitials = computed(() => {
  const name = window.$page?.props?.auth?.user?.full_name || ''
  return name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
})

const canManageUsers = computed(() => {
  const role = window.$page?.props?.auth?.user?.role
  return ['super_admin', 'admin'].includes(role)
})

const logout = () => {
  router.post('/logout')
}
</script>

<style scoped>
@keyframes fade-in {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in {
  animation: fade-in 0.3s ease-out;
}
</style>