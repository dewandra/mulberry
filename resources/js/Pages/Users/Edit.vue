<template>
  <AuthenticatedLayout title="Edit User" subtitle="Update user information">
    <div class="max-w-2xl">
      <!-- Back Button -->
      <Link :href="route('users.index')" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 mb-6">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Back to Users
      </Link>

      <!-- Form Card -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form @submit.prevent="submit">
          <!-- Full Name -->
          <div class="mb-4">
            <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">
              Full Name <span class="text-red-500">*</span>
            </label>
            <input
              id="full_name"
              type="text"
              v-model="form.full_name"
              class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
              :class="{ 'border-red-500': form.errors.full_name }"
              required
            />
            <p v-if="form.errors.full_name" class="mt-1 text-sm text-red-600">{{ form.errors.full_name }}</p>
          </div>

          <!-- Email -->
          <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
              Email <span class="text-red-500">*</span>
            </label>
            <input
              id="email"
              type="email"
              v-model="form.email"
              class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
              :class="{ 'border-red-500': form.errors.email }"
              required
            />
            <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
          </div>

          <!-- Password -->
          <div class="mb-4">
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
              Password
            </label>
            <input
              id="password"
              type="password"
              v-model="form.password"
              class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
              :class="{ 'border-red-500': form.errors.password }"
            />
            <p class="mt-1 text-xs text-gray-500">Leave blank to keep current password</p>
            <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</p>
          </div>

          <!-- Role -->
          <div class="mb-4">
            <label for="role" class="block text-sm font-medium text-gray-700 mb-1">
              Role <span class="text-red-500">*</span>
            </label>
            <select
              id="role"
              v-model="form.role"
              class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
              :class="{ 'border-red-500': form.errors.role }"
              :disabled="isEditingSelf"
              required
            >
              <option value="">Select Role</option>
              <option value="super_admin">Super Admin</option>
              <option value="admin">Admin ACT</option>
              <option value="client">Client</option>
              <option value="pic">PIC</option>
            </select>
            <p v-if="isEditingSelf" class="mt-1 text-xs text-amber-600">You cannot change your own role</p>
            <p v-if="form.errors.role" class="mt-1 text-sm text-red-600">{{ form.errors.role }}</p>
          </div>

          <!-- Client (conditional) -->
          <div v-if="showClientField" class="mb-4">
            <label for="client_id" class="block text-sm font-medium text-gray-700 mb-1">
              Client <span class="text-red-500">*</span>
            </label>
            <select
              id="client_id"
              v-model="form.client_id"
              class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
              :class="{ 'border-red-500': form.errors.client_id }"
              :required="showClientField"
            >
              <option value="">Select Client</option>
              <option v-for="client in clients" :key="client.id" :value="client.id">
                {{ client.company_name }}
              </option>
            </select>
            <p v-if="form.errors.client_id" class="mt-1 text-sm text-red-600">{{ form.errors.client_id }}</p>
          </div>

          <!-- Is Active -->
          <div class="mb-6">
            <label class="flex items-center">
              <input
                type="checkbox"
                v-model="form.is_active"
                :disabled="isEditingSelf"
                class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 disabled:opacity-50 disabled:cursor-not-allowed"
              />
              <span class="ml-2 text-sm text-gray-700">Active</span>
            </label>
            <p v-if="isEditingSelf" class="mt-1 text-xs text-amber-600">You cannot deactivate your own account</p>
            <p v-else class="mt-1 text-xs text-gray-500">Inactive users cannot log in</p>
          </div>

          <!-- Info -->
          <div v-if="user.updated_by" class="mb-6 p-3 bg-gray-50 rounded-lg text-sm text-gray-600">
            Last updated {{ formatDate(user.updated_at) }}
            <span v-if="user.updater"> by {{ user.updater.full_name }}</span>
          </div>

          <!-- Buttons -->
          <div class="flex items-center justify-end space-x-3">
            <Link
              :href="route('users.index')"
              class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition-colors duration-150"
            >
              Cancel
            </Link>
            <button
              type="submit"
              :disabled="form.processing"
              class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-sm transition-colors duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ form.processing ? 'Updating...' : 'Update User' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, useForm, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Swal from 'sweetalert2'

const props = defineProps({
  user: Object,
  clients: Array,
})

const page = usePage()

const form = useForm({
  full_name: props.user.full_name,
  email: props.user.email,
  password: '',
  role: props.user.role,
  client_id: props.user.client_id || '',
  is_active: props.user.is_active,
})

const isEditingSelf = computed(() => {
  return props.user.id === page.props.auth.user.id
})

const showClientField = computed(() => {
  return ['client', 'pic'].includes(form.role)
})

const submit = () => {
  form.put(route('users.update', props.user.id), {
    onSuccess: () => {
      Swal.fire({
        title: 'Success!',
        text: 'User has been updated successfully.',
        icon: 'success',
        timer: 2000,
        showConfirmButton: false
      })
    },
    onError: () => {
      Swal.fire({
        title: 'Error!',
        text: 'Failed to update user. Please check the form and try again.',
        icon: 'error',
        confirmButtonColor: '#3b82f6'
      })
    }
  })
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>
