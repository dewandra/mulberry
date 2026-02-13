<template>
  <AuthenticatedLayout title="Dashboard" subtitle="Overview of your workspace">
    <!-- Stats Grid (for admins) -->
    <div v-if="stats" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <StatCard
        title="Total Users"
        :value="stats.total_users"
        icon="users"
        color="blue"
      />
      <StatCard
        title="Active Users"
        :value="stats.active_users"
        icon="users-active"
        color="green"
      />
      <StatCard
        title="Total Clients"
        :value="stats.total_clients"
        icon="clients"
        color="purple"
      />
      <StatCard
        title="Active Clients"
        :value="stats.active_clients"
        icon="clients-active"
        color="orange"
      />
    </div>

    <!-- Welcome Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
      <div class="max-w-3xl">
        <h3 class="text-2xl font-bold text-gray-900 mb-3" style="font-family: 'Sora', sans-serif;">
          Welcome, {{ user.full_name }}! 👋
        </h3>
        <p class="text-gray-600 mb-6">
          You're logged in as <span class="font-semibold">{{ user.role_display }}</span>
          <span v-if="user.client"> for <span class="font-semibold">{{ user.client.company_name }}</span></span>.
        </p>
        
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
          <div class="flex items-start space-x-3">
            <svg class="w-6 h-6 text-blue-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
            </svg>
            <div>
              <h4 class="font-semibold text-blue-900 mb-1">Getting Started</h4>
              <p class="text-blue-800 text-sm">
                Your ACT Project Management system is ready. 
                <template v-if="canManageUsers">
                  Start by <Link href="/clients" class="underline font-medium">creating clients</Link> 
                  and <Link href="/users" class="underline font-medium">adding users</Link> to your workspace.
                </template>
                <template v-else>
                  Your projects will appear here once they're assigned to you.
                </template>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import StatCard from '@/Components/StatCard.vue'

const props = defineProps({
  user: Object,
  stats: Object,
})

const canManageUsers = computed(() => {
  return props.user.role === 'super_admin'
})
</script>