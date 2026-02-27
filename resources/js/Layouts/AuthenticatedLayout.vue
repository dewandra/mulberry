<template>
  <div class="min-h-screen" style="background: #f3f4f8;">
    <Head :title="title" />
    
    <!-- Mobile menu backdrop -->
    <div 
      v-if="sidebarOpen" 
      @click="sidebarOpen = false"
      class="fixed inset-0 bg-gray-900 bg-opacity-50 z-40 lg:hidden"
    ></div>
    
    <!-- Sidebar -->
    <aside 
      :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
      class="fixed inset-y-0 left-0 w-64 flex flex-col z-50 transition-transform duration-300 ease-in-out lg:translate-x-0"
      style="background: linear-gradient(175deg, #0f172a 0%, #1e293b 40%, #1e293b 70%, #334155 100%);"
    >
      <!-- Decorative top glow -->
      <div class="absolute top-0 right-0 w-40 h-40 rounded-full opacity-10 pointer-events-none" style="background: radial-gradient(circle, #ffffff 0%, transparent 70%); transform: translate(30%, -30%);"></div>
      <div class="absolute bottom-32 left-0 w-32 h-32 rounded-full opacity-5 pointer-events-none" style="background: radial-gradient(circle, #94a3b8 0%, transparent 70%); transform: translate(-40%, 0);"></div>

      <!-- Logo -->
      <div class="h-[5.5rem] flex items-center px-6 border-b border-white/10 relative z-10">
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background: linear-gradient(135deg, #475569, #1e293b);">
            <span class="text-white font-black text-sm">A</span>
          </div>
          <h1 class="text-xl font-bold text-white" style="font-family: 'Sora', sans-serif;">
            ACT<span class="text-slate-400">.</span>
          </h1>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto relative z-10">
        <!-- Main Section -->
        <p class="px-3 text-xs font-semibold text-white/30 uppercase tracking-widest mb-3">Main</p>

        <NavLink href="/dashboard" :active="route().current('dashboard')">
          <svg class="w-4 h-4 flex-shrink-0 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v5a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zm10-3a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1v-7z" />
          </svg>
          Dashboard
        </NavLink>

        <NavLink href="/projects" :active="route().current('projects.*')">
          <svg class="w-4 h-4 flex-shrink-0 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
          </svg>
          Projects
        </NavLink>

        <!-- Management Section - Super Admin Only -->
        <template v-if="isSuperAdmin">
          <p class="px-3 text-xs font-semibold text-white/30 uppercase tracking-widest mt-6 mb-3">Management</p>

          <NavLink href="/users" :active="route().current('users.*')">
            <svg class="w-4 h-4 flex-shrink-0 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Users
          </NavLink>

          <NavLink href="/clients" :active="route().current('clients.*')">
            <svg class="w-4 h-4 flex-shrink-0 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            Companies
          </NavLink>
        </template>
      </nav>

      <!-- User Card -->
      <div class="relative z-10 border-t border-white/10 p-4">
        <div class="flex items-center gap-3 mb-3">
          <!-- Avatar / Client Logo -->
          <div v-if="clientLogo" class="w-9 h-9 rounded-xl border border-white/20 flex items-center justify-center flex-shrink-0 bg-white/10 p-1">
            <img :src="clientLogo" :alt="clientCompanyName" class="w-full h-full object-contain" />
          </div>
          <div v-else class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 font-bold text-sm text-white" style="background: linear-gradient(135deg, #475569, #0f172a);">
            {{ userInitials }}
          </div>

          <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-white truncate">
              {{ $page.props.auth.user.full_name }}
            </p>
            <p class="text-xs text-white/50 truncate">
              {{ $page.props.auth.user.role_display }}
            </p>
            <p v-if="clientCompanyName" class="text-xs text-slate-300 truncate font-medium mt-0.5">
              {{ clientCompanyName }}
            </p>
          </div>
        </div>
        <button 
          @click="logout"
          class="w-full flex items-center justify-center gap-2 px-4 py-2 text-xs font-semibold text-white/70 hover:text-white rounded-xl transition-all duration-200 hover:bg-white/10 border border-white/10 hover:border-white/20"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
          </svg>
          <span>Sign Out</span>
        </button>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="lg:pl-64">
      <!-- Header -->
      <header class="bg-white/80 backdrop-blur-md border-b border-gray-200/70 sticky top-0 z-10">
        <div class="px-4 sm:px-6 lg:px-8 py-4">
          <div class="flex items-center gap-4">
            <!-- Mobile menu button -->
            <button
              @click="sidebarOpen = !sidebarOpen"
              class="lg:hidden p-2 rounded-xl text-gray-600 hover:bg-gray-100 transition-colors -ml-2"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              </svg>
            </button>
            
            <!-- Page Title -->
            <div class="flex-1">
              <h2 class="text-xl sm:text-2xl font-bold text-gray-900" style="font-family: 'Sora', sans-serif;">
                {{ title }}
              </h2>
              <p v-if="subtitle" class="mt-1 text-xs sm:text-sm text-gray-500">
                {{ subtitle }}
              </p>
            </div>
            
            <!-- Actions slot -->
            <div v-if="$slots.actions" class="flex items-center space-x-3">
              <slot name="actions" />
            </div>
          </div>
        </div>
      </header>

      <!-- Flash Messages -->
      <div v-if="$page.props.flash?.success || $page.props.flash?.error" class="px-4 sm:px-6 lg:px-8 py-4">
        <div v-if="$page.props.flash?.success" 
             class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center space-x-3 animate-fade-in">
          <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          </svg>
          <span>{{ $page.props.flash.success }}</span>
        </div>
        <div v-if="$page.props.flash?.error" 
             class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg flex items-center space-x-3 animate-fade-in">
          <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
          </svg>
          <span>{{ $page.props.flash.error }}</span>
        </div>
      </div>

      <!-- Page Content -->
      <div class="px-4 sm:px-6 lg:px-8 py-6">
        <slot />
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import NavLink from '@/Components/NavLink.vue'

const sidebarOpen = ref(false)
const page = usePage()

defineProps({
  title: String,
  subtitle: String,
})

const userInitials = computed(() => {
  const name = page.props.auth?.user?.full_name || ''
  return name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
})

const isSuperAdmin = computed(() => {
  const role = page.props.auth?.user?.role
  return role === 'super_admin'
})

const clientLogo = computed(() => {
  const user = page.props.auth?.user
  if (user?.role === 'client' || user?.role === 'pic') {
    return user?.client?.logo || null
  }
  return null
})

const clientCompanyName = computed(() => {
  const user = page.props.auth?.user
  if (user?.role === 'client' || user?.role === 'pic') {
    return user?.client?.company_name || null
  }
  return null
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
