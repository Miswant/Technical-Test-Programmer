<template>
  <div class="space-y-8">
    <div class="flex items-center gap-4">
      <router-link :to="{ name: 'projects.show', params: { id: route.params.id } }" class="p-2 rounded-lg border bg-white hover:bg-gray-50 text-gray-500 hover:text-gray-700 transition">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
      </router-link>
      <div>
        <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">Riwayat & Audit Log</h2>
        <p class="text-sm text-gray-500 mt-1">{{ project?.project_code }} · {{ project?.title }}</p>
      </div>
    </div>

    <div v-if="loading" class="p-12 bg-white rounded-xl border border-gray-200 flex items-center justify-center">
      <span class="h-8 w-8 rounded-full border-4 border-indigo-600 border-t-transparent animate-spin"></span>
    </div>

    <div v-else class="grid lg:grid-cols-3 gap-8">
      <section class="lg:col-span-2">
        <AuditTimeline :logs="logs" title="Timeline Audit Log" empty-message="Belum ada riwayat yang tersedia." />

        <div v-if="logs.length > 0" class="mt-4 flex items-center justify-between bg-white rounded-xl border border-gray-200 shadow-sm px-4 py-3">
          <button
            :disabled="!prevCursor"
            @click="goToPrev"
            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border text-sm font-semibold transition disabled:opacity-40 disabled:cursor-not-allowed"
            :class="prevCursor ? 'border-gray-300 text-gray-700 hover:bg-white' : 'border-gray-200 text-gray-400'"
          >
            Sebelumnya
          </button>
          <button
            :disabled="!nextCursor"
            @click="goToNext"
            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border text-sm font-semibold transition disabled:opacity-40 disabled:cursor-not-allowed"
            :class="nextCursor ? 'border-gray-300 text-gray-700 hover:bg-white' : 'border-gray-200 text-gray-400'"
          >
            Selanjutnya
          </button>
        </div>
      </section>

      <aside class="space-y-6">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-4">
          <h3 class="text-lg font-bold text-gray-800 border-b pb-3">Ringkasan Project</h3>
          <dl class="space-y-4 text-sm">
            <div>
              <dt class="text-gray-500 font-semibold">Kode</dt>
              <dd class="text-gray-900 font-medium mt-1">{{ project?.project_code }}</dd>
            </div>
            <div>
              <dt class="text-gray-500 font-semibold">Status</dt>
              <dd class="text-gray-900 font-medium mt-1">{{ project?.status }}</dd>
            </div>
            <div>
              <dt class="text-gray-500 font-semibold">Pemohon</dt>
              <dd class="text-gray-900 font-medium mt-1">{{ project?.user?.name }}</dd>
            </div>
            <div>
              <dt class="text-gray-500 font-semibold">Total Log</dt>
              <dd class="text-gray-900 font-medium mt-1">{{ totalLogs }}</dd>
            </div>
          </dl>
        </div>
      </aside>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/api'
import AuditTimeline from '@/components/AuditTimeline.vue'

const route = useRoute()
const loading = ref(true)
const project = ref(null)
const logs = ref([])
const nextCursor = ref(null)
const prevCursor = ref(null)
const totalLogs = ref(0)

const loadLogs = async (cursor = null) => {
  loading.value = true
  try {
    const [projectRes, logRes] = await Promise.all([
      api.get(`/projects/${route.params.id}`),
      api.get(`/projects/${route.params.id}/logs`, { params: { cursor, per_page: 10 } }),
    ])

    project.value = projectRes.data.data
    logs.value = logRes.data.data || []
    nextCursor.value = logRes.data.meta?.next_cursor || null
    prevCursor.value = logRes.data.meta?.previous_cursor || null
    totalLogs.value = logRes.data.meta?.total || logs.value.length
  } finally {
    loading.value = false
  }
}

const goToNext = () => {
  if (nextCursor.value) loadLogs(nextCursor.value)
}

const goToPrev = () => {
  if (prevCursor.value) loadLogs(prevCursor.value)
}

onMounted(() => loadLogs())
</script>
