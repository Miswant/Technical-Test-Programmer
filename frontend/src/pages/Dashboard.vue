<template>
  <div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
      <div>
        <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">Selamat Datang, {{ authStore.user?.name }}</h2>
        <p class="text-sm text-gray-500 mt-1">Berikut adalah ringkasan status permohonan dokumen kelayakan Anda.</p>
      </div>
      <router-link
        v-if="authStore.hasRole('pemohon')"
        :to="{ name: 'projects.create' }"
        class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2.5 rounded-lg shadow-sm hover:shadow transition"
      >
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Buat Permohonan Baru
      </router-link>
    </div>

    <!-- Statistik Cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
      <div
        v-for="card in statCards"
        :key="card.label"
        class="bg-white p-5 rounded-xl shadow-sm border border-gray-200 flex flex-col justify-between hover:shadow-md transition duration-200"
      >
        <div class="flex justify-between items-start">
          <span class="text-xs font-bold uppercase tracking-wider text-gray-500">{{ card.label }}</span>
          <span class="p-1.5 rounded-lg text-xs font-bold" :class="card.colorClass">
            <component :is="card.icon" class="h-4 w-4" />
          </span>
        </div>
        <div class="mt-4 flex items-baseline gap-2">
          <span class="text-3xl font-extrabold text-gray-900">{{ card.value }}</span>
          <span class="text-xs text-gray-400">Berkas</span>
        </div>
      </div>
    </div>

    <!-- Main Grid: Recent Projects & Quick Help -->
    <div class="grid lg:grid-cols-3 gap-8">
      <!-- Recent Projects -->
      <div class="lg:col-span-2 space-y-4">
        <div class="flex justify-between items-center">
          <h3 class="text-lg font-bold text-gray-800">Permohonan Terbaru</h3>
          <router-link :to="{ name: 'projects.index' }" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700 transition">
            Lihat Semua &rarr;
          </router-link>
        </div>

        <div v-if="loading" class="bg-white rounded-xl border border-gray-200 p-8 flex items-center justify-center">
          <span class="h-8 w-8 rounded-full border-4 border-indigo-600 border-t-transparent animate-spin"></span>
        </div>

        <div v-else-if="recentProjects.length === 0" class="bg-white rounded-xl border border-gray-200 p-8 text-center text-gray-500">
          <p class="font-medium text-gray-700">Belum ada pengajuan permohonan</p>
          <p class="text-sm text-gray-400 mt-1">Mulai dengan menekan tombol "Buat Permohonan Baru".</p>
        </div>

        <div v-else class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
          <div class="divide-y divide-gray-100">
            <div
              v-for="project in recentProjects"
              :key="project.id"
              class="p-4 sm:p-5 flex items-center justify-between hover:bg-gray-50 transition"
            >
              <div class="min-w-0 flex-1 pr-4">
                <div class="flex items-center gap-2.5">
                  <span class="text-xs font-mono font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">
                    {{ project.project_code }}
                  </span>
                  <span :class="statusBadgeClass(project.status)" class="px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wider">
                    {{ project.status }}
                  </span>
                </div>
                <h4 class="text-base font-bold text-gray-900 mt-1.5 truncate">{{ project.title }}</h4>
                <p class="text-xs text-gray-400 mt-1">Dibuat pada {{ formatDate(project.created_at) }}</p>
              </div>
              <router-link
                :to="{ name: 'projects.show', params: { id: project.id } }"
                class="inline-flex items-center justify-center p-2 rounded-lg hover:bg-indigo-50 text-gray-400 hover:text-indigo-600 transition"
              >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </router-link>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Guidance & Workflow Timeline -->
      <div class="space-y-6">
        <h3 class="text-lg font-bold text-gray-800">Alur Pengajuan</h3>
        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm space-y-6">
          <div class="relative pl-6 border-l-2 border-indigo-100 space-y-6">
            <div class="relative">
              <span class="absolute -left-[31px] top-0 h-4 w-4 rounded-full border-2 border-indigo-600 bg-white"></span>
              <h5 class="text-sm font-bold text-gray-900">1. Draft & Upload</h5>
              <p class="text-xs text-gray-500 mt-1">Buat permohonan baru dan unggah semua berkas kelayakan (PDF/DOCX, maks. 5MB).</p>
            </div>
            <div class="relative">
              <span class="absolute -left-[31px] top-0 h-4 w-4 rounded-full border-2 border-gray-300 bg-white"></span>
              <h5 class="text-sm font-bold text-gray-800">2. Submit Dokumen</h5>
              <p class="text-xs text-gray-500 mt-1">Kirim dokumen untuk verifikasi. Status menjadi SUBMITTED (Read-only).</p>
            </div>
            <div class="relative">
              <span class="absolute -left-[31px] top-0 h-4 w-4 rounded-full border-2 border-gray-300 bg-white"></span>
              <h5 class="text-sm font-bold text-gray-800">3. Evaluasi & Keputusan</h5>
              <p class="text-xs text-gray-500 mt-1">Penilai dapat menyetujui, meminta revisi berkas, atau menolak permohonan.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/api'

const authStore = useAuthStore()
const loading = ref(true)
const recentProjects = ref([])
const stats = ref({
  draft: 0,
  submitted: 0,
  revision_required: 0,
  revised: 0,
  approved: 0,
  rejected: 0,
  total: 0,
})

const statCards = computed(() => [
  {
    label: 'Draft',
    value: stats.value.draft,
    colorClass: 'bg-gray-100 text-gray-600',
    icon: {
      template: `<svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>`
    }
  },
  {
    label: 'Submitted',
    value: stats.value.submitted,
    colorClass: 'bg-blue-50 text-blue-600',
    icon: {
      template: `<svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>`
    }
  },
  {
    label: 'Revisi',
    value: stats.value.revision_required,
    colorClass: 'bg-amber-50 text-amber-600',
    icon: {
      template: `<svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H17"/></svg>`
    }
  },
  {
    label: 'Revised',
    value: stats.value.revised,
    colorClass: 'bg-indigo-50 text-indigo-600',
    icon: {
      template: `<svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>`
    }
  },
  {
    label: 'Disetujui',
    value: stats.value.approved,
    colorClass: 'bg-green-50 text-green-600',
    icon: {
      template: `<svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`
    }
  },
  {
    label: 'Ditolak',
    value: stats.value.rejected,
    colorClass: 'bg-red-50 text-red-600',
    icon: {
      template: `<svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`
    }
  }
])

const statusBadgeClass = (status) => {
  return {
    'DRAFT': 'bg-gray-100 text-gray-700',
    'SUBMITTED': 'bg-blue-50 text-blue-700 border border-blue-200',
    'REVISION_REQUIRED': 'bg-amber-50 text-amber-700 border border-amber-200',
    'REVISED': 'bg-indigo-50 text-indigo-700 border border-indigo-200',
    'APPROVED': 'bg-green-50 text-green-700 border border-green-200',
    'REJECTED': 'bg-red-50 text-red-700 border border-red-200',
  }[status] || 'bg-gray-100 text-gray-700'
}

const formatDate = (dateStr) => {
  return new Date(dateStr).toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'long',
    year: 'numeric',
  })
}

onMounted(async () => {
  try {
    const [statsRes, projectsRes] = await Promise.all([
      api.get('/dashboard'),
      api.get('/projects', { params: { per_page: 5 } }),
    ])
    stats.value = statsRes.data.data
    recentProjects.value = projectsRes.data.data
  } catch (error) {
    //
  } finally {
    loading.value = false
  }
})
</script>
