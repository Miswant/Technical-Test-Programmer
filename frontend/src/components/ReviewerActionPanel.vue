<template>
  <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-4">
    <h3 class="text-lg font-bold text-gray-800 border-b pb-3">Keputusan Penilai</h3>

    <div v-if="!canReview" class="text-sm text-gray-500 bg-gray-50 border border-gray-200 rounded-lg p-4">
      Action penilaian hanya tersedia untuk status SUBMITTED atau REVISED.
    </div>

    <div v-else class="grid grid-cols-1 gap-3">
      <button @click="openModal('APPROVED')" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2.5 rounded-lg font-semibold transition">
        Setujui
      </button>
      <button @click="openModal('REVISION_REQUIRED')" class="w-full bg-amber-500 hover:bg-amber-600 text-white px-4 py-2.5 rounded-lg font-semibold transition">
        Minta Revisi
      </button>
      <button @click="openModal('REJECTED')" class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2.5 rounded-lg font-semibold transition">
        Tolak
      </button>
    </div>

    <teleport to="body">
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center px-4">
        <div class="absolute inset-0 bg-slate-900/60" @click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 space-y-5">
          <div>
            <h4 class="text-xl font-extrabold text-gray-900">{{ modalTitle }}</h4>
            <p class="text-sm text-gray-500 mt-1">{{ modalDescription }}</p>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700">Catatan</label>
            <textarea
              v-model.trim="remarks"
              rows="5"
              class="mt-1 w-full rounded-lg border px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500"
              :class="error ? 'border-red-300' : 'border-gray-300'"
              :placeholder="remarksPlaceholder"
            ></textarea>
            <p v-if="error" class="text-sm text-red-600 mt-1">{{ error }}</p>
          </div>

          <div class="flex justify-end gap-3 pt-2">
            <button @click="closeModal" class="px-4 py-2.5 rounded-lg border border-gray-300 text-sm font-semibold text-gray-700 hover:bg-gray-50">
              Batal
            </button>
            <button
              @click="submitAction"
              :disabled="loading"
              class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-white text-sm font-semibold disabled:opacity-60 disabled:cursor-not-allowed"
              :class="confirmButtonClass"
            >
              <span v-if="loading" class="h-4 w-4 rounded-full border-2 border-white border-t-transparent animate-spin"></span>
              {{ loading ? 'Memproses...' : confirmLabel }}
            </button>
          </div>
        </div>
      </div>
    </teleport>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import api from '@/api'

const props = defineProps({
  project: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits(['updated'])

const showModal = ref(false)
const selectedStatus = ref('')
const remarks = ref('')
const error = ref('')
const loading = ref(false)

const canReview = computed(() => ['SUBMITTED', 'REVISED'].includes(props.project?.status))
const needsRemarks = computed(() => ['REVISION_REQUIRED', 'REJECTED'].includes(selectedStatus.value))

const modalTitle = computed(() => ({
  APPROVED: 'Setujui Permohonan',
  REVISION_REQUIRED: 'Minta Revisi Permohonan',
  REJECTED: 'Tolak Permohonan',
})[selectedStatus.value] || 'Keputusan Penilai')

const modalDescription = computed(() => ({
  APPROVED: 'Permohonan akan disetujui dan masuk ke status akhir APPROVED.',
  REVISION_REQUIRED: 'Pemohon akan diminta memperbaiki data atau dokumen sesuai catatan revisi.',
  REJECTED: 'Permohonan akan ditolak dan masuk ke status akhir REJECTED.',
})[selectedStatus.value] || '')

const remarksPlaceholder = computed(() => needsRemarks.value ? 'Tuliskan catatan/alasan secara jelas...' : 'Catatan opsional')
const confirmLabel = computed(() => selectedStatus.value === 'APPROVED' ? 'Setujui' : selectedStatus.value === 'REJECTED' ? 'Tolak' : 'Kirim Revisi')
const confirmButtonClass = computed(() => ({
  APPROVED: 'bg-green-600 hover:bg-green-700',
  REVISION_REQUIRED: 'bg-amber-500 hover:bg-amber-600',
  REJECTED: 'bg-red-600 hover:bg-red-700',
})[selectedStatus.value] || 'bg-indigo-600 hover:bg-indigo-700')

const openModal = (status) => {
  selectedStatus.value = status
  remarks.value = ''
  error.value = ''
  showModal.value = true
}

const closeModal = () => {
  if (loading.value) return
  showModal.value = false
}

const submitAction = async () => {
  error.value = ''

  if (needsRemarks.value && !remarks.value) {
    error.value = 'Catatan wajib diisi untuk revisi atau penolakan.'
    return
  }

  loading.value = true
  try {
    await api.post(`/projects/${props.project.id}/transition`, {
      status: selectedStatus.value,
      remarks: remarks.value || null,
    })
    showModal.value = false
    emit('updated')
  } catch (err) {
    error.value = err.response?.data?.message || 'Gagal memproses keputusan.'
  } finally {
    loading.value = false
  }
}
</script>
