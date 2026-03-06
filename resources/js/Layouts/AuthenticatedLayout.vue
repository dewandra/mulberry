<template>
  <div class="min-h-screen" style="background: #f3f4f8;">
    <Head :title="title" />

    <!-- ───────────── TOP NAVBAR ───────────── -->
    <nav class="bg-white border-b border-gray-200/80 sticky top-0 z-50 shadow-sm">
      <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center h-16 gap-6">

          <!-- Logo -->
          <Link href="/dashboard" class="flex items-center gap-2.5 flex-shrink-0">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
              style="background: linear-gradient(135deg, #5b21b6, #7c3aed);">
              <span class="text-white font-black text-sm" style="font-family:'Sora',sans-serif;">M</span>
            </div>
            <div class="leading-none">
              <p class="text-base font-black text-gray-900 leading-none tracking-tight"
                style="font-family:'Sora',sans-serif;">Mulberry</p>
              <p class="text-[9px] text-gray-400 font-semibold tracking-widest uppercase leading-none mt-0.5">by ACT</p>
            </div>
          </Link>

          <!-- Main nav links -->
          <div class="hidden md:flex items-center gap-1 flex-shrink-0">
            <Link
              href="/dashboard"
              :class="route().current('dashboard')
                ? 'bg-gray-100 text-gray-900 font-semibold'
                : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'"
              class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition-colors duration-150 whitespace-nowrap"
            >
              <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v5a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zm10-3a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1v-7z" />
              </svg>
              Dashboard
            </Link>

            <Link
              href="/projects"
              :class="route().current('projects.*')
                ? 'bg-gray-100 text-gray-900 font-semibold'
                : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'"
              class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition-colors duration-150 whitespace-nowrap"
            >
              <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
              Projects
            </Link>

            <!-- Management Dropdown (Super Admin only) -->
            <div v-if="isSuperAdmin" class="relative flex-shrink-0">
              <button
                @click="managementOpen = !managementOpen"
                :class="(route().current('users.*') || route().current('clients.*'))
                  ? 'bg-gray-100 text-gray-900 font-semibold'
                  : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'"
                class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition-colors duration-150 whitespace-nowrap"
              >
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Management
                <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="managementOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>

              <!-- Dropdown panel -->
              <transition
                enter-active-class="transition ease-out duration-150"
                enter-from-class="opacity-0 scale-95 -translate-y-1"
                enter-to-class="opacity-100 scale-100 translate-y-0"
                leave-active-class="transition ease-in duration-100"
                leave-from-class="opacity-100 scale-100 translate-y-0"
                leave-to-class="opacity-0 scale-95 -translate-y-1"
              >
                <div v-if="managementOpen" class="absolute left-0 mt-1 w-48 bg-white rounded-xl shadow-lg border border-gray-200/80 py-1 z-50">
                  <Link
                    href="/users"
                    @click="managementOpen = false"
                    :class="route().current('users.*') ? 'bg-gray-50 text-gray-900 font-semibold' : 'text-gray-700'"
                    class="flex items-center gap-2.5 px-4 py-2.5 text-sm hover:bg-gray-50 transition-colors"
                  >
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Users
                  </Link>
                  <Link
                    href="/clients"
                    @click="managementOpen = false"
                    :class="route().current('clients.*') ? 'bg-gray-50 text-gray-900 font-semibold' : 'text-gray-700'"
                    class="flex items-center gap-2.5 px-4 py-2.5 text-sm hover:bg-gray-50 transition-colors"
                  >
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    Companies
                  </Link>
                </div>
              </transition>
            </div>
          </div>

          <!-- Spacer -->
          <div class="flex-1" />

          <!-- Right side: User info + Sign Out (no dropdown) -->
          <div class="hidden sm:flex items-center gap-3">
            <!-- Name + role -->
            <div class="text-right">
              <p class="text-sm font-semibold text-gray-900 leading-none">{{ $page.props.auth.user.full_name }}</p>
              <p class="text-xs text-gray-500 mt-0.5">{{ $page.props.auth.user.role_display }}</p>
            </div>
            <!-- Divider -->
            <div class="w-px h-8 bg-gray-200" />
            <!-- Sign Out button -->
            <button
              @click="logout"
              class="flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-150"
              title="Sign Out"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
              </svg>
              <span class="hidden lg:inline">Sign Out</span>
            </button>
          </div>

          <!-- Mobile hamburger -->
          <button
            @click="mobileMenuOpen = !mobileMenuOpen"
            class="md:hidden p-2 rounded-xl text-gray-600 hover:bg-gray-100 transition-colors"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path v-if="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>

        </div>
      </div>

      <!-- Mobile menu panel -->
      <transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0 -translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition ease-in duration-150"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-2"
      >
        <div v-if="mobileMenuOpen" class="md:hidden border-t border-gray-100 bg-white px-4 py-3 space-y-1">
          <Link href="/dashboard" @click="mobileMenuOpen = false" :class="route().current('dashboard') ? 'bg-gray-100 font-semibold text-gray-900' : 'text-gray-700'" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm hover:bg-gray-50 transition-colors">
            Dashboard
          </Link>
          <Link href="/projects" @click="mobileMenuOpen = false" :class="route().current('projects.*') ? 'bg-gray-100 font-semibold text-gray-900' : 'text-gray-700'" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm hover:bg-gray-50 transition-colors">
            Projects
          </Link>
          <template v-if="isSuperAdmin">
            <div class="pt-1 pb-0.5 px-3">
              <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Management</p>
            </div>
            <Link href="/users" @click="mobileMenuOpen = false" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition-colors">Users</Link>
            <Link href="/clients" @click="mobileMenuOpen = false" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition-colors">Companies</Link>
          </template>
        </div>
      </transition>
    </nav>

    <!-- ───────────── MAIN CONTENT ───────────── -->
    <main>
      <!-- Flash Messages -->
      <div v-if="$page.props.flash?.success || $page.props.flash?.error" class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 pt-5">
        <div v-if="$page.props.flash?.success"
             class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl flex items-center space-x-3 animate-fade-in">
          <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          </svg>
          <span>{{ $page.props.flash.success }}</span>
        </div>
        <div v-if="$page.props.flash?.error"
             class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl flex items-center space-x-3 animate-fade-in">
          <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
          </svg>
          <span>{{ $page.props.flash.error }}</span>
        </div>
      </div>

      <!-- Page Content -->
      <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <slot />
      </div>
    </main>
  </div>

  <!-- Backdrop to close management dropdown when clicking outside -->
  <div v-if="managementOpen" @click="managementOpen = false" class="fixed inset-0 z-40" />
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'

const page = usePage()

defineProps({
  title: String,
  subtitle: String,
})

const managementOpen = ref(false)
const mobileMenuOpen = ref(false)

const isSuperAdmin = computed(() => page.props.auth?.user?.role === 'super_admin')

const logout = () => {
  router.post('/logout')
}
</script>

<style scoped>
@keyframes fade-in {
  from { opacity: 0; transform: translateY(-6px); }
  to   { opacity: 1; transform: translateY(0); }
}
.animate-fade-in {
  animation: fade-in 0.25s ease-out;
}
</style>
