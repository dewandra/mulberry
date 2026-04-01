<template>
  <!-- ── Hero Banner ── -->
  <div class="relative rounded-2xl overflow-hidden mb-6 hero-light">
    <div class="hero-light-blob blob-a" />
    <div class="hero-light-blob blob-b" />
    <div class="relative z-10 px-8 py-7">
      <p class="text-indigo-500 text-xs font-bold uppercase tracking-widest mb-1">ACT Digital Agency</p>
      <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 leading-tight">
        Welcome back,
        <span class="text-indigo-600">{{ firstName }}!</span>
      </h1>
      <p class="text-gray-500 mt-1.5 text-sm">Here's what's happening across all your projects today.</p>
    </div>
  </div>

  <!-- ── Stat Cards ── -->
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <!-- Active Projects -->
    <Link :href="route('projects.index')" class="admin-stat group">
      <div class="admin-stat-icon bg-indigo-50 text-indigo-600 group-hover:bg-indigo-100">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
      </div>
      <div class="flex-1 min-w-0">
        <p class="admin-stat-label">Active Projects</p>
        <div class="flex items-baseline gap-2">
          <p class="admin-stat-num text-indigo-600">{{ stats.active_projects }}</p>
          <p class="text-xs text-gray-400">of {{ stats.total_projects }} total</p>
        </div>
      </div>
    </Link>

    <!-- Awaiting Feedback -->
    <Link :href="route('projects.index', { status: 'preview_sent' })" class="admin-stat group">
      <div class="admin-stat-icon bg-amber-50 text-amber-600 group-hover:bg-amber-100">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
        </svg>
      </div>
      <div class="flex-1 min-w-0">
        <p class="admin-stat-label">Awaiting Feedback</p>
        <div class="flex items-baseline gap-2">
          <p class="admin-stat-num text-amber-600">{{ stats.awaiting_feedback }}</p>
          <p v-if="stats.feedback_received" class="text-xs text-gray-400">{{ stats.feedback_received }} replied</p>
        </div>
      </div>
    </Link>

    <!-- High Priority -->
    <Link :href="route('projects.index', { priority: 'high' })" class="admin-stat group">
      <div class="admin-stat-icon bg-red-50 text-red-500 group-hover:bg-red-100">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
        </svg>
      </div>
      <div class="flex-1 min-w-0">
        <p class="admin-stat-label">High Priority</p>
        <div class="flex items-baseline gap-2">
          <p class="admin-stat-num text-red-500">{{ stats.high_priority }}</p>
          <p class="text-xs text-gray-400">need attention</p>
        </div>
      </div>
    </Link>

    <!-- Closed / Companies -->
    <Link :href="isSuperAdmin ? route('clients.index') : route('projects.index', { status: 'project_closed' })" class="admin-stat group">
      <div class="admin-stat-icon bg-emerald-50 text-emerald-600 group-hover:bg-emerald-100">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </div>
      <div class="flex-1 min-w-0">
        <p class="admin-stat-label">Completed</p>
        <div class="flex items-baseline gap-2">
          <p class="admin-stat-num text-emerald-600">{{ stats.closed_projects ?? 0 }}</p>
          <p v-if="isSuperAdmin" class="text-xs text-gray-400">&middot; {{ stats.total_clients }} companies</p>
        </div>
      </div>
    </Link>
  </div>

  <!-- ── Bottom Row: Status Distribution + Recent Projects ── -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Status Distribution -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
      <h2 class="section-title mb-4">
        <span class="title-dot bg-purple-500"></span>
        Status Distribution
      </h2>

      <!-- Segmented Overview Bar -->
      <div class="flex h-3 rounded-full overflow-hidden bg-gray-100 mb-5" v-if="stats.total_projects > 0">
        <div
          v-for="s in statuses"
          :key="'bar-'+s"
          v-show="(byStatus[s] ?? 0) > 0"
          class="h-full transition-all duration-700 first:rounded-l-full last:rounded-r-full"
          :class="statusBarColor(s)"
          :style="{ width: barPercent(byStatus[s] ?? 0) }"
          :title="`${statusLabel(s)}: ${byStatus[s] ?? 0}`"
        />
      </div>

      <!-- Status Rows (only with count > 0) -->
      <div class="space-y-1">
        <Link
          v-for="s in statusesWithCount"
          :key="s"
          :href="route('projects.index', { status: s })"
          class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-gray-50 transition-colors group cursor-pointer"
        >
          <span class="w-2.5 h-2.5 rounded-full flex-shrink-0 ring-2 ring-offset-1" :class="statusDotRing(s)" />
          <span class="text-xs font-semibold text-gray-700 flex-1 group-hover:text-gray-900 transition-colors">{{ statusLabel(s) }}</span>
          <span class="text-sm font-bold text-gray-900 tabular-nums">{{ byStatus[s] }}</span>
          <span class="text-[10px] text-gray-400 w-7 text-right tabular-nums">{{ barPercent(byStatus[s]) }}</span>
        </Link>
      </div>

      <!-- Empty statuses collapsed -->
      <div v-if="emptyStatuses.length" class="mt-3 pt-3 border-t border-gray-100">
        <p class="text-[10px] font-semibold text-gray-300 uppercase tracking-widest mb-1.5 px-3">No projects</p>
        <div class="flex flex-wrap gap-1.5 px-3">
          <span
            v-for="s in emptyStatuses"
            :key="'empty-'+s"
            class="px-2 py-0.5 bg-gray-50 text-gray-400 text-[10px] font-medium rounded-full"
          >{{ statusLabel(s) }}</span>
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
            <p class="text-xs text-gray-400 mt-0.5">{{ p.client?.company_name }} &middot; {{ p.project_code }}</p>
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

const statusesWithCount = computed(() => (props.statuses ?? []).filter(s => (props.byStatus?.[s] ?? 0) > 0))
const emptyStatuses     = computed(() => (props.statuses ?? []).filter(s => (props.byStatus?.[s] ?? 0) === 0))

const barPercent = (count) => {
  if (!count) return ''
  return `${Math.round((count / (props.stats?.total_projects || 1)) * 100)}%`
}
</script>

<style scoped>
.admin-stat {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.25rem 1.5rem;
  background: white;
  border-radius: 1rem;
  border: 1px solid #f1f5f9;
  box-shadow: 0 1px 3px rgba(0,0,0,0.04);
  transition: all 0.2s ease;
  cursor: pointer;
  text-decoration: none;
}
.admin-stat:hover {
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  transform: translateY(-1px);
}
.admin-stat-icon {
  width: 2.75rem;
  height: 2.75rem;
  border-radius: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  transition: background-color 0.2s ease;
}
.admin-stat-label {
  font-size: 0.6875rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #94a3b8;
  margin-bottom: 0.125rem;
}
.admin-stat-num {
  font-size: 1.5rem;
  font-weight: 800;
  line-height: 1.2;
}
</style>
