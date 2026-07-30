<template>
  <div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between gap-4">
      <div>
        <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">{{ isEditMode ? 'Edit Permohonan' : 'Buat Permohonan' }}</h2>
        <p class="text-sm text-gray-500 mt-1">{{ isEditMode ? 'Perbarui data permohonan Anda selama status masih dapat diedit.' : 'Lengkapi data permohonan baru sebelum disubmit.' }}</p>
      </div>
      <router-link :to="{ name: 'projects.index' }" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700">Kembali</router-link>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 sm:p-8">
      <form class="space-y-6" @submit.prevent="handleSubmit">
        <div>
          <label class="block text-sm font-semibold text-gray-700">Judul Permohonan</label>
          <input
            v-model.trim="form.title"
            type="text"
            maxlength="255"
            class="mt-1 w-full rounded-lg border px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500"
            :class="errors.title ? 'border-red-300' : 'border-gray-300'"
            placeholder="Contoh: Permohonan Kelayakan Usaha"
          />
          <p v-if="errors.title" class="mt-1 text-sm text-red-600">{{ errors.title }}</p>
        </div>

        <div>
          <label class="block text-sm font-semibold text-gray-700">Deskripsi</label>
          <textarea
            v-model.trim="form.description"
            rows="7"
            class="mt-1 w-full rounded-lg border px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500"
            :class="errors.description ? 'border-red-300' : 'border-gray-300'"
            placeholder="Tuliskan ringkasan kebutuhan, latar belakang, atau informasi tambahan"
          ></textarea>
          <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description }}</p>
        </div>

        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <label class="block text-sm font-semibold text-gray-700">Dokumen Pendukung</label>
            <span class="text-xs text-gray-500">Opsional saat draft, wajib saat submit di tahap berikutnya</span>
          </div>
          <DocumentUploadDropzone v-model="selectedFile" @invalid="handleInvalidFile" />
        </div>

        <div class="grid sm:grid-cols-2 gap-4 bg-gray-50 border border-gray-200 rounded-xl p-4 text-sm text-gray-600">
          <div>
            <p class="font-semibold text-gray-800">Status saat ini</p>
            <p class="mt-1">{{ isEditMode ? projectStatusLabel : 'DRAFT' }}</p>
          </div>
          <div>
            <p class="font-semibold text-gray-800">Akses edit</p>
            <p class="mt-1">{{ isEditMode ? 'Hanya jika status DRAFT / REVISION_REQUIRED' : 'Akan dibuat sebagai DRAFT' }}</p>
          </div>
        </div>

        <div v-if="serverError" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
          {{ serverError }}
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
          <router-link :to="{ name: 'projects.index' }" class="px-4 py-2.5 rounded-lg border border-gray-300 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
            Batal
          </router-link>
          <button
            type="submit"
            :disabled="loading"
            class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2.5 rounded-lg shadow-sm disabled:opacity-60 disabled:cursor-not-allowed"
          >
            <span v-if="loading" class="h-4 w-4 rounded-full border-2 border-white border-t-transparent animate-spin"></span>
            {{ loading ? 'Menyimpan...' : submitLabel }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { computed, reactive, ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/api'
import DocumentUploadDropzone from '@/components/DocumentUploadDropzone.vue'

const route = useRoute()
const router = useRouter()

const loading = ref(false)
const serverError = ref('')
const project = ref(null)
const selectedFile = ref(null)

const form = reactive({
  title: '',
  description: '',
})

const errors = reactive({
  title: '',
  description: '',
})

const isEditMode = computed(() => !!route.params.id)
const submitLabel = computed(() => (isEditMode.value ? 'Simpan Perubahan' : 'Simpan Draft'))
const projectStatusLabel = computed(() => project.value?.status || 'DRAFT')

const statusEditable = (status) => ['DRAFT', 'REVISION_REQUIRED'].includes(status)

const validate = () => {
  errors.title = ''
  errors.description = ''

  if (!form.title) {
    errors.title = 'Judul permohonan wajib diisi.'
  } else if (form.title.length > 255) {
    errors.title = 'Judul maksimal 255 karakter.'
  }

  if (form.description && form.description.length > 5000) {
    errors.description = 'Deskripsi maksimal 5000 karakter.'
  }

  return !errors.title && !errors.description
}

const loadProject = async () => {
  if (!isEditMode.value) return

  loading.value = true
  try {
    const response = await api.get(`/projects/${route.params.id}`)
    project.value = response.data.data

    if (!statusEditable(project.value.status)) {
      serverError.value = 'Project hanya dapat diedit pada status DRAFT atau REVISION_REQUIRED.'
      return
    }

    form.title = project.value.title || ''
    form.description = project.value.description || ''
  } catch (error) {
    serverError.value = error.response?.data?.message || 'Gagal memuat detail permohonan.'
  } finally {
    loading.value = false
  }
}

const handleInvalidFile = (message) => {
  serverError.value = message
}

const handleSubmit = async () => {
  serverError.value = ''

  if (!validate()) return

  loading.value = true
  try {
    const payload = new FormData()
    payload.append('title', form.title)
    payload.append('description', form.description || '')

    if (selectedFile.value) {
      payload.append('file', selectedFile.value)
    }

    const config = { headers: { 'Content-Type': 'multipart/form-data' } }

    if (isEditMode.value) {
      await api.post(`/projects/${route.params.id}?_method=PATCH`, payload, config)
      await router.push({ name: 'projects.show', params: { id: route.params.id } })
      return
    }

    const response = await api.post('/projects', payload, config)
    await router.push({ name: 'projects.show', params: { id: response.data.data.id } })
  } catch (error) {
    const responseErrors = error.response?.data?.errors
    if (responseErrors) {
      errors.title = responseErrors.title?.[0] || ''
      errors.description = responseErrors.description?.[0] || ''
      serverError.value = Object.values(responseErrors).flat()[0] || error.response?.data?.message || 'Gagal menyimpan permohonan.'
    } else {
      serverError.value = error.response?.data?.message || 'Gagal menyimpan permohonan.'
    }
  } finally {
    loading.value = false
  }
}

onMounted(loadProject)
</script>
