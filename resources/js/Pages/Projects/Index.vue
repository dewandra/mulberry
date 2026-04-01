<template>
  <AuthenticatedLayout title="Project Status Dashboard">

    <!-- â”€â”€â”€ Dashboard Header â”€â”€â”€ -->
    <div class="mb-6 flex flex-col lg:flex-row lg:items-start lg:justify-between gap-5">
      <!-- Left: avatar + title + meta -->
      <div class="flex items-start gap-4">
        <!-- User avatar / client logo -->
        <div class="flex-shrink-0 mt-0.5">
          <!-- Client logo: smooth rounded avatar with ring + shadow -->
          <div v-if="pageClientLogo"
            class="w-14 h-14 rounded-2xl ring-2 ring-white shadow-md overflow-hidden bg-white flex items-center justify-center"
            style="box-shadow: 0 2px 12px rgba(0,0,0,0.10), 0 0 0 2px #e2e8f0;"
          >
            <img
              :src="pageClientLogo"
              :alt="pageClientCompanyName"
              class="w-full h-full object-cover"
              style="image-rendering: auto;"
            />
          </div>
          <div v-else
            class="w-14 h-14 rounded-2xl flex items-center justify-center font-bold text-sm"
            style="background: linear-gradient(135deg, #eef2ff 0%, #f0f9ff 50%, #faf5ff 100%); border: 1px solid #e0e7ff; box-shadow: 0 2px 12px rgba(99,102,241,0.12); color: #4f46e5;"
          >
            {{ pageUserInitials }}
          </div>
        </div>
        <!-- Title + subtitle -->
        <div>
          <h1 class="text-2xl font-bold text-gray-900" style="font-family:'Sora',sans-serif;">Project Status Dashboard</h1>
          <p class="text-sm text-gray-500 mt-0.5">
            <span class="font-medium text-gray-700">{{ pageDisplayName }}</span>
            <span class="mx-1.5 text-gray-300">&middot;</span>
            Showing <span class="font-medium text-gray-700">{{ projects.from }}&ndash;{{ projects.to }}</span> of
            <span class="font-medium text-gray-700">{{ projects.total }}</span> projects
          </p>
          <p class="text-xs text-gray-400 mt-0.5">
            Report Period:
            <span>{{ form.from || '–' }}</span> to <span>{{ form.to || '–' }}</span>
            <span class="mx-1">&middot;</span>Generated {{ today }}
          </p>
        </div>
      </div>

      <!-- Right: Search -->
      <div class="w-full lg:w-72 flex-shrink-0">
        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest mb-1.5">Search</p>
        <div class="relative">
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
          <input
            type="text"
            v-model="form.search"
            @input="search"
            placeholder="Search by project name..."
            class="w-full pl-9 pr-4 py-2.5 text-sm rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-shadow"
          />
        </div>
      </div>
    </div>


    <!-- ——— Stats Cards ——— -->
    <div class="mb-6 grid grid-cols-2 sm:grid-cols-4 gap-4">
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest">Active</p>
        <p class="text-3xl font-bold text-gray-900 mt-2">{{ projects.total }}</p>
      </div>
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <p class="text-xs font-semibold text-amber-500 uppercase tracking-widest">Awaiting Feedback</p>
        <p class="text-3xl font-bold text-gray-900 mt-2">{{ awaitingFeedback }}</p>
      </div>
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <p class="text-xs font-semibold text-red-500 uppercase tracking-widest">High Priority</p>
        <p class="text-3xl font-bold text-gray-900 mt-2">{{ highPriority }}</p>
      </div>
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <p class="text-xs font-semibold text-blue-500 uppercase tracking-widest">This Week</p>
        <p class="text-3xl font-bold text-gray-900 mt-2">{{ thisWeek }}</p>
      </div>
    </div>

    <!-- ——— Filter Bar ——— -->
    <div class="mb-6 bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4">
      <div class="flex flex-wrap items-end gap-4">

        <!-- Date range preset (cosmetic display) -->
        <div class="flex-shrink-0 min-w-[140px]">
          <label class="block text-[10px] font-semibold text-gray-400 uppercase tracking-widest mb-1.5">Date Range</label>
          <select class="w-full text-sm rounded-lg border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100" @change="applyDatePreset($event.target.value)">
            <option value="">Custom</option>
            <option value="today">Today</option>
            <option value="yesterday">Yesterday</option>
            <option value="7d">Last 7 days</option>
            <option value="thisweek">This week</option>
            <option value="thismonth">This month</option>
          </select>
        </div>

        <div class="flex-shrink-0">
          <label class="block text-[10px] font-semibold text-gray-400 uppercase tracking-widest mb-1.5">From</label>
          <input v-model="form.from" type="date" @change="filter" class="text-sm rounded-lg border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100" />
        </div>
        <div class="flex-shrink-0">
          <label class="block text-[10px] font-semibold text-gray-400 uppercase tracking-widest mb-1.5">To</label>
          <input v-model="form.to" type="date" @change="filter" class="text-sm rounded-lg border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100" />
        </div>

        <div class="w-px h-8 bg-gray-200 self-end mb-1 hidden sm:block" />

        <!-- Status filter -->
        <div class="flex-shrink-0 min-w-[130px]">
          <label class="block text-[10px] font-semibold text-gray-400 uppercase tracking-widest mb-1.5">Status</label>
          <select v-model="form.status" @change="filter" class="w-full text-sm rounded-lg border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            <option value="">All</option>
            <option value="brief">Brief</option>
            <option value="scheduled">Scheduled</option>
            <option value="work_in_progress">Work In Progress</option>
            <option value="preview_sent">Preview Sent</option>
            <option value="feedback_received">Feedback Received</option>
            <option value="artwork_approved">Artwork Approved</option>
            <option value="final_artwork_preparation">Final Artwork Prep</option>
            <option value="fa_sent">FA Sent</option>
            <option value="project_closed">Project Closed</option>
          </select>
        </div>

        <!-- Priority filter -->
        <div class="flex-shrink-0 min-w-[110px]">
          <label class="block text-[10px] font-semibold text-gray-400 uppercase tracking-widest mb-1.5">Priority</label>
          <select v-model="form.priority" @change="filter" class="w-full text-sm rounded-lg border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            <option value="">All</option>
            <option value="high">High</option>
            <option value="normal">Normal</option>
            <option value="low">Low</option>
          </select>
        </div>

        <!-- Spacer + Create button -->
        <div class="flex-1" />
        <button
          v-if="canManage"
          @click="openCreateModal"
          class="flex-shrink-0 inline-flex items-center gap-2 px-4 py-2.5 bg-gray-900 hover:bg-gray-800 text-white text-sm font-semibold rounded-xl shadow-sm transition-colors duration-150"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          New Project
        </button>
      </div>
    </div>

    <!-- ——— Projects Grid ——— -->
    <div v-if="projects.data.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
      <div
        v-for="project in projects.data"
        :key="project.id"
        class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden flex flex-col"
      >
        <!-- ——— Thumbnail ——— -->
        <Link :href="route('projects.show', project.id)" class="group block relative overflow-hidden flex-shrink-0" style="height: 200px;">
          <!-- Actual thumbnail -->
          <img
            v-if="project.thumbnail"
            :src="project.thumbnail"
            :alt="project.project_name"
            class="absolute inset-0 w-full h-full object-cover scale-105 group-hover:scale-110 transition-transform duration-500 ease-in-out"
          />
          <!-- Elegant bottom fade to white (single smooth gradient, no harsh mask) -->
          <div
            v-if="project.thumbnail"
            class="absolute inset-0 pointer-events-none"
            style="background: linear-gradient(
              to bottom,
              rgba(255,255,255,0)   0%,
              rgba(255,255,255,0)  45%,
              rgba(255,255,255,0.5) 65%,
              rgba(255,255,255,0.88) 82%,
              rgba(255,255,255,1)  100%
            );"
          />
          <!-- Gradient fallback (no image) -->
          <div v-else class="absolute inset-0 w-full h-full" :class="coverBg(project.status)" />

          <!-- Priority badge -->
          <div class="absolute top-3 left-3">
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold shadow-sm" :class="priorityBadgeClass(project.priority)">
              {{ project.priority_display }}
            </span>
          </div>
        </Link>


        <!-- ——— Card Body ——— -->
        <div class="flex flex-col flex-1 px-4 pt-3 pb-3">
          <!-- Name -->
          <Link :href="route('projects.show', project.id)">
            <h3 class="text-sm font-bold text-gray-900 leading-snug line-clamp-2 hover:text-gray-700 transition-colors">
              {{ project.project_name }}
            </h3>
          </Link>

          <!-- Updated timestamp -->
          <div class="flex items-center gap-1 mt-1.5 mb-3">
            <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-[10px] font-semibold text-blue-500 tracking-wide uppercase">
              Updated {{ formatUpdated(project.updated_at) }}
            </span>
          </div>

          <!-- Separator -->
          <div class="border-t border-gray-100 mb-3" />

          <!-- STATUS row -->
          <div class="flex items-center justify-between mb-1.5">
            <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Status</span>
            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold" :class="statusClass(project.status)">{{ project.status_display }}</span>
          </div>
          <!-- Progress bar (status indicator) -->
          <div class="w-full h-1.5 bg-gray-100 rounded-full mb-3 overflow-hidden">
            <div class="h-full rounded-full transition-all duration-500" :class="statusBarClass(project.status)" :style="{ width: statusProgress(project.status) }" />
          </div>

          <!-- Separator -->
          <div class="border-t border-gray-100 mb-3" />

          <!-- DEADLINE row -->
          <div class="flex items-center justify-between">
            <div>
              <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest mb-0.5">Deadline</p>
              <p class="text-xs font-semibold text-gray-800">
                <span v-if="project.deadline">{{ formatDate(project.deadline) }}</span>
                <span v-else class="text-gray-400 italic font-normal">No deadline</span>
              </p>
            </div>
            <!-- Arrow button -->
            <Link
              :href="route('projects.show', project.id)"
              class="w-8 h-8 rounded-full border border-gray-200 flex items-center justify-center text-gray-400 hover:text-gray-700 hover:border-gray-400 transition-colors flex-shrink-0"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
              </svg>
            </Link>
          </div>

          <!-- Actions (admin only) -->
          <div v-if="canManage" class="flex gap-2 mt-3 pt-3 border-t border-gray-100">
            <button
              @click.stop.prevent="openEditModal(project)"
              class="flex-1 px-3 py-1.5 text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors"
            >
              Edit
            </button>
            <!-- <button
              @click.stop.prevent="deleteProject(project)"
              class="flex-1 px-3 py-1.5 text-xs font-semibold text-red-700 bg-red-50 hover:bg-red-100 rounded-lg transition-colors"
            >
              Delete
            </button> -->
          </div>
        </div>
      </div>
    </div>


    <!-- Empty State -->
    <div v-else class="bg-white rounded-2xl border border-gray-100 shadow-sm py-24 text-center">
      <svg class="w-14 h-14 text-gray-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
      </svg>
      <p class="text-gray-500 font-semibold">No projects found</p>
      <p class="text-gray-400 text-sm mt-1">Try adjusting your search or filters</p>
    </div>

    <!-- Pagination -->
    <div v-if="projects.links.length > 3" class="mt-8 flex items-center justify-between">
      <p class="text-sm text-gray-500">
        Showing <span class="font-semibold text-gray-900">{{ projects.from }}</span>&ndash;<span class="font-semibold text-gray-900">{{ projects.to }}</span>
        of <span class="font-semibold text-gray-900">{{ projects.total }}</span> projects
      </p>
      <nav class="inline-flex rounded-xl shadow-sm overflow-hidden border border-gray-200">
        <template v-for="(link, index) in projects.links" :key="index">
          <Link
            v-if="link.url"
            :href="link.url"
            :class="link.active ? 'bg-gray-900 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'"
            class="relative inline-flex items-center px-4 py-2 border-r border-gray-200 text-sm font-medium last:border-r-0 transition-colors"
            v-html="link.label"
          />
          <span
            v-else
            class="relative inline-flex items-center px-4 py-2 border-r border-gray-200 bg-gray-50 text-gray-300 text-sm font-medium cursor-not-allowed last:border-r-0"
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
import {
  coverBg, priorityBadgeClass, priorityClass,
  statusClass, statusBarClass, statusProgress,
  formatDate, formatUpdated, timeAgo,
} from '@/composables/useProject'

const page = usePage()

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

// â”€â”€ Computed stats â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
const awaitingFeedback = computed(() => props.projects.data.filter(p => p.status === 'preview_sent').length)
const highPriority     = computed(() => props.projects.data.filter(p => p.priority === 'high').length)
const thisWeek         = computed(() => {
  const now = Date.now()
  return props.projects.data.filter(p => {
    const diff = now - new Date(p.updated_at).getTime()
    return diff < 7 * 24 * 60 * 60 * 1000
  }).length
})

const today = computed(() => new Date().toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }))

// â”€â”€ User info for dashboard header â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
const pageClientLogo = computed(() => {
  const user = page.props.auth?.user
  if (user?.role === 'client' || user?.role === 'pic') return user?.client?.logo || null
  return null
})
const pageClientCompanyName = computed(() => {
  const user = page.props.auth?.user
  if (user?.role === 'client' || user?.role === 'pic') return user?.client?.company_name || ''
  return ''
})
const pageUserInitials = computed(() => {
  const name = page.props.auth?.user?.full_name || ''
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
})
const pageDisplayName = computed(() => {
  const user = page.props.auth?.user
  if (user?.role === 'client' || user?.role === 'pic') {
    return user?.client?.company_name || user?.full_name || ''
  }
  return user?.full_name || ''
})

// â”€â”€ Date preset helper â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
const applyDatePreset = (preset) => {
  const now  = new Date()
  const pad  = d => d.toISOString().slice(0, 10)
  const ago  = (days) => { const d = new Date(); d.setDate(d.getDate() - days); return d }

  if (preset === 'today')     { form.from = pad(now); form.to = pad(now) }
  else if (preset === 'yesterday') { const y = ago(1); form.from = pad(y); form.to = pad(y) }
  else if (preset === '7d')   { form.from = pad(ago(7)); form.to = pad(now) }
  else if (preset === 'thisweek') {
    const d = new Date(); d.setDate(d.getDate() - d.getDay())
    form.from = pad(d); form.to = pad(now)
  }
  else if (preset === 'thismonth') {
    const d = new Date(); d.setDate(1)
    form.from = pad(d); form.to = pad(now)
  }
  else { form.from = ''; form.to = '' }
  filter()
}

// â”€â”€ Search & filter â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
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

// â”€â”€ Modal actions â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
const openCreateModal = () => { selectedProject.value = null; modalKey.value++; showModal.value = true }
const openEditModal   = (p) => { selectedProject.value = p;    modalKey.value++; showModal.value = true }
const closeModal      = ()  => { showModal.value = false; selectedProject.value = null }
const handleSaved     = ()  => { closeModal(); router.reload({ only: ['projects'] }) }

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
        onSuccess: () => Swal.fire({ title: 'Deleted!', text: 'Project deleted.', icon: 'success', timer: 2000, showConfirmButton: false }),
        onError:   () => Swal.fire({ title: 'Error!',   text: 'Failed to delete.',  icon: 'error',   confirmButtonColor: '#3b82f6' }),
      })
    }
  })
}

// â”€â”€â”€ Style helpers moved to @/composables/useProject.js â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// coverBg, priorityBadgeClass, priorityClass, statusClass,
// statusBarClass, statusProgress, formatDate, formatUpdated, timeAgo
</script>
