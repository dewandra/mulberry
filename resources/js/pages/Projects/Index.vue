<template>
  <AuthenticatedLayout title="Projects" subtitle="Manage all creative projects">

    <!-- Header Row -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <!-- Search -->
      <div class="flex-1 max-w-md">
        <div class="relative">
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
          <input
            type="text"
            v-model="form.search"
            @input="search"
            placeholder="Search by project name or code..."
            class="w-full pl-10 pr-4 py-2 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
          />
        </div>
      </div>

      <!-- Create Button (admin/super_admin only) -->
      <button
        v-if="canManage"
        @click="openCreateModal"
        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-sm transition-colors duration-150"
      >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Create Project
      </button>
    </div>

    <!-- Filters Row -->
    <div class="mb-6 grid grid-cols-2 sm:grid-cols-4 gap-3">
      <div>
        <label class="block text-xs font-medium text-gray-500 mb-1 uppercase tracking-wide">Status</label>
        <select v-model="form.status" @change="filter" class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
          <option value="">All Status</option>
          <option value="brief">Brief</option>
          <option value="scheduled">Scheduled</option>
          <option value="work_in_progress">Work In Progress</option>
          <option value="preview_sent">Preview Sent</option>
          <option value="feedback_received">Feedback Received</option>
          <option value="artwork_approved">Artwork Approved</option>
          <option value="final_artwork_preparation">Final Artwork Preparation</option>
          <option value="fa_sent">FA Sent</option>
          <option value="project_closed">Project Closed</option>
        </select>
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-500 mb-1 uppercase tracking-wide">Priority</label>
        <select v-model="form.priority" @change="filter" class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
          <option value="">All Priority</option>
          <option value="high">High</option>
          <option value="normal">Normal</option>
          <option value="low">Low</option>
        </select>
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-500 mb-1 uppercase tracking-wide">From</label>
        <input v-model="form.from" type="date" @change="filter" class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-500 mb-1 uppercase tracking-wide">To</label>
        <input v-model="form.to" type="date" @change="filter" class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
      </div>
    </div>

    <!-- Stats Row -->
    <div class="mb-6 grid grid-cols-2 sm:grid-cols-4 gap-4">
      <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Active</p>
        <p class="text-2xl font-bold text-gray-900 mt-1">{{ projects.total }}</p>
      </div>
      <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
        <p class="text-xs font-medium text-amber-600 uppercase tracking-wide">Awaiting Feedback</p>
        <p class="text-2xl font-bold text-gray-900 mt-1">{{ awaitingFeedback }}</p>
      </div>
      <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
        <p class="text-xs font-medium text-red-600 uppercase tracking-wide">High Priority</p>
        <p class="text-2xl font-bold text-gray-900 mt-1">{{ highPriority }}</p>
      </div>
      <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
        <p class="text-xs font-medium text-blue-600 uppercase tracking-wide">This Page</p>
        <p class="text-2xl font-bold text-gray-900 mt-1">{{ projects.data.length }}</p>
      </div>
    </div>

    <!-- Projects Grid -->
    <div v-if="projects.data.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
      <Link
        v-for="project in projects.data"
        :key="project.id"
        :href="route('projects.show', project.id)"
        class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 overflow-hidden flex flex-col group cursor-pointer"
      >
        <!-- Card Image / Color Header -->
        <div class="h-32 relative overflow-hidden" :class="!project.thumbnail ? coverBg(project) : ''">
          <!-- Real thumbnail -->
          <img
            v-if="project.thumbnail"
            :src="project.thumbnail"
            :alt="project.project_name"
            class="w-full h-full object-cover"
          />
          <!-- Priority Badge -->
          <div class="absolute top-3 left-3">
            <span
              class="px-2.5 py-1 rounded-full text-xs font-bold shadow-sm"
              :class="priorityClass(project.priority)"
            >
              {{ project.priority_display }}
            </span>
          </div>
          <!-- Code Badge -->
          <div class="absolute top-3 right-3">
            <span class="bg-black bg-opacity-30 text-white text-xs px-2 py-0.5 rounded-full font-mono backdrop-blur-sm">
              {{ project.project_code }}
            </span>
          </div>
        </div>

        <!-- Card Body -->
        <div class="p-4 flex flex-col flex-1">
          <h3 class="text-sm font-semibold text-gray-900 leading-tight mb-1 line-clamp-2">
            {{ project.project_name }}
          </h3>
          <p class="text-xs text-gray-500 mb-3">{{ project.client?.company_name }}</p>

          <!-- Status Badge -->
          <span class="inline-flex self-start px-2.5 py-1 rounded-full text-xs font-medium mb-3" :class="statusClass(project.status)">
            {{ project.status_display }}
          </span>

          <!-- Deadline -->
          <div class="flex items-center text-xs text-gray-500 mb-4">
            <svg class="w-3.5 h-3.5 mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span v-if="project.deadline">{{ formatDate(project.deadline) }}</span>
            <span v-else class="italic text-gray-400">No deadline</span>
          </div>

          <!-- Updated timestamp -->
          <p class="text-xs text-gray-400 mt-auto mb-3">
            Updated {{ timeAgo(project.updated_at) }}
          </p>

          <!-- Actions (admin only) -->
          <div v-if="canManage" class="flex gap-2 mt-auto">
            <button
              @click.stop.prevent="openEditModal(project)"
              class="flex-1 px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors"
            >
              Edit
            </button>
            <button
              @click.stop.prevent="deleteProject(project)"
              class="flex-1 px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 hover:bg-red-100 rounded-lg transition-colors"
            >
              Delete
            </button>
          </div>
        </div>
      </Link>
    </div>

    <!-- Empty State -->
    <div v-else class="bg-white rounded-xl border border-gray-200 shadow-sm py-20 text-center">
      <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
      </svg>
      <p class="text-gray-500 font-medium">No projects found</p>
      <p class="text-gray-400 text-sm mt-1">Try adjusting your search or filters</p>
    </div>

    <!-- Pagination -->
    <div v-if="projects.links.length > 3" class="mt-6 flex items-center justify-between">
      <p class="text-sm text-gray-600">
        Showing <span class="font-medium">{{ projects.from }}</span> to <span class="font-medium">{{ projects.to }}</span> of <span class="font-medium">{{ projects.total }}</span> projects
      </p>
      <nav class="inline-flex rounded-lg shadow-sm -space-x-px overflow-hidden border border-gray-200">
        <template v-for="(link, index) in projects.links" :key="index">
          <Link
            v-if="link.url"
            :href="link.url"
            :class="[
              link.active ? 'bg-blue-50 text-blue-600 border-blue-400' : 'bg-white text-gray-600 hover:bg-gray-50',
              'relative inline-flex items-center px-4 py-2 border-r border-gray-200 text-sm font-medium last:border-r-0'
            ]"
            v-html="link.label"
          />
          <span
            v-else
            class="relative inline-flex items-center px-4 py-2 border-r border-gray-200 bg-gray-100 text-gray-400 text-sm font-medium cursor-not-allowed last:border-r-0"
            v-html="link.label"
          />
        </template>
      </nav>
    </div>

  </AuthenticatedLayout>

  <!-- Project Modal -->
  <ProjectModal
    :key="modalKey"
    :show="showModal"
    :project="selectedProject"
    :clients="clients"
    :pic-users="picUsers"
    @close="closeModal"
    @saved="handleSaved"
  />
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import ProjectModal from '@/Components/ProjectModal.vue'
import Swal from 'sweetalert2'

const props = defineProps({
  projects:   Object,
  clients:    Array,
  picUsers:   Array,
  filters:    Object,
  statuses:   Array,
  canManage:  Boolean,
})

const form = reactive({
  search:   props.filters?.search   || '',
  status:   props.filters?.status   || '',
  priority: props.filters?.priority || '',
  from:     props.filters?.from     || '',
  to:       props.filters?.to       || '',
})

const showModal       = ref(false)
const selectedProject = ref(null)
const modalKey        = ref(0)

// Stats computed from current page data
const awaitingFeedback = computed(() => props.projects.data.filter(p => p.status === 'preview_sent').length)
const highPriority     = computed(() => props.projects.data.filter(p => p.priority === 'high').length)

// Debounced search
let searchTimeout = null
const search = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => filter(), 300)
}

const filter = () => {
  router.get(route('projects.index'), {
    search:   form.search,
    status:   form.status,
    priority: form.priority,
    from:     form.from,
    to:       form.to,
  }, { preserveState: true, preserveScroll: true })
}

const openCreateModal = () => {
  selectedProject.value = null
  modalKey.value++
  showModal.value = true
}

const openEditModal = (project) => {
  selectedProject.value = project
  modalKey.value++
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  selectedProject.value = null
}

const handleSaved = () => {
  closeModal()
  router.reload({ only: ['projects'] })
}

const deleteProject = (project) => {
  Swal.fire({
    title: 'Delete Project?',
    html: `Are you sure you want to delete <strong>${project.project_name}</strong>?<br><small class="text-gray-500">This action cannot be undone.</small>`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc2626',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Yes, delete it!',
    cancelButtonText: 'Cancel',
    reverseButtons: true,
  }).then((result) => {
    if (result.isConfirmed) {
      router.delete(route('projects.destroy', project.id), {
        preserveScroll: true,
        onSuccess: () => {
          Swal.fire({ title: 'Deleted!', text: 'Project has been deleted successfully.', icon: 'success', timer: 2000, showConfirmButton: false })
        },
        onError: () => {
          Swal.fire({ title: 'Error!', text: 'Failed to delete project. Please try again.', icon: 'error', confirmButtonColor: '#3b82f6' })
        },
      })
    }
  })
}

// ─── Helpers ────────────────────────────────────────────────────────────────

const coverBg = (project) => {
  const map = {
    brief:                    'bg-gradient-to-br from-gray-400 to-gray-600',
    scheduled:                'bg-gradient-to-br from-blue-400 to-blue-600',
    work_in_progress:         'bg-gradient-to-br from-amber-400 to-orange-500',
    preview_sent:             'bg-gradient-to-br from-purple-400 to-purple-600',
    feedback_received:        'bg-gradient-to-br from-orange-400 to-red-500',
    artwork_approved:         'bg-gradient-to-br from-green-400 to-emerald-600',
    final_artwork_preparation:'bg-gradient-to-br from-teal-400 to-cyan-600',
    fa_sent:                  'bg-gradient-to-br from-indigo-400 to-indigo-600',
    project_closed:           'bg-gradient-to-br from-gray-700 to-gray-900',
  }
  return map[project.status] || 'bg-gradient-to-br from-gray-400 to-gray-600'
}

const priorityClass = (priority) => ({
  high:   'bg-red-500 text-white',
  normal: 'bg-blue-500 text-white',
  low:    'bg-gray-200 text-gray-700',
}[priority] || 'bg-gray-200 text-gray-700')

const statusClass = (status) => {
  const map = {
    brief:                    'bg-gray-100 text-gray-700',
    scheduled:                'bg-blue-100 text-blue-700',
    work_in_progress:         'bg-amber-100 text-amber-700',
    preview_sent:             'bg-purple-100 text-purple-700',
    feedback_received:        'bg-orange-100 text-orange-700',
    artwork_approved:         'bg-green-100 text-green-700',
    final_artwork_preparation:'bg-teal-100 text-teal-700',
    fa_sent:                  'bg-indigo-100 text-indigo-700',
    project_closed:           'bg-gray-800 text-gray-100',
  }
  return map[status] || 'bg-gray-100 text-gray-700'
}

const formatDate = (dateStr) => {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
}

const timeAgo = (dateStr) => {
  if (!dateStr) return ''
  const diffMs = Date.now() - new Date(dateStr).getTime()
  const mins  = Math.floor(diffMs / 60000)
  const hours = Math.floor(mins / 60)
  const days  = Math.floor(hours / 24)
  if (days > 0)  return `${days}d ago`
  if (hours > 0) return `${hours}h ago`
  if (mins > 0)  return `${mins}m ago`
  return 'just now'
}
</script>
