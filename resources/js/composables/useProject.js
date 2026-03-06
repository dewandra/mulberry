/**
 * useProject.js
 * Shared helpers for project status, priority, cover background,
 * avatars, and date formatting — used by Dashboard, Projects/Index, Projects/Show.
 *
 * Import only what you need:
 *   import { statusLabel, statusClass, coverBg } from '@/composables/useProject'
 */

// ─── Status Labels ────────────────────────────────────────────────────────────

/** Full readable label used in lists, dropdowns, dashboard. */
export const statusLabel = (s) => ({
  brief:                    'Brief',
  scheduled:                'Scheduled',
  work_in_progress:         'Work In Progress',
  preview_sent:             'Preview Sent',
  feedback_received:        'Feedback Received',
  artwork_approved:         'Artwork Approved',
  final_artwork_preparation:'FA Preparation',
  fa_sent:                  'FA Sent',
  project_closed:           'Closed',
}[s] ?? s)

/** Short label used in status timeline stepper (Show page). */
export const statusLabelShort = (s) => ({
  brief:                    'Brief',
  scheduled:                'Scheduled',
  work_in_progress:         'WIP',
  preview_sent:             'Preview Sent',
  feedback_received:        'Feedback',
  artwork_approved:         'Approved',
  final_artwork_preparation:'FA Prep',
  fa_sent:                  'FA Sent',
  project_closed:           'Closed',
}[s] ?? s)

// ─── Status Colors ────────────────────────────────────────────────────────────

/** Soft badge pill: `bg-*-100 text-*-700` — used on cards and lists. */
export const statusClass = (s) => ({
  brief:                    'bg-gray-100 text-gray-700',
  scheduled:                'bg-blue-100 text-blue-700',
  work_in_progress:         'bg-amber-100 text-amber-700',
  preview_sent:             'bg-purple-100 text-purple-700',
  feedback_received:        'bg-orange-100 text-orange-700',
  artwork_approved:         'bg-green-100 text-green-700',
  final_artwork_preparation:'bg-teal-100 text-teal-700',
  fa_sent:                  'bg-indigo-100 text-indigo-700',
  project_closed:           'bg-gray-800 text-gray-100',
}[s] ?? 'bg-gray-100 text-gray-700')

/** Dot + ring indicator (used in dashboard activity list). */
export const statusDotRing = (s) => ({
  brief:                    'bg-gray-400 ring-gray-200',
  scheduled:                'bg-blue-500 ring-blue-200',
  work_in_progress:         'bg-amber-500 ring-amber-200',
  preview_sent:             'bg-purple-500 ring-purple-200',
  feedback_received:        'bg-orange-500 ring-orange-200',
  artwork_approved:         'bg-green-500 ring-green-200',
  final_artwork_preparation:'bg-teal-500 ring-teal-200',
  fa_sent:                  'bg-indigo-500 ring-indigo-200',
  project_closed:           'bg-gray-700 ring-gray-300',
}[s] ?? 'bg-gray-400 ring-gray-200')

/** Gradient progress bar (dashboard recent projects). */
export const statusBarColor = (s) => ({
  brief:                    'bg-gradient-to-r from-gray-300 to-gray-400',
  scheduled:                'bg-gradient-to-r from-blue-400 to-blue-500',
  work_in_progress:         'bg-gradient-to-r from-amber-400 to-orange-400',
  preview_sent:             'bg-gradient-to-r from-purple-500 to-violet-500',
  feedback_received:        'bg-gradient-to-r from-orange-400 to-pink-400',
  artwork_approved:         'bg-gradient-to-r from-green-400 to-emerald-500',
  final_artwork_preparation:'bg-gradient-to-r from-teal-400 to-cyan-500',
  fa_sent:                  'bg-gradient-to-r from-indigo-500 to-blue-600',
  project_closed:           'bg-gradient-to-r from-gray-600 to-gray-800',
}[s] ?? 'bg-gray-300')

/** Solid progress bar color (project card progress strip). */
export const statusBarClass = (s) => ({
  brief:                    'bg-gray-400',
  scheduled:                'bg-blue-500',
  work_in_progress:         'bg-amber-500',
  preview_sent:             'bg-purple-500',
  feedback_received:        'bg-orange-500',
  artwork_approved:         'bg-green-500',
  final_artwork_preparation:'bg-teal-500',
  fa_sent:                  'bg-indigo-500',
  project_closed:           'bg-gray-800',
}[s] ?? 'bg-gray-300')

/** Workflow completion percentage (used for progress bar width). */
export const statusProgress = (s) => ({
  brief:                    '10%',
  scheduled:                '22%',
  work_in_progress:         '35%',
  preview_sent:             '50%',
  feedback_received:        '60%',
  artwork_approved:         '72%',
  final_artwork_preparation:'84%',
  fa_sent:                  '92%',
  project_closed:           '100%',
}[s] ?? '5%')

// ─── Cover Background ─────────────────────────────────────────────────────────

/** Diagonal gradient cover for project card/header fallback (no thumbnail). */
export const coverBg = (status) => ({
  brief:                    'bg-gradient-to-br from-gray-400 to-gray-600',
  scheduled:                'bg-gradient-to-br from-blue-400 to-blue-600',
  work_in_progress:         'bg-gradient-to-br from-amber-400 to-orange-500',
  preview_sent:             'bg-gradient-to-br from-purple-400 to-purple-600',
  feedback_received:        'bg-gradient-to-br from-orange-400 to-red-500',
  artwork_approved:         'bg-gradient-to-br from-green-400 to-emerald-600',
  final_artwork_preparation:'bg-gradient-to-br from-teal-400 to-cyan-600',
  fa_sent:                  'bg-gradient-to-br from-indigo-400 to-indigo-600',
  project_closed:           'bg-gradient-to-br from-gray-700 to-gray-900',
}[status] ?? 'bg-gradient-to-br from-gray-400 to-gray-600')

// ─── Priority ─────────────────────────────────────────────────────────────────

/** Priority pill — solid (used on project cards and Show page). */
export const priorityClass = (p) => ({
  high:   'bg-red-500 text-white',
  normal: 'bg-blue-500 text-white',
  low:    'bg-gray-200 text-gray-700',
}[p] ?? 'bg-gray-200 text-gray-700')

/** Priority pill — solid with different low style (used on Index card badge). */
export const priorityBadgeClass = (p) => ({
  high:   'bg-red-500 text-white',
  normal: 'bg-gray-500 text-white',
  low:    'bg-gray-300 text-gray-700',
}[p] ?? 'bg-gray-300 text-gray-700')

/** Priority pill — soft/light (used on dashboard). */
export const priorityClassSoft = (p) => ({
  high:   'bg-red-100 text-red-700',
  normal: 'bg-blue-100 text-blue-700',
  low:    'bg-gray-100 text-gray-600',
}[p] ?? 'bg-gray-100 text-gray-600')

// ─── Avatar ───────────────────────────────────────────────────────────────────

/** Avatar background color by user role. */
export const avatarBg = (role) => ({
  super_admin: 'bg-purple-500',
  admin:       'bg-blue-500',
  client:      'bg-emerald-500',
  pic:         'bg-orange-500',
}[role] ?? 'bg-gray-400')

/** Extract up to 2 initials from a full name. */
export const initials = (name) => {
  if (!name) return '?'
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

// ─── Date Formatters ──────────────────────────────────────────────────────────

/** Format as localized short date  e.g. "1 Jan 2024". */
export const formatDate = (dateStr) => {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString('en-GB', {
    day: 'numeric', month: 'short', year: 'numeric',
  })
}

/**
 * Relative time for < 24 h ("5 min ago", "3 hrs ago"),
 * localized date for ≥ 24 h ("1 Jan 2024").
 */
export const formatUpdated = (dateStr) => {
  if (!dateStr) return ''
  const diffMs = Date.now() - new Date(dateStr).getTime()
  const mins   = Math.floor(diffMs / 60000)
  const hours  = Math.floor(mins / 60)
  const days   = Math.floor(hours / 24)
  if (mins  < 1) return 'just now'
  if (hours < 1) return `${mins} min ago`
  if (days  < 1) return `${hours} hrs ago`
  return formatDate(dateStr)
}

/** Compact relative time: "2d ago", "5h ago", "10m ago". */
export const timeAgo = (dateStr) => {
  if (!dateStr) return ''
  const diffMs = Date.now() - new Date(dateStr).getTime()
  const mins   = Math.floor(diffMs / 60000)
  const hours  = Math.floor(mins / 60)
  const days   = Math.floor(hours / 24)
  if (days  > 0) return `${days}d ago`
  if (hours > 0) return `${hours}h ago`
  if (mins  > 0) return `${mins}m ago`
  return 'just now'
}
