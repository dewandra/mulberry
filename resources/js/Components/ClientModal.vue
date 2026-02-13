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
                    {{ isEditing ? 'Edit Client' : 'Create New Client' }}
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
                <!-- Company Name -->
                <div>
                  <label for="company_name" class="block text-sm font-medium text-gray-700 mb-1">
                    Company Name <span class="text-red-500">*</span>
                  </label>
                  <input
                    id="company_name"
                    type="text"
                    v-model="form.company_name"
                    required
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    :class="{ 'border-red-500': form.errors.company_name }"
                  />
                  <p v-if="form.errors.company_name" class="mt-1 text-sm text-red-600">
                    {{ form.errors.company_name }}
                  </p>
                </div>

                <!-- Logo Upload -->
                <div>
                  <label for="logo" class="block text-sm font-medium text-gray-700 mb-1">
                    Logo
                  </label>
                  <div class="flex items-center gap-4">
                    <!-- Preview -->
                    <div v-if="logoPreview || client?.logo" class="flex-shrink-0">
                      <img 
                        :src="logoPreview || client?.logo" 
                        alt="Logo preview" 
                        class="w-16 h-16 object-contain rounded border border-gray-200"
                      />
                    </div>
                    <!-- Upload Input -->
                    <div class="flex-1">
                      <input
                        id="logo"
                        type="file"
                        @change="handleLogoChange"
                        accept="image/jpeg,image/png,image/jpg,image/svg+xml"
                        class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                      />
                      <p class="mt-1 text-xs text-gray-500">PNG, JPG, SVG up to 2MB</p>
                    </div>
                  </div>
                  <p v-if="form.errors.logo" class="mt-1 text-sm text-red-600">
                    {{ form.errors.logo }}
                  </p>
                </div>

                <!-- Contact Person -->
                <div>
                  <label for="contact_person" class="block text-sm font-medium text-gray-700 mb-1">
                    Contact Person <span class="text-red-500">*</span>
                  </label>
                  <input
                    id="contact_person"
                    type="text"
                    v-model="form.contact_person"
                    required
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    :class="{ 'border-red-500': form.errors.contact_person }"
                  />
                  <p v-if="form.errors.contact_person" class="mt-1 text-sm text-red-600">
                    {{ form.errors.contact_person }}
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
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    :class="{ 'border-red-500': form.errors.email }"
                  />
                  <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">
                    {{ form.errors.email }}
                  </p>
                </div>

                <!-- Phone -->
                <div>
                  <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                    Phone <span class="text-red-500">*</span>
                  </label>
                  <input
                    id="phone"
                    type="tel"
                    v-model="form.phone"
                    required
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    :class="{ 'border-red-500': form.errors.phone }"
                  />
                  <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600">
                    {{ form.errors.phone }}
                  </p>
                </div>

                <!-- Address -->
                <div>
                  <label for="address" class="block text-sm font-medium text-gray-700 mb-1">
                    Address
                  </label>
                  <textarea
                    id="address"
                    v-model="form.address"
                    rows="3"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    :class="{ 'border-red-500': form.errors.address }"
                  ></textarea>
                  <p v-if="form.errors.address" class="mt-1 text-sm text-red-600">
                    {{ form.errors.address }}
                  </p>
                </div>

                <!-- Is Active -->
                <div class="flex items-center">
                  <input
                    id="is_active"
                    type="checkbox"
                    v-model="form.is_active"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                  <label for="is_active" class="ml-2 block text-sm text-gray-700">
                    Active
                  </label>
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
                    <span v-else>{{ isEditing ? 'Update' : 'Create' }} Client</span>
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
import { ref, computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'
import Swal from 'sweetalert2'

const props = defineProps({
  show: Boolean,
  client: Object,
})

const emit = defineEmits(['close', 'saved'])

const isEditing = computed(() => !!props.client)
const logoPreview = ref(null)

const form = useForm({
  company_name: '',
  logo: null,
  contact_person: '',
  email: '',
  phone: '',
  address: '',
  is_active: true,
})

const handleLogoChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    form.logo = file
    // Create preview
    const reader = new FileReader()
    reader.onload = (e) => {
      logoPreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

// Watch for client changes to populate form
watch(() => props.client, (newClient) => {
  logoPreview.value = null
  if (newClient) {
    form.company_name = newClient.company_name
    form.logo = null
    form.contact_person = newClient.contact_person
    form.email = newClient.email
    form.phone = newClient.phone
    form.address = newClient.address || ''
    form.is_active = newClient.is_active
  } else {
    form.reset()
  }
}, { immediate: true })

const submit = () => {
  const url = isEditing.value 
    ? route('clients.update', props.client.id)
    : route('clients.store')

  const method = isEditing.value ? 'post' : 'post'

  form.transform((data) => {
    const formData = new FormData()
    
    // Add all form fields
    formData.append('company_name', data.company_name || '')
    formData.append('contact_person', data.contact_person || '')
    formData.append('email', data.email || '')
    formData.append('phone', data.phone || '')
    formData.append('address', data.address || '')
    formData.append('is_active', data.is_active ? '1' : '0')
    
    // Add logo if exists
    if (data.logo) {
      formData.append('logo', data.logo)
    }
    
    // Add method spoofing for PUT request when editing
    if (isEditing.value) {
      formData.append('_method', 'PUT')
    }
    
    return formData
  })[method](url, {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => {
      Swal.fire({
        title: 'Success!',
        text: `Client has been ${isEditing.value ? 'updated' : 'created'} successfully.`,
        icon: 'success',
        timer: 2000,
        showConfirmButton: false
      })
      emit('saved')
    },
    onError: () => {
      Swal.fire({
        title: 'Error!',
        text: 'Failed to save client. Please check the form and try again.',
        icon: 'error',
        confirmButtonColor: '#3b82f6'
      })
    }
  })
}
</script>
