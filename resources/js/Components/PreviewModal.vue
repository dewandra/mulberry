<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-50" @click="$emit('close')" />
        <div class="flex min-h-full items-center justify-center p-4">
          <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg flex flex-col" @click.stop>

            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
              <div>
                <h2 class="text-lg font-bold text-gray-900">Send New Preview</h2>
                <p class="text-xs text-gray-500 mt-0.5">Version will be auto-assigned ({{ nextVersion }})</p>
              </div>
              <button @click="$emit('close')" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>

            <!-- Body -->
            <div class="px-6 py-5 space-y-4">
              <!-- Title -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                <input
                  v-model="form.title"
                  type="text"
                  :placeholder="`Preview ${nextVersion}`"
                  class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                />
              </div>

              <!-- Description -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Description <span class="text-red-500">*</span>
                </label>
                <textarea
                  v-model="form.description"
                  rows="3"
                  placeholder="What was changed or included in this preview?"
                  class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                  :class="{ 'border-red-400': errors.description }"
                />
                <p v-if="errors.description" class="mt-1 text-xs text-red-600">{{ errors.description }}</p>
              </div>

              <!-- Internal Notes -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Internal Notes <span class="text-xs text-gray-400">(not visible to client)</span></label>
                <textarea
                  v-model="form.internal_notes"
                  rows="2"
                  placeholder="Team notes, revision count, etc."
                  class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                />
              </div>

              <!-- Review Deadline -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Review Deadline</label>
                <input
                  v-model="form.review_deadline"
                  type="date"
                  class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                />
              </div>

              <!-- Info banner -->
              <div class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 flex gap-2">
                <svg class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <p class="text-xs text-blue-700 leading-relaxed">
                  Sending this preview will automatically update the project status to <strong>Preview Sent</strong>.
                </p>
              </div>
            </div>

            <!-- Footer -->
            <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-200 bg-gray-50 rounded-b-2xl">
              <button type="button" @click="$emit('close')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Cancel
              </button>
              <button
                type="button"
                @click="submit"
                :disabled="processing"
                class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition-colors disabled:opacity-60"
              >
                <span v-if="processing" class="flex items-center gap-2">
                  <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                  </svg>
                  Sending...
                </span>
                <span v-else>📤 Send Preview</span>
              </button>
            </div>

          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  show:    { type: Boolean, default: false },
  project: { type: Object, required: true },
})

const emit = defineEmits(['close', 'saved'])

const processing = ref(false)
const errors     = ref({})

const form = reactive({
  title:           '',
  description:     '',
  internal_notes:  '',
  review_deadline: '',
})

// Compute next version label
const nextVersion = computed(() => {
  const previews = props.project?.previews ?? []
  if (!previews.length) return 'v1'
  const last = previews[0]?.version ?? 'v0'
  const num  = parseInt(last.replace('v', '')) + 1
  return `v${num}`
})

const submit = () => {
  processing.value = true
  errors.value = {}

  router.post(route('projects.previews.store', props.project.id), { ...form }, {
    preserveScroll: true,
    onSuccess: () => {
      processing.value = false
      form.title           = ''
      form.description     = ''
      form.internal_notes  = ''
      form.review_deadline = ''
      emit('saved')
    },
    onError: (errs) => {
      processing.value = false
      errors.value = errs
    },
  })
}
</script>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: opacity 0.2s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
</style>
