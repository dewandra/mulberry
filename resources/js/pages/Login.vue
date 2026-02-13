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
            Project Status Dashboard
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
          <div>
            <input
              id="password"
              type="password"
              v-model="form.password"
              placeholder="Password"
              required
              autocomplete="current-password"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all placeholder-gray-400"
              :class="{ 'border-red-500': form.errors.password }"
            />
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
    </div>
  </div>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3'

defineProps({
  canResetPassword: {
    type: Boolean,
  },
  status: {
    type: String,
  },
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