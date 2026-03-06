<template>
  <!-- ── Hero ── -->
  <div class="relative rounded-2xl overflow-hidden mb-6 hero-light">
    <div class="hero-light-blob blob-a" />
    <div class="hero-light-blob blob-b" />
    <div class="relative z-10 px-8 py-8">
      <p class="text-indigo-500 text-xs font-bold uppercase tracking-widest mb-1">Client Portal</p>
      <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900">
        Hi, <span class="text-indigo-600">{{ firstName }}</span> 👋
      </h1>
      <p class="text-gray-500 mt-2 text-sm">Track your project progress at a glance.</p>
    </div>
  </div>

  <!-- ── Stat Cards ── -->
  <div class="grid grid-cols-3 gap-4 mb-6">
    <div class="stat-card-light border-l-4 border-indigo-400">
      <div class="stat-light-icon">📁</div>
      <p class="stat-light-label">My Projects</p>
      <p class="stat-light-num text-indigo-600">{{ projects?.length ?? 0 }}</p>
    </div>
    <div class="stat-card-light border-l-4 border-purple-400">
      <div class="stat-light-icon">👁️</div>
      <p class="stat-light-label">Needs Review</p>
      <p class="stat-light-num text-purple-600">{{ byStatus['preview_sent'] ?? 0 }}</p>
    </div>
    <div class="stat-card-light border-l-4 border-emerald-400">
      <div class="stat-light-icon">💬</div>
      <p class="stat-light-label">Feedback Sent</p>
      <p class="stat-light-num text-emerald-600">{{ totalFeedback ?? 0 }}</p>
    </div>
  </div>

  <!-- ── Projects List ── -->
  <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100">
      <h2 class="section-title"><span class="title-dot bg-indigo-500"></span>Your Projects</h2>
    </div>
    <div class="divide-y divide-gray-50">
      <Link
        v-for="p in projects"
        :key="p.id"
        :href="route('projects.show', p.id)"
        class="flex items-center gap-4 px-6 py-4 hover:bg-indigo-50/50 transition-colors group"
      >
        <div class="w-2.5 h-2.5 rounded-full flex-shrink-0 ring-2 ring-offset-1" :class="statusDotRing(p.status)" />
        <div class="flex-1 min-w-0">
          <p class="text-sm font-semibold text-gray-900 truncate group-hover:text-indigo-700 transition-colors">{{ p.project_name }}</p>
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

<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { statusLabel, statusDotRing, statusClass } from '@/composables/useProject'

defineProps({
  projects:     Array,
  byStatus:     Object,
  totalFeedback: Number,
})

const $page     = usePage()
const firstName = computed(() => $page.props.auth.user.full_name.split(' ')[0])
</script>
