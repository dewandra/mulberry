<template>
  <AuthenticatedLayout title="Client Management" subtitle="Manage client companies">
    <!-- Header with Search and Create Button -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div class="flex-1 max-w-md">
        <input
          type="text"
          v-model="form.search"
          @input="search"
          placeholder="Search clients by name, contact, or email..."
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
        Create Client
      </button>
    </div>

    <!-- Filter -->
    <div class="mb-6 max-w-xs">
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

    <!-- Clients Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Logo</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact Person</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="client in clients.data" :key="client.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-6 py-4 whitespace-nowrap align-middle">
                <div v-if="client.logo" class="w-12 h-12 flex items-center justify-center">
                  <img :src="client.logo" :alt="client.company_name" class="max-w-full max-h-full object-contain" />
                </div>
                <div v-else class="w-12 h-12 bg-gray-100 rounded flex items-center justify-center">
                  <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap align-middle">
                <div class="text-sm font-medium text-gray-900">{{ client.company_name }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 align-middle">
                {{ client.contact_person }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 align-middle">
                {{ client.email }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 align-middle">
                {{ client.phone }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap align-middle">
                <span :class="client.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full">
                  {{ client.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium align-middle">
                <button
                  @click="openEditModal(client)"
                  class="text-blue-600 hover:text-blue-900 mr-4"
                >
                  Edit
                </button>
                <button
                  @click="deleteClient(client)"
                  class="text-red-600 hover:text-red-900"
                >
                  Delete
                </button>
              </td>
            </tr>
            <tr v-if="clients.data.length === 0">
              <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                No clients found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="clients.links.length > 3" class="bg-gray-50 px-6 py-3 flex items-center justify-between border-t border-gray-200">
        <div class="flex-1 flex justify-between sm:hidden">
          <Link
            v-if="clients.prev_page_url"
            :href="clients.prev_page_url"
            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
          >
            Previous
          </Link>
          <Link
            v-if="clients.next_page_url"
            :href="clients.next_page_url"
            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
          >
            Next
          </Link>
        </div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
          <div>
            <p class="text-sm text-gray-700">
              Showing <span class="font-medium">{{ clients.from }}</span> to <span class="font-medium">{{ clients.to }}</span> of <span class="font-medium">{{ clients.total }}</span> results
            </p>
          </div>
          <div>
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
              <template v-for="(link, index) in clients.links" :key="index">
                <Link
                  v-if="link.url"
                  :href="link.url"
                  :class="[
                    link.active ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                    index === 0 ? 'rounded-l-md' : '',
                    index === clients.links.length - 1 ? 'rounded-r-md' : '',
                    'relative inline-flex items-center px-4 py-2 border text-sm font-medium'
                  ]"
                  v-html="link.label"
                />
                <span
                  v-else
                  :class="[
                    index === 0 ? 'rounded-l-md' : '',
                    index === clients.links.length - 1 ? 'rounded-r-md' : '',
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

    <!-- Client Modal -->
    <ClientModal
      :show="showModal"
      :client="selectedClient"
      @close="closeModal"
      @saved="handleSaved"
    />
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import ClientModal from '@/Components/ClientModal.vue'
import Swal from 'sweetalert2'

const props = defineProps({
  clients: Object,
  filters: Object,
})

const form = reactive({
  search: props.filters.search || '',
  status: props.filters.status || '',
})

const showModal = ref(false)
const selectedClient = ref(null)

// Simple debounce implementation
let searchTimeout = null
const search = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    filter()
  }, 300)
}

const filter = () => {
  router.get(route('clients.index'), {
    search: form.search,
    status: form.status,
  }, {
    preserveState: true,
    preserveScroll: true,
  })
}

const openCreateModal = () => {
  selectedClient.value = null
  showModal.value = true
}

const openEditModal = (client) => {
  selectedClient.value = client
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  selectedClient.value = null
}

const handleSaved = () => {
  closeModal()
  router.reload({ only: ['clients'] })
}

const deleteClient = (client) => {
  Swal.fire({
    title: 'Delete Client?',
    html: `Are you sure you want to delete <strong>${client.company_name}</strong>?<br><small class="text-gray-500">This action cannot be undone.</small>`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc2626',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Yes, delete it!',
    cancelButtonText: 'Cancel',
    reverseButtons: true
  }).then((result) => {
    if (result.isConfirmed) {
      router.delete(route('clients.destroy', client.id), {
        preserveScroll: true,
        onSuccess: () => {
          Swal.fire({
            title: 'Deleted!',
            text: 'Client has been deleted successfully.',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
          })
        },
        onError: (errors) => {
          Swal.fire({
            title: 'Error!',
            text: errors.error || 'Failed to delete client. Please try again.',
            icon: 'error',
            confirmButtonColor: '#3b82f6'
          })
        }
      })
    }
  })
}
</script>
