<template>
  <AuthenticatedLayout title="Dashboard" :subtitle="roleSubtitle">

    <!-- ═══════════════════════════════════════════════════════════
         ADMIN / SUPER ADMIN DASHBOARD
    ════════════════════════════════════════════════════════════════ -->
    <template v-if="role === 'admin' || role === 'super_admin'">

      <!-- Hero Banner -->
      <div class="relative rounded-3xl overflow-hidden mb-8 hero-banner">
        <!-- Background blobs -->
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>

        <div class="relative z-10 flex flex-col sm:flex-row items-center justify-between px-8 py-10 gap-6">
          <div>
            <p class="text-white/60 text-xs font-semibold uppercase tracking-widest mb-1">ACT Digital Agency</p>
            <h1 class="text-3xl sm:text-4xl font-extrabold text-white leading-tight">
              Welcome back,<br>
              <span class="text-orange-300">{{ $page.props.auth.user.full_name.split(' ')[0] }}!</span>
            </h1>
            <p class="text-white/70 mt-3 text-sm max-w-sm">
              Here's what's happening across all your projects today.
            </p>
          </div>

          <!-- Hero quick stats -->
          <div class="flex flex-wrap gap-4">
            <div class="hero-stat">
              <span class="hero-stat-num">{{ stats.total_projects }}</span>
              <span class="hero-stat-label">Total Projects</span>
            </div>
            <div class="hero-stat">
              <span class="hero-stat-num text-orange-300">{{ stats.active_projects }}</span>
              <span class="hero-stat-label">Active</span>
            </div>
            <div class="hero-stat">
              <span class="hero-stat-num text-green-300">{{ stats.total_clients }}</span>
              <span class="hero-stat-label">Companies</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Stat Cards -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="stat-card gradient-purple">
          <div class="stat-icon">📁</div>
          <p class="stat-label">Total Projects</p>
          <p class="stat-number">{{ stats.total_projects }}</p>
          <p class="stat-sub">{{ stats.active_projects }} active</p>
        </div>
        <div class="stat-card gradient-orange">
          <div class="stat-icon">📬</div>
          <p class="stat-label">Awaiting Feedback</p>
          <p class="stat-number">{{ stats.awaiting_feedback }}</p>
          <p class="stat-sub">{{ stats.feedback_received }} in review</p>
        </div>
        <div class="stat-card gradient-red">
          <div class="stat-icon">🔥</div>
          <p class="stat-label">High Priority</p>
          <p class="stat-number">{{ stats.high_priority }}</p>
          <p class="stat-sub">projects</p>
        </div>
        <div class="stat-card gradient-teal">
          <div class="stat-icon">🏢</div>
          <p class="stat-label">Companies</p>
          <p class="stat-number">{{ stats.total_clients }}</p>
          <p class="stat-sub">{{ stats.total_users }} users total</p>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

        <!-- Status Distribution -->
        <div class="glass-card p-6">
          <h2 class="section-title mb-5">
            <span class="title-dot bg-purple-500"></span>
            Status Distribution
          </h2>
          <div class="space-y-4">
            <div v-for="s in statuses" :key="s" class="group">
              <!-- Label row -->
              <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                  <span class="w-2 h-2 rounded-full flex-shrink-0" :class="statusDotRing(s).split(' ')[0]"></span>
                  <span class="text-xs font-semibold text-gray-700">{{ statusLabel(s) }}</span>
                </div>
                <div class="flex items-center gap-2">
                  <span class="text-xs font-bold text-gray-900">{{ byStatus[s] ?? 0 }}</span>
                  <span class="text-xs text-gray-400 w-8 text-right">{{ barPercent(byStatus[s] ?? 0) }}</span>
                </div>
              </div>
              <!-- Bar -->
              <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                <div
                  class="h-2.5 rounded-full transition-all duration-700 relative overflow-hidden"
                  :class="statusBarColor(s)"
                  :style="{ width: barWidth(byStatus[s] ?? 0) }"
                >
                  <!-- shimmer -->
                  <div v-if="(byStatus[s] ?? 0) > 0" class="absolute inset-0 shimmer-bar"></div>
                </div>
              </div>
            </div>
          </div>
        </div>


        <!-- Recent Projects -->
        <div class="lg:col-span-2 glass-card overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="section-title">
              <span class="title-dot bg-indigo-500"></span>
              Recent Projects
            </h2>
            <Link :href="route('projects.index')" class="text-xs text-violet-600 hover:text-violet-800 font-semibold transition-colors flex items-center gap-1">
              View all <span>→</span>
            </Link>
          </div>
          <div class="divide-y divide-gray-50">
            <Link
              v-for="p in recentProjects"
              :key="p.id"
              :href="route('projects.show', p.id)"
              class="flex items-center gap-4 px-6 py-3.5 hover:bg-violet-50/60 transition-colors group"
            >
              <div class="w-2.5 h-2.5 rounded-full flex-shrink-0 ring-2 ring-offset-1" :class="statusDotRing(p.status)" />
              <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-900 truncate group-hover:text-violet-700 transition-colors">{{ p.project_name }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ p.client?.company_name }} · {{ p.project_code }}</p>
              </div>
              <div class="flex flex-col items-end gap-1">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold" :class="statusClass(p.status)">
                  {{ statusLabel(p.status) }}
                </span>
                <span class="px-2 py-0.5 rounded-full text-xs font-bold" :class="priorityClass(p.priority)">
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

      <!-- Quick Links -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <Link :href="route('projects.index')" class="quick-link quick-link-blue">
          <span class="quick-link-icon">📁</span>
          <p class="quick-link-label">All Projects</p>
        </Link>
        <template v-if="isSuperAdmin">
          <Link :href="route('clients.index')" class="quick-link quick-link-green">
            <span class="quick-link-icon">🏢</span>
            <p class="quick-link-label">Companies</p>
          </Link>
          <Link :href="route('users.index')" class="quick-link quick-link-purple">
            <span class="quick-link-icon">👥</span>
            <p class="quick-link-label">Users</p>
          </Link>
        </template>
        <Link :href="route('projects.index', { status: 'preview_sent' })" class="quick-link quick-link-orange">
          <span class="quick-link-icon">📬</span>
          <p class="quick-link-label">Awaiting Feedback</p>
          <p class="text-white/80 text-2xl font-extrabold mt-1">{{ stats.awaiting_feedback }}</p>
        </Link>
      </div>
    </template>

    <!-- ═══════════════════════════════════════════════════════════
         CLIENT DASHBOARD
    ════════════════════════════════════════════════════════════════ -->
    <template v-else-if="role === 'client'">

      <!-- Hero -->
      <div class="relative rounded-3xl overflow-hidden mb-8 hero-banner">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="relative z-10 px-8 py-10">
          <p class="text-white/60 text-xs font-semibold uppercase tracking-widest mb-1">Client Portal</p>
          <h1 class="text-3xl font-extrabold text-white">
            Hi, <span class="text-orange-300">{{ $page.props.auth.user.full_name.split(' ')[0] }}</span> 👋
          </h1>
          <p class="text-white/70 mt-2 text-sm">Track your project progress at a glance.</p>
        </div>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-8">
        <div class="stat-card gradient-purple">
          <div class="stat-icon">📁</div>
          <p class="stat-label">My Projects</p>
          <p class="stat-number">{{ projects?.length ?? 0 }}</p>
        </div>
        <div class="stat-card gradient-orange">
          <div class="stat-icon">👁️</div>
          <p class="stat-label">Needs Review</p>
          <p class="stat-number">{{ byStatus['preview_sent'] ?? 0 }}</p>
        </div>
        <div class="stat-card gradient-teal">
          <div class="stat-icon">💬</div>
          <p class="stat-label">Feedback Sent</p>
          <p class="stat-number">{{ totalFeedback ?? 0 }}</p>
        </div>
      </div>

      <div class="glass-card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
          <h2 class="section-title"><span class="title-dot bg-violet-500"></span>Your Projects</h2>
        </div>
        <div class="divide-y divide-gray-50">
          <Link
            v-for="p in projects"
            :key="p.id"
            :href="route('projects.show', p.id)"
            class="flex items-center gap-4 px-6 py-4 hover:bg-violet-50/60 transition-colors group"
          >
            <div class="w-2.5 h-2.5 rounded-full flex-shrink-0 ring-2 ring-offset-1" :class="statusDotRing(p.status)" />
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold text-gray-900 truncate group-hover:text-violet-700 transition-colors">{{ p.project_name }}</p>
              <p class="text-xs text-gray-400">{{ p.project_code }}</p>
            </div>
            <span class="px-2.5 py-1 rounded-full text-xs font-semibold" :class="statusClass(p.status)">
              {{ statusLabel(p.status) }}
            </span>
            <span v-if="p.status === 'preview_sent'" class="text-xs bg-purple-100 text-purple-700 px-2.5 py-0.5 rounded-full font-bold animate-pulse">
              ⚡ Review!
            </span>
          </Link>
          <div v-if="!projects?.length" class="px-6 py-12 text-center text-gray-400 text-sm">
            <span class="text-3xl block mb-2">📭</span>
            No projects assigned yet.
          </div>
        </div>
      </div>
    </template>

    <!-- ═══════════════════════════════════════════════════════════
         PIC DASHBOARD
    ════════════════════════════════════════════════════════════════ -->
    <template v-else-if="role === 'pic'">

      <!-- Hero -->
      <div class="relative rounded-3xl overflow-hidden mb-8 hero-banner">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="relative z-10 px-8 py-10">
          <p class="text-white/60 text-xs font-semibold uppercase tracking-widest mb-1">PIC Dashboard</p>
          <h1 class="text-3xl font-extrabold text-white">
            Hello, <span class="text-orange-300">{{ $page.props.auth.user.full_name.split(' ')[0] }}</span> 🎯
          </h1>
          <p class="text-white/70 mt-2 text-sm">Here are the projects you're responsible for.</p>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4 mb-8">
        <div class="stat-card gradient-purple">
          <div class="stat-icon">📋</div>
          <p class="stat-label">Assigned Projects</p>
          <p class="stat-number">{{ projects?.length ?? 0 }}</p>
        </div>
        <div class="stat-card gradient-orange">
          <div class="stat-icon">⏳</div>
          <p class="stat-label">Waiting for Feedback</p>
          <p class="stat-number">{{ awaitingFeedback ?? 0 }}</p>
        </div>
      </div>

      <div class="glass-card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
          <h2 class="section-title"><span class="title-dot bg-violet-500"></span>My Assigned Projects</h2>
        </div>
        <div class="divide-y divide-gray-50">
          <Link
            v-for="p in projects"
            :key="p.id"
            :href="route('projects.show', p.id)"
            class="flex items-center gap-4 px-6 py-4 hover:bg-violet-50/60 transition-colors group"
          >
            <div class="w-2.5 h-2.5 rounded-full flex-shrink-0 ring-2 ring-offset-1" :class="statusDotRing(p.status)" />
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold text-gray-900 truncate group-hover:text-violet-700 transition-colors">{{ p.project_name }}</p>
              <p class="text-xs text-gray-400">{{ p.client?.company_name }} · {{ p.project_code }}</p>
            </div>
            <span class="px-2.5 py-1 rounded-full text-xs font-semibold" :class="statusClass(p.status)">
              {{ statusLabel(p.status) }}
            </span>
          </Link>
          <div v-if="!projects?.length" class="px-6 py-12 text-center text-gray-400 text-sm">
            <span class="text-3xl block mb-2">📭</span>
            No projects assigned yet.
          </div>
        </div>
      </div>
    </template>

  </AuthenticatedLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const $page = usePage()

const props = defineProps({
  role:             String,
  isSuperAdmin:     Boolean,
  stats:            Object,
  byStatus:         Object,
  statuses:         Array,
  recentProjects:   Array,
  projects:         Array,
  totalFeedback:    Number,
  awaitingFeedback: Number,
})

const roleSubtitle = computed(() => ({
  super_admin: 'Overview of all operations',
  admin:       'Overview of all operations',
  client:      'Your project status at a glance',
  pic:         'Your assigned projects',
}[props.role] ?? ''))

const maxCount = computed(() => {
  if (!props.byStatus) return 1
  return Math.max(1, ...Object.values(props.byStatus))
})

const barWidth = (count) => `${Math.round((count / maxCount.value) * 100)}%`
const barPercent = (count) => {
  if (!count) return ''
  const total = props.stats?.total_projects || 1
  return `${Math.round((count / total) * 100)}%`
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

const statusLabel = (s) => ({
  brief:                     'Brief',
  scheduled:                 'Scheduled',
  work_in_progress:          'Work In Progress',
  preview_sent:              'Preview Sent',
  feedback_received:         'Feedback Received',
  artwork_approved:          'Artwork Approved',
  final_artwork_preparation: 'FA Preparation',
  fa_sent:                   'FA Sent',
  project_closed:            'Closed',
}[s] ?? s)

const statusBarColor = (s) => ({
  brief:                     'bg-gradient-to-r from-gray-300 to-gray-400',
  scheduled:                 'bg-gradient-to-r from-blue-400 to-blue-500',
  work_in_progress:          'bg-gradient-to-r from-amber-400 to-orange-400',
  preview_sent:              'bg-gradient-to-r from-purple-500 to-violet-500',
  feedback_received:         'bg-gradient-to-r from-orange-400 to-pink-400',
  artwork_approved:          'bg-gradient-to-r from-green-400 to-emerald-500',
  final_artwork_preparation: 'bg-gradient-to-r from-teal-400 to-cyan-500',
  fa_sent:                   'bg-gradient-to-r from-indigo-500 to-blue-600',
  project_closed:            'bg-gradient-to-r from-gray-600 to-gray-800',
}[s] ?? 'bg-gray-300')

const statusDotRing = (s) => ({
  brief:                     'bg-gray-400 ring-gray-200',
  scheduled:                 'bg-blue-500 ring-blue-200',
  work_in_progress:          'bg-amber-500 ring-amber-200',
  preview_sent:              'bg-purple-500 ring-purple-200',
  feedback_received:         'bg-orange-500 ring-orange-200',
  artwork_approved:          'bg-green-500 ring-green-200',
  final_artwork_preparation: 'bg-teal-500 ring-teal-200',
  fa_sent:                   'bg-indigo-500 ring-indigo-200',
  project_closed:            'bg-gray-700 ring-gray-300',
}[s] ?? 'bg-gray-400 ring-gray-200')

const statusClass = (s) => ({
  brief:                     'bg-gray-100 text-gray-700',
  scheduled:                 'bg-blue-100 text-blue-700',
  work_in_progress:          'bg-amber-100 text-amber-700',
  preview_sent:              'bg-purple-100 text-purple-700',
  feedback_received:         'bg-orange-100 text-orange-700',
  artwork_approved:          'bg-green-100 text-green-700',
  final_artwork_preparation: 'bg-teal-100 text-teal-700',
  fa_sent:                   'bg-indigo-100 text-indigo-700',
  project_closed:            'bg-gray-800 text-gray-100',
}[s] ?? 'bg-gray-100 text-gray-700')

const priorityClass = (p) => ({
  high:   'bg-red-100 text-red-700',
  normal: 'bg-blue-100 text-blue-700',
  low:    'bg-gray-100 text-gray-600',
}[p] ?? 'bg-gray-100 text-gray-600')
</script>

<style scoped>
/* ── Hero Banner ─────────────────────────────────────────────────────── */
.hero-banner {
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 40%, #334155 80%, #475569 100%);
  min-height: 160px;
}

.blob {
  position: absolute;
  border-radius: 50%;
  filter: blur(60px);
  opacity: 0.35;
}
.blob-1 {
  width: 280px; height: 280px;
  background: #64748b;
  top: -80px; right: -60px;
}
.blob-2 {
  width: 200px; height: 200px;
  background: #94a3b8;
  bottom: -80px; left: 30%;
}
.blob-3 {
  width: 150px; height: 150px;
  background: #475569;
  top: 20px; left: -40px;
}

/* ── Hero Stat bubbles ───────────────────────────────────────────────── */
.hero-stat {
  background: rgba(255,255,255,0.12);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(255,255,255,0.2);
  border-radius: 16px;
  padding: 14px 20px;
  text-align: center;
  min-width: 80px;
}
.hero-stat-num {
  display: block;
  font-size: 2rem;
  font-weight: 800;
  color: #fff;
  line-height: 1;
}
.hero-stat-label {
  display: block;
  font-size: 0.65rem;
  font-weight: 600;
  color: rgba(255,255,255,0.65);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-top: 4px;
}

/* ── Stat Cards ──────────────────────────────────────────────────────── */
.stat-card {
  border-radius: 20px;
  padding: 20px;
  color: white;
  position: relative;
  overflow: hidden;
  box-shadow: 0 8px 24px -4px rgba(0,0,0,0.18);
  transition: transform 0.2s, box-shadow 0.2s;
}
.stat-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 14px 32px -6px rgba(0,0,0,0.25);
}
.stat-card::after {
  content: '';
  position: absolute;
  top: -20px; right: -20px;
  width: 90px; height: 90px;
  background: rgba(255,255,255,0.1);
  border-radius: 50%;
}

.gradient-purple { background: linear-gradient(135deg, #334155, #0f172a); }
.gradient-orange  { background: linear-gradient(135deg, #475569, #1e293b); }
.gradient-red     { background: linear-gradient(135deg, #1e293b, #0f172a); }
.gradient-teal    { background: linear-gradient(135deg, #374151, #111827); }

.stat-icon  { font-size: 1.6rem; margin-bottom: 8px; }
.stat-label { font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.07em; opacity: 0.75; }
.stat-number{ font-size: 2.4rem; font-weight: 900; line-height: 1.1; margin-top: 2px; }
.stat-sub   { font-size: 0.7rem; opacity: 0.7; margin-top: 4px; }

/* ── Glass card ──────────────────────────────────────────────────────── */
.glass-card {
  background: #fff;
  border-radius: 20px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 24px -4px rgba(15, 23, 42, 0.08);
}

/* ── Section title ───────────────────────────────────────────────────── */
.section-title {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.78rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #4b5563;
}
.title-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  flex-shrink: 0;
}

/* ── Quick Links ─────────────────────────────────────────────────────── */
.quick-link {
  border-radius: 20px;
  padding: 20px;
  color: white;
  display: block;
  transition: transform 0.2s, box-shadow 0.2s, opacity 0.2s;
  box-shadow: 0 6px 20px -4px rgba(0,0,0,0.2);
  text-decoration: none;
}
.quick-link:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 28px -4px rgba(0,0,0,0.28);
  opacity: 0.93;
}
.quick-link-icon  { font-size: 1.8rem; display: block; margin-bottom: 8px; }
.quick-link-label { font-weight: 700; font-size: 0.85rem; }
.quick-link-blue   { background: linear-gradient(135deg, #334155, #0f172a); }
.quick-link-green  { background: linear-gradient(135deg, #475569, #1e293b); }
.quick-link-purple { background: linear-gradient(135deg, #1e293b, #0f172a); }
.quick-link-orange { background: linear-gradient(135deg, #374151, #111827); }
@keyframes shimmer {
  0%   { transform: translateX(-100%); }
  100% { transform: translateX(200%); }
}
.shimmer-bar {
  background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.35) 50%, transparent 100%);
  animation: shimmer 2.2s infinite;
  width: 60%;
}
</style>