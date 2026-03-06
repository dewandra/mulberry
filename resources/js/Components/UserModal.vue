<template>
  <TransitionRoot :show="show" as="template">
    <Dialog as="div" class="relative z-50" @close="$emit('close')">
      <!-- Backdrop -->
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" />
      </TransitionChild>

      <!-- Modal Content -->
      <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
          <TransitionChild
            as="template"
            enter="ease-out duration-300"
            enter-from="opacity-0 translate-y-4 scale-95"
            enter-to="opacity-100 translate-y-0 scale-100"
            leave="ease-in duration-200"
            leave-from="opacity-100 translate-y-0 scale-100"
            leave-to="opacity-0 translate-y-4 scale-95"
          >
            <DialogPanel class="relative transform overflow-hidden rounded-2xl bg-white shadow-xl transition-all w-full max-w-lg">
              <!-- Header -->
              <div class="bg-white px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                  <DialogTitle class="text-xl font-semibold text-gray-900" style="font-family: 'Sora', sans-serif;">
                    {{ isEditing ? 'Edit User' : 'Create New User' }}
                  </DialogTitle>
                  <button
                    @click="$emit('close')"
                    class="text-gray-400 hover:text-gray-500 transition-colors"
                  >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              </div>

              <!-- Form -->
              <form @submit.prevent="submit" class="px-6 py-4 space-y-4">
                <!-- Full Name -->
                <div>
                  <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">
                    Full Name <span class="text-red-500">*</span>
                  </label>
                  <input
                    id="full_name"
                    type="text"
                    v-model="form.full_name"
                    required
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    :class="{ 'border-red-500': form.errors.full_name }"
                  />
                  <p v-if="form.errors.full_name" class="mt-1 text-sm text-red-600">
                    {{ form.errors.full_name }}
                  </p>
                </div>

                <!-- Email -->
                <div>
                  <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                    Email <span class="text-red-500">*</span>
                  </label>
                  <input
                    id="email"
                    type="email"
                    v-model="form.email"
                    required
                    :disabled="isEditingSelf"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 disabled:bg-gray-100 disabled:cursor-not-allowed"
                    :class="{ 'border-red-500': form.errors.email }"
                  />
                  <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">
                    {{ form.errors.email }}
                  </p>
                </div>

                <!-- Password -->
                <div>
                  <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                    Password <span v-if="!isEditing" class="text-red-500">*</span>
                    <span v-else class="text-gray-500 text-xs">(leave blank to keep current)</span>
                  </label>
                  <div class="flex gap-2">
                    <!-- Input + show/hide toggle -->
                    <div class="relative flex-1">
                      <input
                        id="password"
                        :type="showPassword ? 'text' : 'password'"
                        v-model="form.password"
                        :required="!isEditing"
                        placeholder="Min. 8 characters"
                        class="w-full pr-10 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 font-mono"
                        :class="{ 'border-red-500': form.errors.password }"
                      />
                      <!-- Eye toggle -->
                      <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-2 flex items-center text-gray-400 hover:text-gray-600 transition-colors"
                        :title="showPassword ? 'Hide password' : 'Show password'"
                      >
                        <!-- Eye open -->
                        <svg v-if="showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <!-- Eye closed -->
                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                        </svg>
                      </button>
                    </div>

                    <!-- Generate button -->
                    <button
                      type="button"
                      @click="generatePassword"
                      class="inline-flex items-center gap-1.5 px-3 py-2 bg-violet-600 hover:bg-violet-700 active:bg-violet-800 text-white text-sm font-medium rounded-lg transition-colors whitespace-nowrap shadow-sm"
                      title="Generate a secure random password"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                      </svg>
                      Generate
                    </button>

                    <!-- Copy button -->
                    <button
                      type="button"
                      @click="copyPassword"
                      :disabled="!form.password"
                      class="inline-flex items-center gap-1.5 px-3 py-2 border border-gray-300 hover:bg-gray-50 active:bg-gray-100 text-gray-700 text-sm font-medium rounded-lg transition-colors whitespace-nowrap disabled:opacity-40 disabled:cursor-not-allowed"
                      :title="copied ? 'Copied!' : 'Copy password'"
                    >
                      <!-- Check icon when copied -->
                      <svg v-if="copied" class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                      </svg>
                      <!-- Clipboard icon normally -->
                      <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                      </svg>
                      <span :class="{ 'text-green-600': copied }">{{ copied ? 'Copied!' : 'Copy' }}</span>
                    </button>
                  </div>

                  <!-- Hint row -->
                  <div class="mt-1 flex items-center justify-between">
                    <p v-if="form.errors.password" class="text-sm text-red-600">{{ form.errors.password }}</p>
                    <p v-else class="text-xs text-gray-400">{{ form.password.length }} / 8 min characters</p>
                  </div>
                </div>

                <!-- Role -->
                <div>
                  <label for="role" class="block text-sm font-medium text-gray-700 mb-1">
                    Role <span class="text-red-500">*</span>
                  </label>
                  <select
                    id="role"
                    v-model="form.role"
                    required
                    :disabled="isEditingSelf"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 disabled:bg-gray-100 disabled:cursor-not-allowed"
                    :class="{ 'border-red-500': form.errors.role }"
                  >
                    <option value="">Select Role</option>
                    <option value="super_admin">Super Admin</option>
                    <option value="admin">Admin ACT</option>
                    <option value="client">Client</option>
                    <option value="pic">PIC</option>
                  </select>
                  <p v-if="form.errors.role" class="mt-1 text-sm text-red-600">
                    {{ form.errors.role }}
                  </p>
                  <p v-if="isEditingSelf" class="mt-1 text-sm text-amber-600">
                    ⚠️ You cannot change your own role
                  </p>
                </div>

                <!-- Company (conditional) -->
                <div v-if="showClientField">
                  <label for="client_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Company <span class="text-red-500">*</span>
                  </label>
                  <select
                    id="client_id"
                    v-model="form.client_id"
                    required
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    :class="{ 'border-red-500': form.errors.client_id }"
                  >
                    <option value="">Select Company</option>
                    <option v-for="client in clients" :key="client.id" :value="client.id">
                      {{ client.company_name }}
                    </option>
                  </select>
                  <p v-if="form.errors.client_id" class="mt-1 text-sm text-red-600">
                    {{ form.errors.client_id }}
                  </p>
                </div>


                <!-- Footer Buttons -->
                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200 mt-6">
                  <button
                    type="button"
                    @click="$emit('close')"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                  >
                    Cancel
                  </button>
                  <button
                    type="submit"
                    :disabled="form.processing"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    <span v-if="form.processing">Saving...</span>
                    <span v-else">{{ isEditing ? 'Update' : 'Create' }} User</span>
                  </button>
                </div>
              </form>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'
import Swal from 'sweetalert2'

const props = defineProps({
  show: Boolean,
  user: Object,
  clients: Array,
})

const emit = defineEmits(['close', 'saved'])

const page = usePage()
const isEditing = computed(() => !!props.user)
const isEditingSelf = computed(() => props.user?.id === page.props.auth.user.id)

const showPassword = ref(false)
const copied = ref(false)

const form = useForm({
  full_name: '',
  email: '',
  password: '',
  role: '',
  client_id: '',
})

const showClientField = computed(() => {
  return ['client', 'pic'].includes(form.role)
})

// Generate a secure random password (12 chars: upper, lower, digit, symbol)
const generatePassword = () => {
  const upper  = 'ABCDEFGHJKLMNPQRSTUVWXYZ'
  const lower  = 'abcdefghjkmnpqrstuvwxyz'
  const digits = '23456789'
  const syms   = '@#$%&*!'
  const all    = upper + lower + digits + syms

  // Guarantee at least one of each character class
  let pwd = [
    upper [Math.floor(Math.random() * upper.length)],
    lower [Math.floor(Math.random() * lower.length)],
    digits[Math.floor(Math.random() * digits.length)],
    syms  [Math.floor(Math.random() * syms.length)],
  ]

  // Fill the remaining 8 characters from the full pool
  for (let i = 0; i < 8; i++) {
    pwd.push(all[Math.floor(Math.random() * all.length)])
  }

  // Shuffle
  pwd = pwd.sort(() => Math.random() - 0.5)
  form.password = pwd.join('')
  showPassword.value = true
  copied.value = false
}

// Copy password to clipboard with visual feedback
const copyPassword = async () => {
  if (!form.password) return
  try {
    await navigator.clipboard.writeText(form.password)
    copied.value = true
    setTimeout(() => { copied.value = false }, 2000)
  } catch {
    // Fallback for environments without clipboard API
    const el = document.getElementById('password')
    el?.select()
    document.execCommand('copy')
    copied.value = true
    setTimeout(() => { copied.value = false }, 2000)
  }
}

// Watch for user changes to populate form (edit mode)
watch(() => props.user, (newUser) => {
  if (newUser) {
    form.full_name = newUser.full_name
    form.email = newUser.email
    form.password = ''
    form.role = newUser.role
    form.client_id = newUser.client_id || ''
  } else {
    form.reset()
  }
}, { immediate: true })

// Reset form + UI state every time modal opens
watch(() => props.show, (visible) => {
  showPassword.value = false
  copied.value = false
  if (visible && !props.user) {
    form.reset()
  }
})

const submit = () => {
  const url = isEditing.value 
    ? route('users.update', props.user.id)
    : route('users.store')

  const method = isEditing.value ? 'put' : 'post'

  form[method](url, {
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
      Swal.fire({
        title: 'Success!',
        text: `User has been ${isEditing.value ? 'updated' : 'created'} successfully.`,
        icon: 'success',
        timer: 2000,
        showConfirmButton: false
      })
      emit('saved')
    },
    onError: () => {
      Swal.fire({
        title: 'Error!',
        text: 'Failed to save user. Please check the form and try again.',
        icon: 'error',
        confirmButtonColor: '#3b82f6'
      })
    }
  })
}
</script>
