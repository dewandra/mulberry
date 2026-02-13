<template>
  <AuthenticatedLayout title="User Management" subtitle="Manage users and their roles">
    <!-- Header with Search and Create Button -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div class="flex-1 max-w-md">
        <input
          type="text"
          v-model="form.search"
          @input="search"
          placeholder="Search users by name or email..."
          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
        />
      </div>
      <button
        @click="openCreateModal"
        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-sm transition-colors duration-150"
      >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Create User
      </button>
    </div>

    <!-- Filters -->
    <div class="mb-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
        <select
          v-model="form.role"
          @change="filter"
          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
        >
          <option value="">All Roles</option>
          <option value="super_admin">Super Admin</option>
          <option value="admin">Admin ACT</option>
          <option value="client">Client</option>
          <option value="pic">PIC</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Client</label>
        <select
          v-model="form.client_id"
          @change="filter"
          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
        >
          <option value="">All Clients</option>
          <option v-for="client in clients" :key="client.id" :value="client.id">
            {{ client.company_name }}
          </option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
        <select
          v-model="form.status"
          @change="filter"
          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
        >
          <option value="">All Status</option>
          <option value="1">Active</option>
          <option value="0">Inactive</option>
        </select>
      </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10">
                    <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white font-semibold">
                      {{ user.full_name.charAt(0).toUpperCase() }}
                    </div>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">{{ user.full_name }}</div>
                    <div class="text-sm text-gray-500">{{ user.email }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getRoleBadgeClass(user.role)" class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full">
                  {{ user.role_display }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ user.client ? user.client.company_name : '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="user.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full">
                  {{ user.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button
                  @click="openEditModal(user)"
                  class="text-blue-600 hover:text-blue-900 mr-4"
                >
                  Edit
                </button>
                <button
                  @click="deleteUser(user)"
                  class="text-red-600 hover:text-red-900"
                  :disabled="user.id === $page.props.auth.user.id"
                  :class="{ 'opacity-50 cursor-not-allowed': user.id === $page.props.auth.user.id }"
                >
                  Delete
                </button>
              </td>
            </tr>
            <tr v-if="users.data.length === 0">
              <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                No users found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="users.links.length > 3" class="bg-gray-50 px-6 py-3 flex items-center justify-between border-t border-gray-200">
        <div class="flex-1 flex justify-between sm:hidden">
          <Link
            v-if="users.prev_page_url"
            :href="users.prev_page_url"
            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
          >
            Previous
          </Link>
          <Link
            v-if="users.next_page_url"
            :href="users.next_page_url"
            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
          >
            Next
          </Link>
        </div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
          <div>
            <p class="text-sm text-gray-700">
              Showing <span class="font-medium">{{ users.from }}</span> to <span class="font-medium">{{ users.to }}</span> of <span class="font-medium">{{ users.total }}</span> results
            </p>
          </div>
          <div>
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
              <template v-for="(link, index) in users.links" :key="index">
                <Link
                  v-if="link.url"
                  :href="link.url"
                  :class="[
                    link.active ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                    index === 0 ? 'rounded-l-md' : '',
                    index === users.links.length - 1 ? 'rounded-r-md' : '',
                    'relative inline-flex items-center px-4 py-2 border text-sm font-medium'
                  ]"
                  v-html="link.label"
                />
                <span
                  v-else
                  :class="[
                    index === 0 ? 'rounded-l-md' : '',
                    index === users.links.length - 1 ? 'rounded-r-md' : '',
                    'relative inline-flex items-center px-4 py-2 border border-gray-300 bg-gray-100 text-gray-400 text-sm font-medium cursor-not-allowed'
                  ]"
                  v-html="link.label"
                />
              </template>
            </nav>
          </div>
        </div>
      </div>
    </div>

    <!-- User Modal -->
    <UserModal
      :show="showModal"
      :user="selectedUser"
      :clients="clients"
      @close="closeModal"
      @saved="handleSaved"
    />
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import UserModal from '@/Components/UserModal.vue'
import Swal from 'sweetalert2'

const props = defineProps({
  users: Object,
  clients: Array,
  filters: Object,
})

const form = reactive({
  search: props.filters.search || '',
  role: props.filters.role || '',
  client_id: props.filters.client_id || '',
  status: props.filters.status || '',
})

const showModal = ref(false)
const selectedUser = ref(null)

// Simple debounce implementation
let searchTimeout = null
const search = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    filter()
  }, 300)
}

const filter = () => {
  router.get(route('users.index'), {
    search: form.search,
    role: form.role,
    client_id: form.client_id,
    status: form.status,
  }, {
    preserveState: true,
    preserveScroll: true,
  })
}

const getRoleBadgeClass = (role) => {
  const classes = {
    super_admin: 'bg-purple-100 text-purple-800',
    admin: 'bg-blue-100 text-blue-800',
    client: 'bg-green-100 text-green-800',
    pic: 'bg-orange-100 text-orange-800',
  }
  return classes[role] || 'bg-gray-100 text-gray-800'
}

const openCreateModal = () => {
  selectedUser.value = null
  showModal.value = true
}

const openEditModal = (user) => {
  selectedUser.value = user
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  selectedUser.value = null
}

const handleSaved = () => {
  closeModal()
  router.reload({ only: ['users'] })
}

const deleteUser = (user) => {
  Swal.fire({
    title: 'Delete User?',
    html: `Are you sure you want to delete <strong>${user.full_name}</strong>?<br><small class="text-gray-500">This action cannot be undone.</small>`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc2626',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Yes, delete it!',
    cancelButtonText: 'Cancel',
    reverseButtons: true
  }).then((result) => {
    if (result.isConfirmed) {
      router.delete(route('users.destroy', user.id), {
        preserveScroll: true,
        onSuccess: () => {
          Swal.fire({
            title: 'Deleted!',
            text: 'User has been deleted successfully.',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
          })
        },
        onError: () => {
          Swal.fire({
            title: 'Error!',
            text: 'Failed to delete user. Please try again.',
            icon: 'error',
            confirmButtonColor: '#3b82f6'
          })
        }
      })
    }
  })
}
</script>
