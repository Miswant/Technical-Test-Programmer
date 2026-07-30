<template>
  <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-4">
    <div class="flex items-center justify-between border-b pb-3">
      <h3 class="text-lg font-bold text-gray-800">{{ title }}</h3>
      <span class="text-xs text-gray-500 font-semibold">{{ logs.length }} event</span>
    </div>

    <div v-if="logs.length === 0" class="text-center text-sm text-gray-500 py-6">
      {{ emptyMessage }}
    </div>

    <div v-else class="relative pl-6 border-l-2 border-slate-100 space-y-6">
      <div v-for="log in logs" :key="log.id" class="relative">
        <span class="absolute -left-[31px] top-0 h-4 w-4 rounded-full border-2 bg-white" :class="pointClass(log.new_status)"></span>
        <div class="text-xs text-gray-400 font-semibold">{{ formatDate(log.created_at, true) }}</div>
        <h5 class="text-sm font-bold text-gray-800 mt-1">
          {{ log.actor?.name || 'Sistem' }} &rarr;
          <span class="text-xs px-1.5 py-0.5 rounded font-bold uppercase tracking-wider ml-1" :class="statusBadgeClass(log.new_status)">
            {{ statusLabel(log.new_status) }}
          </span>
        </h5>
        <p v-if="log.remarks" class="text-xs text-gray-500 mt-1 italic">"{{ log.remarks }}"</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  logs: {
    type: Array,
    default: () => [],
  },
  title: {
    type: String,
    default: 'Riwayat Penilaian',
  },
  emptyMessage: {
    type: String,
    default: 'Belum ada audit log tersedia.',
  },
})

const statusBadgeClass = (status) => ({
  DRAFT: 'bg-gray-100 text-gray-700',
  SUBMITTED: 'bg-blue-50 text-blue-700 border border-blue-200',
  REVISION_REQUIRED: 'bg-amber-50 text-amber-700 border border-amber-200',
  REVISED: 'bg-indigo-50 text-indigo-700 border border-indigo-200',
  APPROVED: 'bg-green-50 text-green-700 border border-green-200',
  REJECTED: 'bg-red-50 text-red-700 border border-red-200',
})[status] || 'bg-gray-100 text-gray-700'

const pointClass = (status) => ({
  DRAFT: 'border-gray-400',
  SUBMITTED: 'border-blue-500',
  REVISION_REQUIRED: 'border-amber-500',
  REVISED: 'border-indigo-500',
  APPROVED: 'border-green-500',
  REJECTED: 'border-red-500',
})[status] || 'border-gray-400'

const statusLabel = (status) => ({
  DRAFT: 'Draft',
  SUBMITTED: 'Submitted',
  REVISION_REQUIRED: 'Revisi',
  REVISED: 'Revised',
  APPROVED: 'Disetujui',
  REJECTED: 'Ditolak',
})[status] || status

const formatDate = (dateStr, time = false) => {
  if (!dateStr) return ''
  const options = { day: '2-digit', month: 'long', year: 'numeric' }
  if (time) {
    options.hour = '2-digit'
    options.minute = '2-digit'
  }
  return new Date(dateStr).toLocaleDateString('id-ID', options)
}
</script>
