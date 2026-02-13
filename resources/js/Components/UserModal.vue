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
                  <input
                    id="password"
                    type="password"
                    v-model="form.password"
                    :required="!isEditing"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    :class="{ 'border-red-500': form.errors.password }"
                  />
                  <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">
                    {{ form.errors.password }}
                  </p>
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

                <!-- Client (conditional) -->
                <div v-if="showClientField">
                  <label for="client_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Client <span class="text-red-500">*</span>
                  </label>
                  <select
                    id="client_id"
                    v-model="form.client_id"
                    required
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    :class="{ 'border-red-500': form.errors.client_id }"
                  >
                    <option value="">Select Client</option>
                    <option v-for="client in clients" :key="client.id" :value="client.id">
                      {{ client.company_name }}
                    </option>
                  </select>
                  <p v-if="form.errors.client_id" class="mt-1 text-sm text-red-600">
                    {{ form.errors.client_id }}
                  </p>
                </div>

                <!-- Is Active -->
                <div class="flex items-center">
                  <input
                    id="is_active"
                    type="checkbox"
                    v-model="form.is_active"
                    :disabled="isEditingSelf"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded disabled:bg-gray-100 disabled:cursor-not-allowed"
                  />
                  <label for="is_active" class="ml-2 block text-sm text-gray-700">
                    Active
                  </label>
                  <p v-if="isEditingSelf" class="ml-2 text-sm text-amber-600">
                    (You cannot deactivate yourself)
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
import { computed, watch } from 'vue'
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

const form = useForm({
  full_name: '',
  email: '',
  password: '',
  role: '',
  client_id: '',
  is_active: true,
})

const showClientField = computed(() => {
  return ['client', 'pic'].includes(form.role)
})

// Watch for user changes to populate form
watch(() => props.user, (newUser) => {
  if (newUser) {
    form.full_name = newUser.full_name
    form.email = newUser.email
    form.password = ''
    form.role = newUser.role
    form.client_id = newUser.client_id || ''
    form.is_active = newUser.is_active
  } else {
    form.reset()
  }
}, { immediate: true })

const submit = () => {
  const url = isEditing.value 
    ? route('users.update', props.user.id)
    : route('users.store')

  const method = isEditing.value ? 'put' : 'post'

  form[method](url, {
    preserveScroll: true,
    onSuccess: () => {
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
