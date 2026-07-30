<template>
  <div class="space-y-8">
    <div class="flex items-center gap-4">
      <router-link :to="{ name: 'projects.index' }" class="p-2 rounded-lg border bg-white hover:bg-gray-50 text-gray-500 hover:text-gray-700 transition">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
      </router-link>
      <div class="flex-1">
        <div class="flex items-center gap-2.5">
          <span class="text-xs font-mono font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">
            {{ project?.project_code }}
          </span>
          <span :class="statusBadgeClass(project?.status)" class="px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wider">
            {{ statusLabel(project?.status) }}
          </span>
        </div>
        <h2 class="text-2xl font-extrabold text-gray-900 mt-1 tracking-tight">{{ project?.title }}</h2>
      </div>
      <router-link
        v-if="canEditProject"
        :to="{ name: 'projects.edit', params: { id: project.id } }"
        class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2.5 rounded-lg shadow-sm hover:shadow transition"
      >
        Edit
      </router-link>
    </div>

    <div v-if="loading" class="p-12 bg-white rounded-xl border border-gray-200 flex items-center justify-center">
      <span class="h-8 w-8 rounded-full border-4 border-indigo-600 border-t-transparent animate-spin"></span>
    </div>

    <div v-else class="grid lg:grid-cols-3 gap-8">
      <section class="lg:col-span-2 space-y-8">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-4">
          <h3 class="text-lg font-bold text-gray-800 border-b pb-3">Informasi Permohonan</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Pemohon</span>
              <p class="text-sm font-semibold text-gray-800 mt-1">{{ project?.user?.name }} ({{ project?.user?.email }})</p>
            </div>
            <div>
              <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Tanggal Pengajuan</span>
              <p class="text-sm font-semibold text-gray-800 mt-1">{{ formatDate(project?.created_at) }}</p>
            </div>
          </div>
          <div>
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Deskripsi</span>
            <p class="text-sm text-gray-600 mt-1.5 whitespace-pre-line leading-relaxed">{{ project?.description || 'Tidak ada deskripsi.' }}</p>
          </div>
        </div>

        <div v-if="canEditProject" class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-4">
          <div class="flex items-center justify-between border-b pb-3">
            <h3 class="text-lg font-bold text-gray-800">Upload Dokumen</h3>
            <span class="text-xs text-gray-500 font-semibold">PDF / DOCX / JPG / PNG | Maks 5 MB</span>
          </div>
          <DocumentUploadDropzone v-model="selectedFile" @invalid="handleInvalidFile" />
          <div class="flex justify-end">
            <button
              type="button"
              :disabled="uploading || !selectedFile"
              @click="uploadDocument"
              class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2.5 rounded-lg shadow-sm disabled:opacity-60 disabled:cursor-not-allowed"
            >
              <span v-if="uploading" class="h-4 w-4 rounded-full border-2 border-white border-t-transparent animate-spin"></span>
              {{ uploading ? 'Mengunggah...' : 'Upload Dokumen' }}
            </button>
          </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-4">
          <div class="flex justify-between items-center border-b pb-3">
            <h3 class="text-lg font-bold text-gray-800">Lampiran Dokumen</h3>
            <span class="text-xs text-gray-500 font-semibold">{{ project?.documents?.length || 0 }} File</span>
          </div>

          <div v-if="project?.documents?.length === 0" class="p-6 text-center text-gray-500 text-sm">
            Belum ada berkas dokumen terunggah.
          </div>

          <div v-else class="divide-y divide-gray-100">
            <div v-for="doc in project?.documents" :key="doc.id" class="py-3.5 flex items-center justify-between hover:bg-gray-50/50 rounded-lg px-2 -mx-2 transition">
              <div class="flex items-center gap-3 min-w-0">
                <svg class="h-6 w-6 text-indigo-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                <div class="min-w-0">
                  <p class="text-sm font-semibold text-gray-800 truncate max-w-[280px] sm:max-w-[420px]">{{ doc.file_name }}</p>
                  <p class="text-xs text-gray-400 mt-0.5">{{ formatBytes(doc.file_size) }} &bull; {{ doc.mime_type }}</p>
                </div>
              </div>
              <a
                :href="`/storage/${doc.file_path}`"
                target="_blank"
                download
                class="inline-flex items-center gap-1 text-xs font-bold text-indigo-600 hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg transition"
              >
                Unduh
              </a>
            </div>
          </div>
        </div>
      </section>

      <aside class="space-y-6">
        <ReviewerActionPanel v-if="authStore.hasRole('penilai') && project" :project="project" @updated="refreshProject" />

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-4">
          <h3 class="text-lg font-bold text-gray-800 border-b pb-3">Riwayat Penilaian</h3>
          <div v-if="project?.logs?.length === 0" class="text-center text-sm text-gray-500 py-6">
            Belum ada audit log tersedia.
          </div>
          <div v-else class="relative pl-6 border-l-2 border-slate-100 space-y-6">
            <div v-for="log in project?.logs" :key="log.id" class="relative">
              <span class="absolute -left-[31px] top-0 h-4 w-4 rounded-full border-2 bg-white" :class="logPointColor(log.new_status)"></span>
              <div class="text-xs text-gray-400 font-semibold">{{ formatDate(log.created_at, true) }}</div>
              <h5 class="text-sm font-bold text-gray-800 mt-1">
                {{ log.actor?.name }} &rarr;
                <span class="text-xs px-1.5 py-0.5 rounded font-bold uppercase tracking-wider ml-1" :class="statusBadgeClass(log.new_status)">
                  {{ statusLabel(log.new_status) }}
                </span>
              </h5>
              <p class="text-xs text-gray-500 mt-1 italic" v-if="log.remarks">"{{ log.remarks }}"</p>
            </div>
          </div>
        </div>
      </aside>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/api'
import DocumentUploadDropzone from '@/components/DocumentUploadDropzone.vue'
import ReviewerActionPanel from '@/components/ReviewerActionPanel.vue'

const route = useRoute()
const authStore = useAuthStore()

const loading = ref(true)
const uploading = ref(false)
const selectedFile = ref(null)
const project = ref(null)

const canEditProject = computed(() => {
  if (!project.value) return false
  if (!authStore.hasRole('pemohon')) return false
  if (project.value.user_id !== authStore.user?.id) return false
  return ['DRAFT', 'REVISION_REQUIRED'].includes(project.value.status)
})

const statusBadgeClass = (status) => ({
  DRAFT: 'bg-gray-100 text-gray-700',
  SUBMITTED: 'bg-blue-50 text-blue-700 border border-blue-200',
  REVISION_REQUIRED: 'bg-amber-50 text-amber-700 border border-amber-200',
  REVISED: 'bg-indigo-50 text-indigo-700 border border-indigo-200',
  APPROVED: 'bg-green-50 text-green-700 border border-green-200',
  REJECTED: 'bg-red-50 text-red-700 border border-red-200',
})[status] || 'bg-gray-100 text-gray-700'

const statusLabel = (status) => ({
  DRAFT: 'Draft',
  SUBMITTED: 'Submitted',
  REVISION_REQUIRED: 'Revisi',
  REVISED: 'Revised',
  APPROVED: 'Disetujui',
  REJECTED: 'Ditolak',
})[status] || status

const logPointColor = (status) => ({
  DRAFT: 'border-gray-400',
  SUBMITTED: 'border-blue-500',
  REVISION_REQUIRED: 'border-amber-500',
  REVISED: 'border-indigo-500',
  APPROVED: 'border-green-500',
  REJECTED: 'border-red-500',
})[status] || 'border-gray-400'

const formatDate = (dateStr, time = false) => {
  if (!dateStr) return ''
  const options = { day: '2-digit', month: 'long', year: 'numeric' }
  if (time) {
    options.hour = '2-digit'
    options.minute = '2-digit'
  }
  return new Date(dateStr).toLocaleDateString('id-ID', options)
}

const formatBytes = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const handleInvalidFile = () => {
  selectedFile.value = null
}

const refreshProject = async () => {
  try {
    const response = await api.get(`/projects/${route.params.id}`)
    project.value = response.data.data
  } catch (error) {
    //
  }
}

const uploadDocument = async () => {
  if (!selectedFile.value || !project.value) return

  uploading.value = true
  try {
    const formData = new FormData()
    formData.append('file', selectedFile.value)

    await api.post(`/projects/${project.value.id}/documents`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })

    const response = await api.get(`/projects/${route.params.id}`)
    project.value = response.data.data
    selectedFile.value = null
  } finally {
    uploading.value = false
  }
}

onMounted(async () => {
  try {
    const response = await api.get(`/projects/${route.params.id}`)
    project.value = response.data.data
  } catch (error) {
    //
  } finally {
    loading.value = false
  }
})
</script>
