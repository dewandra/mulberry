<template>
  <!-- ── Hero Banner ── -->
  <div class="relative rounded-2xl overflow-hidden mb-6 hero-light">
    <div class="hero-light-blob blob-a" />
    <div class="hero-light-blob blob-b" />
    <div class="relative z-10 flex flex-col sm:flex-row items-center justify-between px-8 py-8 gap-6">
      <div>
        <p class="text-indigo-500 text-xs font-bold uppercase tracking-widest mb-1">ACT Digital Agency</p>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 leading-tight">
          Welcome back,
          <span class="text-indigo-600">{{ firstName }}!</span>
        </h1>
        <p class="text-gray-500 mt-2 text-sm">Here's what's happening across all your projects today.</p>
      </div>
      <div class="flex flex-wrap gap-3">
        <div class="hero-pill">
          <span class="hero-pill-num">{{ stats.total_projects }}</span>
          <span class="hero-pill-label">Total Projects</span>
        </div>
        <div class="hero-pill">
          <span class="hero-pill-num text-indigo-600">{{ stats.active_projects }}</span>
          <span class="hero-pill-label">Active</span>
        </div>
        <div class="hero-pill">
          <span class="hero-pill-num text-emerald-600">{{ stats.total_clients }}</span>
          <span class="hero-pill-label">Companies</span>
        </div>
      </div>
    </div>
  </div>

  <!-- ── Stat Cards ── -->
  <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-4 mb-6">
    <Link :href="route('projects.index')" class="stat-card-light col-span-1">
      <div class="stat-light-icon">📁</div>
      <p class="stat-light-label">Total Projects</p>
      <p class="stat-light-num">{{ stats.total_projects }}</p>
      <p class="stat-light-sub">{{ stats.active_projects }} active</p>
    </Link>
    <Link :href="route('projects.index', { status: 'preview_sent' })" class="stat-card-light col-span-1 border-l-4 border-purple-400">
      <div class="stat-light-icon">📬</div>
      <p class="stat-light-label">Awaiting Feedback</p>
      <p class="stat-light-num text-purple-600">{{ stats.awaiting_feedback }}</p>
      <p class="stat-light-sub">{{ stats.feedback_received }} in review</p>
    </Link>
    <Link :href="route('projects.index', { priority: 'high' })" class="stat-card-light col-span-1 border-l-4 border-red-400">
      <div class="stat-light-icon">🔥</div>
      <p class="stat-light-label">High Priority</p>
      <p class="stat-light-num text-red-500">{{ stats.high_priority }}</p>
      <p class="stat-light-sub">projects</p>
    </Link>
    <Link v-if="isSuperAdmin" :href="route('clients.index')" class="stat-card-light col-span-1 border-l-4 border-emerald-400">
      <div class="stat-light-icon">🏢</div>
      <p class="stat-light-label">Companies</p>
      <p class="stat-light-num text-emerald-600">{{ stats.total_clients }}</p>
      <p class="stat-light-sub">{{ stats.total_users }} users total</p>
    </Link>
    <Link v-if="isSuperAdmin" :href="route('users.index')" class="stat-card-light col-span-1 border-l-4 border-indigo-400">
      <div class="stat-light-icon">👥</div>
      <p class="stat-light-label">Users</p>
      <p class="stat-light-num text-indigo-600">{{ stats.total_users }}</p>
      <p class="stat-light-sub">registered</p>
    </Link>
    <Link :href="route('projects.index', { status: 'project_closed' })" class="stat-card-light col-span-1 border-l-4 border-gray-400">
      <div class="stat-light-icon">✅</div>
      <p class="stat-light-label">Closed</p>
      <p class="stat-light-num text-gray-600">{{ stats.closed_projects ?? 0 }}</p>
      <p class="stat-light-sub">completed</p>
    </Link>
  </div>

  <!-- ── Bottom Row: Status Distribution + Recent Projects ── -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Status Distribution -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
      <h2 class="section-title mb-5">
        <span class="title-dot bg-purple-500"></span>
        Status Distribution
      </h2>
      <div class="space-y-3">
        <div v-for="s in statuses" :key="s">
          <div class="flex items-center justify-between mb-1">
            <div class="flex items-center gap-2">
              <span class="w-2 h-2 rounded-full flex-shrink-0" :class="statusDotRing(s).split(' ')[0]"></span>
              <span class="text-xs font-semibold text-gray-700">{{ statusLabel(s) }}</span>
            </div>
            <div class="flex items-center gap-2">
              <span class="text-xs font-bold text-gray-900">{{ byStatus[s] ?? 0 }}</span>
              <span class="text-xs text-gray-400 w-8 text-right">{{ barPercent(byStatus[s] ?? 0) }}</span>
            </div>
          </div>
          <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
            <div
              class="h-2 rounded-full transition-all duration-700 relative overflow-hidden"
              :class="statusBarColor(s)"
              :style="{ width: barWidth(byStatus[s] ?? 0) }"
            >
              <div v-if="(byStatus[s] ?? 0) > 0" class="absolute inset-0 shimmer-bar"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Projects -->
    <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <h2 class="section-title">
          <span class="title-dot bg-indigo-500"></span>
          Recent Projects
        </h2>
        <Link :href="route('projects.index')" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold transition-colors flex items-center gap-1">
          View all <span>→</span>
        </Link>
      </div>
      <div class="divide-y divide-gray-50">
        <Link
          v-for="p in recentProjects"
          :key="p.id"
          :href="route('projects.show', p.id)"
          class="flex items-center gap-4 px-6 py-3.5 hover:bg-indigo-50/50 transition-colors group"
        >
          <div class="w-2.5 h-2.5 rounded-full flex-shrink-0 ring-2 ring-offset-1" :class="statusDotRing(p.status)" />
          <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-gray-900 truncate group-hover:text-indigo-700 transition-colors">{{ p.project_name }}</p>
            <p class="text-xs text-gray-400 mt-0.5">{{ p.client?.company_name }} · {{ p.project_code }}</p>
          </div>
          <div class="flex flex-col items-end gap-1">
            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold" :class="statusClass(p.status)">
              {{ statusLabel(p.status) }}
            </span>
            <span class="px-2 py-0.5 rounded-full text-xs font-bold" :class="priorityClassSoft(p.priority)">
              {{ p.priority_display }}
            </span>
          </div>
        </Link>
        <div v-if="!recentProjects?.length" class="px-6 py-12 text-center text-gray-400 text-sm">
          <span class="text-3xl block mb-2">📭</span>
          No projects yet.
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { statusLabel, statusBarColor, statusDotRing, statusClass, priorityClassSoft } from '@/composables/useProject'

const props = defineProps({
  stats:          Object,
  byStatus:       Object,
  statuses:       Array,
  recentProjects: Array,
  isSuperAdmin:   Boolean,
})

const $page    = usePage()
const firstName = computed(() => $page.props.auth.user.full_name.split(' ')[0])

const maxCount  = computed(() => Math.max(1, ...Object.values(props.byStatus ?? {})))
const barWidth  = (count) => `${Math.round((count / maxCount.value) * 100)}%`
const barPercent = (count) => {
  if (!count) return ''
  return `${Math.round((count / (props.stats?.total_projects || 1)) * 100)}%`
}
</script>
