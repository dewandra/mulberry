<template>
  <AuthenticatedLayout :title="project.project_name" :subtitle="project.project_code">

    <!-- Back Link -->
    <div class="mb-4">
      <Link :href="route('projects.index')" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 transition-colors">
        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Back to Projects
      </Link>
    </div>

    <!-- Project Header Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-6">
      <!-- Thumbnail / Gradient Banner -->
      <div class="h-44 relative" :class="!project.thumbnail ? coverBg(project.status) : ''">
        <img v-if="project.thumbnail" :src="project.thumbnail" class="w-full h-full object-cover" :alt="project.project_name" />
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent flex flex-col justify-end px-6 pb-5">
          <div class="flex items-end justify-between flex-wrap gap-3">
            <div>
              <div class="flex items-center gap-2 mb-2">
                <span class="px-2.5 py-1 rounded-full text-xs font-bold" :class="priorityClass(project.priority)">
                  {{ project.priority_display }}
                </span>
                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-white/20 text-white backdrop-blur-sm">
                  {{ project.status_display }}
                </span>
              </div>
              <h1 class="text-2xl font-bold text-white" style="font-family:'Sora',sans-serif;">{{ project.project_name }}</h1>
              <p class="text-white/70 text-sm mt-0.5">{{ project.client?.company_name }} &bull; {{ project.project_code }}</p>
            </div>
            <!-- Admin Action Buttons (top-right on banner) -->
            <div v-if="canManage" class="flex flex-wrap gap-2">
              <button @click="showPreviewModal = true"
                class="px-4 py-2 text-sm font-semibold bg-white text-blue-700 rounded-xl hover:bg-blue-50 shadow-md transition-colors">
                📤 Send Preview
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Status Quick-Action Bar (admin only) -->
      <div v-if="canManage && nextAction" class="px-6 py-3 bg-gradient-to-r from-slate-50 to-gray-50 border-b border-gray-100 flex items-center justify-between gap-3">
        <p class="text-xs text-gray-500">Next step for this project:</p>
        <button
          @click="updateStatus(nextAction.status)"
          class="inline-flex items-center gap-1.5 px-4 py-1.5 text-sm font-semibold text-white rounded-lg shadow-sm transition-colors"
          :class="nextAction.btnClass"
        >
          <span>{{ nextAction.icon }}</span>
          <span>{{ nextAction.label }}</span>
        </button>
      </div>

      <!-- Meta Row -->
      <div class="grid grid-cols-2 sm:grid-cols-4 divide-x divide-y sm:divide-y-0 divide-gray-100">
        <div class="px-5 py-3">
          <p class="text-xs text-gray-400 uppercase tracking-wide font-medium">Deadline</p>
          <p class="text-sm font-semibold text-gray-800 mt-0.5">{{ project.deadline ? formatDate(project.deadline) : '—' }}</p>
        </div>
        <div class="px-5 py-3">
          <p class="text-xs text-gray-400 uppercase tracking-wide font-medium">Report Date</p>
          <p class="text-sm font-semibold text-gray-800 mt-0.5">{{ project.report_date ? formatDate(project.report_date) : '—' }}</p>
        </div>
        <div class="px-5 py-3">
          <p class="text-xs text-gray-400 uppercase tracking-wide font-medium">PICs</p>
          <div class="flex flex-wrap gap-1 mt-0.5">
            <span v-for="pic in project.pic_users" :key="pic.id" class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded-full text-xs font-medium">
              {{ pic.full_name }}
            </span>
            <span v-if="!project.pic_users?.length" class="text-sm text-gray-400">—</span>
          </div>
        </div>
        <div class="px-5 py-3">
          <p class="text-xs text-gray-400 uppercase tracking-wide font-medium">Previews</p>
          <p class="text-sm font-semibold text-gray-800 mt-0.5">{{ project.previews?.length ?? 0 }} version(s)</p>
        </div>
      </div>
    </div>

    <!-- Status Timeline Stepper -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
      <h2 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-4">Project Status</h2>
      <div class="flex items-center overflow-x-auto pb-2">
        <template v-for="(s, index) in statuses" :key="s">
          <div class="flex flex-col items-center min-w-max">
            <div
              class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold border-2 transition-all cursor-default"
              :class="{
                'bg-blue-600 border-blue-600 text-white ring-4 ring-blue-100': s === project.status,
                'bg-green-100 border-green-500 text-green-700': isCompleted(s),
                'bg-gray-100 border-gray-300 text-gray-400': !isCompleted(s) && s !== project.status,
              }"
            >
              <svg v-if="isCompleted(s)" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
              </svg>
              <span v-else>{{ index + 1 }}</span>
            </div>
            <p
              class="text-xs mt-1.5 text-center max-w-16 leading-tight"
              :class="{
                'text-blue-600 font-semibold': s === project.status,
                'text-green-600 font-medium': isCompleted(s),
                'text-gray-400': !isCompleted(s) && s !== project.status,
              }"
            >{{ statusLabel(s) }}</p>
          </div>
          <div v-if="index < statuses.length - 1" class="flex-1 min-w-8 h-0.5 mb-5 mx-1"
            :class="isCompleted(statuses[index + 1]) || statuses[index + 1] === project.status ? 'bg-blue-400' : 'bg-gray-200'" />
        </template>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <!-- Left: Previews & Feedback Column -->
      <div class="lg:col-span-2 space-y-6">

        <!-- No previews yet -->
        <div v-if="!project.previews?.length" class="bg-white rounded-2xl border border-gray-200 shadow-sm px-6 py-16 text-center">
          <span class="text-4xl mb-3 block">🎨</span>
          <p class="text-gray-500 font-medium">No previews sent yet</p>
          <p class="text-gray-400 text-sm mt-1">Send a preview to start the review process with your client.</p>
          <button v-if="canManage" @click="showPreviewModal = true"
            class="mt-4 px-5 py-2 text-sm font-semibold bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors">
            📤 Send First Preview
          </button>
        </div>

        <!-- Preview Cards -->
        <div v-for="preview in project.previews" :key="preview.id" class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">

          <!-- Preview Header -->
          <div class="flex items-start justify-between px-5 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-blue-100">
            <div>
              <div class="flex items-center gap-2 mb-1">
                <span class="bg-blue-600 text-white text-xs font-bold px-2.5 py-0.5 rounded-full font-mono">{{ preview.version }}</span>
                <span v-if="preview.review_deadline" class="text-xs text-amber-600 font-medium">
                  ⏰ Due {{ formatDate(preview.review_deadline) }}
                </span>
              </div>
              <h3 class="text-sm font-semibold text-gray-900">{{ preview.title }}</h3>
              <p class="text-xs text-gray-500 mt-0.5">Sent by <span class="font-medium">{{ preview.sent_by?.full_name }}</span> · {{ formatDatetime(preview.sent_at) }}</p>
            </div>
            <button v-if="canManage" @click="deletePreview(preview)" class="text-gray-400 hover:text-red-500 transition-colors p-1">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
              </svg>
            </button>
          </div>

          <!-- Preview Description -->
          <div class="px-5 py-3 border-b border-gray-100">
            <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ preview.description }}</p>
            <div v-if="canManage && preview.internal_notes" class="mt-2 bg-yellow-50 rounded-lg px-3 py-2 border border-yellow-200">
              <p class="text-xs text-yellow-700"><span class="font-semibold">🔒 Internal:</span> {{ preview.internal_notes }}</p>
            </div>
          </div>

          <!-- Attachments Section -->
          <div class="px-5 py-3 border-b border-gray-100">
            <div class="flex items-center justify-between mb-2">
              <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">
                Attachments ({{ preview.attachments?.length ?? 0 }})
              </p>
              <!-- Upload button (admin) -->
              <label v-if="canManage" class="cursor-pointer inline-flex items-center gap-1 text-xs font-medium text-blue-600 hover:text-blue-800 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Upload Files
                <input type="file" multiple class="hidden" @change="uploadAttachments($event, preview)" />
              </label>
            </div>
            <!-- Attachment List -->
            <div v-if="preview.attachments?.length" class="flex flex-wrap gap-2">
              <a
                v-for="att in preview.attachments"
                :key="att.id"
                :href="att.url"
                target="_blank"
                class="group relative inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 hover:bg-blue-50 border border-gray-200 hover:border-blue-300 rounded-lg text-xs text-gray-700 hover:text-blue-700 transition-all"
              >
                <span>{{ fileIcon(att.mime_type) }}</span>
                <span class="max-w-28 truncate">{{ att.file_name }}</span>
                <span class="text-gray-400 text-[10px]">{{ formatSize(att.file_size) }}</span>
                <!-- delete (admin) -->
                <button
                  v-if="canManage"
                  @click.prevent="deleteAttachment(att)"
                  class="ml-1 opacity-0 group-hover:opacity-100 text-red-400 hover:text-red-600 transition-all"
                >×</button>
              </a>
            </div>
            <p v-else class="text-xs text-gray-400 italic">No files attached yet.</p>
          </div>

          <!-- Feedback Thread -->
          <div class="px-5 py-4">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">
              Feedback ({{ preview.feedbacks?.length ?? 0 }})
            </p>

            <div class="space-y-3 mb-4">
              <div v-for="fb in preview.feedbacks" :key="fb.id" class="flex gap-3 group">
                <div class="w-8 h-8 rounded-full flex-shrink-0 flex items-center justify-center text-xs font-bold text-white"
                  :class="avatarBg(fb.submitted_by?.role)">
                  {{ initials(fb.submitted_by?.full_name) }}
                </div>
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-2 mb-0.5">
                    <span class="text-sm font-semibold text-gray-800">{{ fb.submitted_by?.full_name }}</span>
                    <span class="text-xs text-gray-400">{{ formatDatetime(fb.submitted_at) }}</span>
                  </div>
                  <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ fb.comment }}</p>
                </div>
                <button v-if="canDeleteFeedback(fb)" @click="deleteFeedback(fb)"
                  class="opacity-0 group-hover:opacity-100 text-gray-300 hover:text-red-500 transition-all p-1 flex-shrink-0">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button>
              </div>
              <p v-if="!preview.feedbacks?.length" class="text-sm text-gray-400 italic">No feedback yet. Be the first to comment!</p>
            </div>

            <!-- New Feedback Form -->
            <div v-if="canFeedback" class="border-t border-gray-100 pt-3">
              <div class="flex gap-3">
                <div class="w-8 h-8 rounded-full flex-shrink-0 flex items-center justify-center text-xs font-bold text-white"
                  :class="avatarBg(authUser?.role)">
                  {{ initials(authUser?.full_name) }}
                </div>
                <div class="flex-1">
                  <textarea
                    v-model="feedbackForms[preview.id]"
                    placeholder="Leave your feedback or revision notes..."
                    rows="2"
                    class="w-full rounded-xl border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 resize-none"
                  />
                  <div class="flex justify-end mt-2">
                    <button
                      @click="submitFeedback(preview)"
                      :disabled="!feedbackForms[preview.id]?.trim() || submittingFeedback[preview.id]"
                      class="px-4 py-1.5 text-xs font-semibold bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      {{ submittingFeedback[preview.id] ? 'Submitting...' : 'Submit Feedback' }}
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Sidebar -->
      <div class="space-y-4">

        <!-- Activity Log -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
          <h2 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-4">Activity Log</h2>
          <div class="flow-root">
            <ul class="-mb-4">
              <li v-for="(entry, i) in project.status_history" :key="entry.id" class="relative pb-4">
                <span v-if="i < project.status_history.length - 1" class="absolute top-4 left-3.5 -ml-px h-full w-0.5 bg-gray-200" />
                <div class="relative flex items-start gap-3">
                  <div class="w-7 h-7 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-3.5 h-3.5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                    </svg>
                  </div>
                  <div class="min-w-0 flex-1">
                    <div class="text-xs font-medium text-gray-800">
                      <span v-if="entry.from_status">
                        <span class="text-gray-500">{{ statusLabel(entry.from_status) }}</span> → <span class="text-blue-600">{{ statusLabel(entry.to_status) }}</span>
                      </span>
                      <span v-else class="text-green-600">Project Created</span>
                    </div>
                    <p v-if="entry.notes" class="text-xs text-gray-500 mt-0.5">{{ entry.notes }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ entry.changed_by?.full_name }} · {{ formatDatetime(entry.changed_at) }}</p>
                  </div>
                </div>
              </li>
            </ul>
          </div>
        </div>

        <!-- Description -->
        <div v-if="project.description" class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
          <h2 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-3">Description</h2>
          <p class="text-sm text-gray-700 whitespace-pre-wrap leading-relaxed">{{ project.description }}</p>
        </div>
      </div>
    </div>

  </AuthenticatedLayout>

  <!-- Preview Modal -->
  <PreviewModal
    :show="showPreviewModal"
    :project="project"
    @close="showPreviewModal = false"
    @saved="showPreviewModal = false"
  />
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PreviewModal from '@/Components/PreviewModal.vue'
import Swal from 'sweetalert2'

const props = defineProps({
  project:     Object,
  statuses:    Array,
  canManage:   Boolean,
  canFeedback: Boolean,
})

const page             = usePage()
const authUser         = computed(() => page.props.auth?.user)
const showPreviewModal = ref(false)

const feedbackForms      = reactive({})
const submittingFeedback = reactive({})

// ─── Status helpers ───────────────────────────────────────────────────────────

const statusOrder = computed(() => {
  const map = {}
  props.statuses.forEach((s, i) => { map[s] = i })
  return map
})

const isCompleted = (s) => statusOrder.value[s] < statusOrder.value[project.value.status]

const statusLabel = (s) => ({
  brief:                    'Brief',
  scheduled:                'Scheduled',
  work_in_progress:         'WIP',
  preview_sent:             'Preview Sent',
  feedback_received:        'Feedback',
  artwork_approved:         'Approved',
  final_artwork_preparation:'FA Prep',
  fa_sent:                  'FA Sent',
  project_closed:           'Closed',
}[s] ?? s)

// map each status → what the next quick-action button should be
const nextAction = computed(() => {
  if (!props.canManage) return null
  const map = {
    brief:                    { status: 'scheduled',                 label: 'Mark as Scheduled',         icon: '📅', btnClass: 'bg-blue-600 hover:bg-blue-700' },
    scheduled:                { status: 'work_in_progress',          label: 'Start Work In Progress',    icon: '🛠️', btnClass: 'bg-amber-500 hover:bg-amber-600' },
    work_in_progress:         null, // prompt to send preview instead
    preview_sent:             null, // waiting for client feedback
    feedback_received:        { status: 'artwork_approved',          label: 'Approve Artwork',            icon: '✅', btnClass: 'bg-green-600 hover:bg-green-700' },
    artwork_approved:         { status: 'final_artwork_preparation', label: 'Start FA Preparation',      icon: '🖨️', btnClass: 'bg-teal-600 hover:bg-teal-700' },
    final_artwork_preparation:{ status: 'fa_sent',                   label: 'Mark FA Sent',              icon: '📬', btnClass: 'bg-indigo-600 hover:bg-indigo-700' },
    fa_sent:                  { status: 'project_closed',            label: 'Close Project',             icon: '🏁', btnClass: 'bg-gray-700 hover:bg-gray-800' },
    project_closed:           null,
  }
  return map[project.value.status] ?? null
})

// project is a computed ref — reactive to Inertia prop updates
const project = computed(() => props.project)

// ─── Actions ─────────────────────────────────────────────────────────────────

const submitFeedback = (preview) => {
  const comment = feedbackForms[preview.id]?.trim()
  if (!comment) return
  submittingFeedback[preview.id] = true
  router.post(route('projects.feedbacks.store', project.value.id), { comment, preview_id: preview.id }, {
    preserveScroll: true,
    onSuccess: () => { feedbackForms[preview.id] = ''; submittingFeedback[preview.id] = false },
    onError:   () => { submittingFeedback[preview.id] = false },
  })
}

const deletePreview = (preview) => {
  Swal.fire({ title: `Delete ${preview.version}?`, icon: 'warning', showCancelButton: true,
    confirmButtonColor: '#dc2626', cancelButtonColor: '#6b7280', confirmButtonText: 'Delete', reverseButtons: true,
  }).then(r => r.isConfirmed && router.delete(route('projects.previews.destroy', [project.value.id, preview.id]), { preserveScroll: true }))
}

const deleteFeedback = (fb) => {
  Swal.fire({ title: 'Delete Feedback?', icon: 'warning', showCancelButton: true,
    confirmButtonColor: '#dc2626', cancelButtonColor: '#6b7280', confirmButtonText: 'Delete', reverseButtons: true,
  }).then(r => r.isConfirmed && router.delete(route('projects.feedbacks.destroy', [project.value.id, fb.id]), { preserveScroll: true }))
}

const updateStatus = (newStatus) => {
  Swal.fire({
    title: 'Update Status?',
    text: `Change to "${statusLabel(newStatus)}"?`,
    icon: 'question', showCancelButton: true,
    confirmButtonColor: '#2563eb', cancelButtonColor: '#6b7280',
    confirmButtonText: 'Yes, update', reverseButtons: true,
  }).then(result => {
    if (!result.isConfirmed) return
    const data = new FormData()
    const p = project.value
    data.append('_method', 'PUT')
    data.append('project_name', p.project_name)
    data.append('client_id',    p.client_id)
    data.append('status',       newStatus)
    data.append('priority',     p.priority)
    data.append('description',  p.description ?? '')
    data.append('deadline',     p.deadline    ?? '')
    data.append('report_date',  p.report_date ?? '')
    ;(p.pic_users ?? []).forEach(pu => data.append('pic_ids[]', pu.id))
    router.post(route('projects.update', p.id), data, { preserveScroll: true, forceFormData: true })
  })
}

// Attachment upload
const uploadAttachments = (event, preview) => {
  const files = event.target.files
  if (!files || !files.length) return
  const data = new FormData()
  for (const f of files) data.append('files[]', f)
  router.post(route('projects.previews.attachments.store', [project.value.id, preview.id]), data, {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => { event.target.value = '' },
  })
}

const deleteAttachment = (att) => {
  Swal.fire({ title: 'Delete file?', text: att.file_name, icon: 'warning', showCancelButton: true,
    confirmButtonColor: '#dc2626', cancelButtonColor: '#6b7280', confirmButtonText: 'Delete', reverseButtons: true,
  }).then(r => r.isConfirmed && router.delete(route('projects.attachments.destroy', [project.value.id, att.id]), { preserveScroll: true }))
}

const canDeleteFeedback = (fb) => props.canManage || fb.submitted_by?.id === authUser.value?.id

// ─── Helpers ─────────────────────────────────────────────────────────────────

const coverBg = (status) => ({
  brief:                    'bg-gradient-to-br from-gray-400 to-gray-600',
  scheduled:                'bg-gradient-to-br from-blue-400 to-blue-600',
  work_in_progress:         'bg-gradient-to-br from-amber-400 to-orange-500',
  preview_sent:             'bg-gradient-to-br from-purple-400 to-purple-600',
  feedback_received:        'bg-gradient-to-br from-orange-400 to-red-500',
  artwork_approved:         'bg-gradient-to-br from-green-400 to-emerald-600',
  final_artwork_preparation:'bg-gradient-to-br from-teal-400 to-cyan-600',
  fa_sent:                  'bg-gradient-to-br from-indigo-400 to-indigo-600',
  project_closed:           'bg-gradient-to-br from-gray-700 to-gray-900',
}[status] || 'bg-gradient-to-br from-gray-400 to-gray-600')

const priorityClass = (priority) => ({
  high:   'bg-red-500 text-white',
  normal: 'bg-blue-500 text-white',
  low:    'bg-gray-200 text-gray-700',
}[priority] || 'bg-gray-200 text-gray-700')

const avatarBg = (role) => ({
  super_admin: 'bg-purple-500',
  admin:       'bg-blue-500',
  client:      'bg-emerald-500',
  pic:         'bg-orange-500',
}[role] || 'bg-gray-400')

const initials = (name) => {
  if (!name) return '?'
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

const fileIcon = (mime) => {
  if (!mime) return '📎'
  if (mime.startsWith('image/')) return '🖼️'
  if (mime === 'application/pdf') return '📄'
  if (mime.includes('zip') || mime.includes('rar')) return '🗜️'
  if (mime.includes('word') || mime.includes('document')) return '📝'
  return '📎'
}

const formatSize = (bytes) => {
  if (!bytes) return ''
  if (bytes < 1024) return `${bytes}B`
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(0)}KB`
  return `${(bytes / 1024 / 1024).toFixed(1)}MB`
}

const formatDate = (d) => {
  if (!d) return ''
  return new Date(d).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
}

const formatDatetime = (d) => {
  if (!d) return ''
  return new Date(d).toLocaleString('en-GB', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' })
}
</script>
