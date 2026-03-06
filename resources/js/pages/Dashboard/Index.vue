<template>
  <AuthenticatedLayout title="Dashboard" :subtitle="roleSubtitle">

    <AdminDashboard
      v-if="isAdmin"
      :stats="stats"
      :by-status="byStatus"
      :statuses="statuses"
      :recent-projects="recentProjects"
      :is-super-admin="isSuperAdmin"
    />

    <ClientDashboard
      v-else-if="role === 'client'"
      :projects="projects"
      :by-status="byStatus"
      :total-feedback="totalFeedback"
    />

    <PicDashboard
      v-else-if="role === 'pic'"
      :projects="projects"
      :awaiting-feedback="awaitingFeedback"
    />

  </AuthenticatedLayout>
</template>

<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import AdminDashboard  from '@/Pages/Dashboard/AdminDashboard.vue'
import ClientDashboard from '@/Pages/Dashboard/ClientDashboard.vue'
import PicDashboard    from '@/Pages/Dashboard/PicDashboard.vue'

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

const isAdmin = computed(() => ['admin', 'super_admin'].includes(props.role))

const roleSubtitle = computed(() => ({
  super_admin: 'Overview of all operations',
  admin:       'Overview of all operations',
  client:      'Your project status at a glance',
  pic:         'Your assigned projects',
}[props.role] ?? ''))
</script>

<style>
/* ── Hero Banner ──────────────────────────────────────────────────── */
.hero-light {
  background: linear-gradient(135deg, #eef2ff 0%, #f0f9ff 50%, #faf5ff 100%);
  border: 1px solid #e0e7ff;
  min-height: 140px;
}
.hero-light-blob {
  position: absolute;
  border-radius: 50%;
  filter: blur(50px);
  opacity: 0.45;
}
.blob-a { width: 260px; height: 260px; background: #c7d2fe; top: -80px; right: -40px; }
.blob-b { width: 180px; height: 180px; background: #bfdbfe; bottom: -60px; left: 25%; }

/* ── Hero Stat Pills ──────────────────────────────────────────────── */
.hero-pill {
  background: rgba(255,255,255,0.8);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(99,102,241,0.15);
  border-radius: 14px;
  padding: 12px 18px;
  text-align: center;
  min-width: 80px;
  box-shadow: 0 2px 8px rgba(99,102,241,0.08);
}
.hero-pill-num   { display: block; font-size: 1.8rem; font-weight: 800; color: #1e293b; line-height: 1; }
.hero-pill-label { display: block; font-size: 0.6rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.06em; margin-top: 4px; }

/* ── Stat Cards ───────────────────────────────────────────────────── */
.stat-card-light {
  background: #fff;
  border-radius: 16px;
  padding: 18px 16px;
  border: 1px solid #f1f5f9;
  box-shadow: 0 2px 12px rgba(15,23,42,0.05);
  transition: transform 0.18s, box-shadow 0.18s;
  display: block;
  text-decoration: none;
}
.stat-card-light:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(99,102,241,0.12); }
.stat-light-icon  { font-size: 1.4rem; margin-bottom: 8px; }
.stat-light-label { font-size: 0.6rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.07em; color: #94a3b8; }
.stat-light-num   { font-size: 2rem; font-weight: 900; color: #1e293b; line-height: 1.1; margin-top: 2px; }
.stat-light-sub   { font-size: 0.65rem; color: #94a3b8; margin-top: 3px; }

/* ── Section Title ────────────────────────────────────────────────── */
.section-title { display: flex; align-items: center; gap: 8px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #4b5563; }
.title-dot     { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }

/* ── Shimmer ──────────────────────────────────────────────────────── */
@keyframes shimmer {
  0%   { transform: translateX(-100%); }
  100% { transform: translateX(200%); }
}
.shimmer-bar {
  background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.5) 50%, transparent 100%);
  animation: shimmer 2.2s infinite;
  width: 60%;
}
</style>