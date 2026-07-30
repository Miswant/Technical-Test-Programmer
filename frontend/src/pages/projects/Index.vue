<template>
  <div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
      <div>
        <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">Daftar Permohonan</h2>
        <p class="text-sm text-gray-500 mt-1">{{ authStore.hasRole('penilai') ? 'Semua permohonan dokumen kelayakan.' : 'Permohonan yang Anda ajukan.' }}</p>
      </div>
      <router-link
        v-if="authStore.hasRole('pemohon')"
        :to="{ name: 'projects.create' }"
        class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2.5 rounded-lg shadow-sm hover:shadow transition shrink-0"
      >
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Buat Baru
      </router-link>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
          <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Pencarian</label>
          <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input
              v-model="filters.search"
              type="text"
              placeholder="Cari kode / judul..."
              class="w-full pl-9 pr-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500"
              @input="debounceFetch"
            />
          </div>
        </div>
        <div>
          <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Status</label>
          <select
            v-model="filters.status"
            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 bg-white"
            @change="fetchProjects()"
          >
            <option value="">Semua Status</option>
            <option v-for="s in statusOptions" :key="s.value" :value="s.value">{{ s.label }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Dari Tanggal</label>
          <input
            v-model="filters.date_from"
            type="date"
            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500"
            @change="fetchProjects()"
          />
        </div>
        <div>
          <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Sampai Tanggal</label>
          <input
            v-model="filters.date_to"
            type="date"
            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500"
            @change="fetchProjects()"
          />
        </div>
      </div>
      <div class="flex justify-end mt-3">
        <button @click="resetFilters" class="text-xs font-semibold text-gray-500 hover:text-indigo-600 transition">
          Reset Filter
        </button>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
      <div v-if="loading" class="p-10 flex items-center justify-center">
        <span class="h-8 w-8 rounded-full border-4 border-indigo-600 border-t-transparent animate-spin"></span>
      </div>

      <div v-else-if="projects.length === 0" class="p-10 text-center text-gray-500">
        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <p class="mt-3 font-semibold text-gray-700">Tidak ada data</p>
        <p class="text-sm text-gray-400 mt-1">Coba ubah filter atau buat permohonan baru.</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Kode</th>
              <th v-if="authStore.hasRole('penilai')" class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Pemohon</th>
              <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Judul</th>
              <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Dokumen</th>
              <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tanggal</th>
              <th class="px-4 py-3"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 bg-white">
            <tr v-for="project in projects" :key="project.id" class="hover:bg-gray-50 transition">
              <td class="px-4 py-3.5 whitespace-nowrap">
                <span class="text-sm font-mono font-bold text-indigo-600">{{ project.project_code }}</span>
              </td>
              <td v-if="authStore.hasRole('penilai')" class="px-4 py-3.5 whitespace-nowrap">
                <div class="flex items-center gap-2.5">
                  <div class="h-7 w-7 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-bold shrink-0">
                    {{ project.user?.name?.charAt(0)?.toUpperCase() }}
                  </div>
                  <span class="text-sm text-gray-800 font-medium truncate max-w-[160px]">{{ project.user?.name }}</span>
                </div>
              </td>
              <td class="px-4 py-3.5">
                <span class="text-sm text-gray-900 font-semibold truncate block max-w-[260px]">{{ project.title }}</span>
              </td>
              <td class="px-4 py-3.5 whitespace-nowrap">
                <span class="px-2 py-1 rounded text-xs font-bold uppercase tracking-wider" :class="statusBadgeClass(project.status)">
                  {{ statusLabel(project.status) }}
                </span>
              </td>
              <td class="px-4 py-3.5 whitespace-nowrap">
                <span class="inline-flex items-center gap-1 text-sm text-gray-600">
                  <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                  </svg>
                  {{ project.documents?.length || 0 }}
                </span>
              </td>
              <td class="px-4 py-3.5 whitespace-nowrap text-sm text-gray-500">
                {{ formatDate(project.created_at) }}
              </td>
              <td class="px-4 py-3.5 text-right whitespace-nowrap">
                <router-link
                  :to="{ name: 'projects.show', params: { id: project.id } }"
                  class="text-sm font-semibold text-indigo-600 hover:text-indigo-700 transition"
                >
                  Detail
                </router-link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Cursor Pagination -->
      <div v-if="projects.length > 0" class="flex items-center justify-between px-4 py-3 border-t border-gray-200 bg-gray-50">
        <span class="text-xs text-gray-500 font-medium">
          Menampilkan {{ projects.length }} data per halaman
        </span>
        <div class="flex items-center gap-2">
          <button
            :disabled="!prevCursor"
            @click="goToPrev"
            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border text-sm font-semibold transition disabled:opacity-40 disabled:cursor-not-allowed"
            :class="prevCursor ? 'border-gray-300 text-gray-700 hover:bg-white' : 'border-gray-200 text-gray-400'"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Sebelumnya
          </button>
          <button
            :disabled="!nextCursor"
            @click="goToNext"
            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border text-sm font-semibold transition disabled:opacity-40 disabled:cursor-not-allowed"
            :class="nextCursor ? 'border-gray-300 text-gray-700 hover:bg-white' : 'border-gray-200 text-gray-400'"
          >
            Selanjutnya
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/api'

const authStore = useAuthStore()

const loading = ref(true)
const projects = ref([])
const nextCursor = ref(null)
const prevCursor = ref(null)

let debounceTimer = null

const filters = reactive({
  search: '',
  status: '',
  date_from: '',
  date_to: '',
})

const statusOptions = [
  { value: 'DRAFT', label: 'Draft' },
  { value: 'SUBMITTED', label: 'Submitted' },
  { value: 'REVISION_REQUIRED', label: 'Revisi Diperlukan' },
  { value: 'REVISED', label: 'Revised' },
  { value: 'APPROVED', label: 'Disetujui' },
  { value: 'REJECTED', label: 'Ditolak' },
]

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

const formatDate = (dateStr) => {
  return new Date(dateStr).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
}

const buildParams = (cursor = null) => {
  const params = { per_page: 15 }
  if (filters.search) params.search = filters.search
  if (filters.status) params.status = filters.status
  if (filters.date_from) params.date_from = filters.date_from
  if (filters.date_to) params.date_to = filters.date_to
  if (cursor) params.cursor = cursor
  return params
}

const fetchProjects = async (cursor = null) => {
  loading.value = true
  try {
    const response = await api.get('/projects', { params: buildParams(cursor) })
    projects.value = response.data.data
    nextCursor.value = response.data.meta?.next_cursor || null
    prevCursor.value = response.data.meta?.previous_cursor || null
  } catch (error) {
    //
  } finally {
    loading.value = false
  }
}

const debounceFetch = () => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => fetchProjects(), 350)
}

const goToNext = () => {
  if (nextCursor.value) fetchProjects(nextCursor.value)
}

const goToPrev = () => {
  if (prevCursor.value) fetchProjects(prevCursor.value)
}

const resetFilters = () => {
  filters.search = ''
  filters.status = ''
  filters.date_from = ''
  filters.date_to = ''
  fetchProjects()
}

onMounted(() => fetchProjects())
</script>
