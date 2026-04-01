<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <Head title="Sign in" />

    <div class="max-w-md w-full">
      <!-- Login Card -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
        <!-- Header -->
        <div class="text-center mb-8">
          <h2 class="text-3xl font-bold text-gray-900" style="font-family: 'Sora', sans-serif;">
            Sign in
          </h2>
          <p class="mt-2 text-sm text-gray-600">
            Mullbery Project Tracking System
          </p>
        </div>

        <!-- Status Message -->
        <div v-if="status" class="mb-6 p-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">
          {{ status }}
        </div>

        <!-- Login Form -->
        <form @submit.prevent="submit" class="space-y-5">
          <!-- Email Field -->
          <div>
            <input
              id="email"
              type="email"
              v-model="form.email"
              placeholder="Email address"
              required
              autofocus
              autocomplete="email"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all placeholder-gray-400"
              :class="{ 'border-red-500': form.errors.email }"
            />
            <p v-if="form.errors.email" class="mt-2 text-sm text-red-600">
              {{ form.errors.email }}
            </p>
          </div>

          <!-- Password Field -->
          <div class="relative">
            <input
              id="password"
              :type="showPassword ? 'text' : 'password'"
              v-model="form.password"
              placeholder="Password"
              required
              autocomplete="current-password"
              class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all placeholder-gray-400"
              :class="{ 'border-red-500': form.errors.password }"
            />
            <button
              type="button"
              @click="showPassword = !showPassword"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
              tabindex="-1"
            >
              <!-- Eye icon (show) -->
              <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
              <!-- Eye-off icon (hide) -->
              <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
              </svg>
            </button>
            <p v-if="form.errors.password" class="mt-2 text-sm text-red-600">
              {{ form.errors.password }}
            </p>
          </div>

          <!-- Sign In Button -->
          <button
            type="submit"
            :disabled="form.processing"
            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="!form.processing">Sign in</span>
            <span v-else>Signing in...</span>
          </button>
        </form>

        <!-- Access Rules -->
        <div class="mt-8 pt-6 border-t border-gray-200">
          <h3 class="text-sm font-semibold text-gray-900 mb-3">Access rules</h3>
          <ul class="space-y-2 text-sm text-gray-600">
            <li class="flex items-start">
              <svg class="w-4 h-4 mt-0.5 mr-2 text-gray-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <circle cx="10" cy="10" r="2"/>
              </svg>
              <span>Only projects assigned to your account are visible</span>
            </li>
            <li class="flex items-start">
              <svg class="w-4 h-4 mt-0.5 mr-2 text-gray-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <circle cx="10" cy="10" r="2"/>
              </svg>
              <span>Each client can only view their own projects</span>
            </li>
            <li class="flex items-start">
              <svg class="w-4 h-4 mt-0.5 mr-2 text-gray-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <circle cx="10" cy="10" r="2"/>
              </svg>
              <span>Multiple persons in charge may exist within one client</span>
            </li>
            <li class="flex items-start">
              <svg class="w-4 h-4 mt-0.5 mr-2 text-gray-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <circle cx="10" cy="10" r="2"/>
              </svg>
              <span>Project status is read-only for client users</span>
            </li>
          </ul>
        </div>
      </div>

      <!-- App version footer -->
      <div class="mt-5 flex items-center justify-center gap-1.5">
        <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
        </svg>
        <span class="text-[11px] text-gray-400 font-mono tracking-wide">
          v{{ appVersion }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Head, useForm, usePage } from '@inertiajs/vue3'

const page = usePage()
const appVersion = computed(() => page.props.app_version ?? '1.0.0')
const showPassword = ref(false)

defineProps({
  canResetPassword: { type: Boolean },
  status: { type: String },
})

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const submit = () => {
  form.post('/login', {
    onFinish: () => form.reset('password'),
  })
}
</script>