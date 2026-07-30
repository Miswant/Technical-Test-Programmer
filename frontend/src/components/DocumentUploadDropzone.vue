<template>
  <div
    class="rounded-xl border-2 border-dashed p-6 transition"
    :class="isDragging ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300 bg-gray-50 hover:bg-gray-100'"
    @dragover.prevent="isDragging = true"
    @dragleave.prevent="isDragging = false"
    @drop.prevent="handleDrop"
  >
    <div class="flex flex-col items-center justify-center text-center space-y-4">
      <div class="h-14 w-14 rounded-full bg-white shadow-sm border border-gray-200 flex items-center justify-center text-indigo-600">
        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.902A5 5 0 0115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
        </svg>
      </div>

      <div>
        <p class="text-sm font-semibold text-gray-800">Seret file ke area ini atau pilih manual</p>
        <p class="text-xs text-gray-500 mt-1">PDF, DOCX, JPG, PNG. Maksimal 5 MB per file.</p>
      </div>

      <label class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2.5 rounded-lg cursor-pointer transition">
        Pilih File
        <input type="file" class="hidden" :accept="accept" @change="handleFileChange" />
      </label>
    </div>

    <div v-if="error" class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
      {{ error }}
    </div>

    <div v-if="modelValue?.name" class="mt-4 rounded-lg border border-gray-200 bg-white px-4 py-3 flex items-center justify-between gap-3">
      <div class="min-w-0">
        <p class="text-sm font-semibold text-gray-800 truncate">{{ modelValue.name }}</p>
        <p class="text-xs text-gray-500">{{ formatBytes(modelValue.size) }} · {{ modelValue.type || 'unknown' }}</p>
      </div>
      <button type="button" class="text-sm font-semibold text-red-600 hover:text-red-700" @click="clearFile">Hapus</button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  modelValue: {
    type: Object,
    default: null,
  },
  accept: {
    type: String,
    default: '.pdf,.docx,.jpg,.jpeg,.png',
  },
  maxSizeMb: {
    type: Number,
    default: 5,
  },
})

const emit = defineEmits(['update:modelValue', 'invalid'])

const isDragging = ref(false)
const error = ref('')

const allowedMime = [
  'application/pdf',
  'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
  'image/jpeg',
  'image/png',
]

const validateFile = (file) => {
  error.value = ''

  if (!file) {
    error.value = 'File tidak ditemukan.'
    return false
  }

  if (!allowedMime.includes(file.type)) {
    error.value = 'Tipe file tidak valid. Hanya PDF, DOCX, JPG, dan PNG.'
    return false
  }

  const maxBytes = props.maxSizeMb * 1024 * 1024
  if (file.size > maxBytes) {
    error.value = `Ukuran file melebihi ${props.maxSizeMb} MB.`
    return false
  }

  return true
}

const setFile = (file) => {
  if (!validateFile(file)) {
    emit('invalid', error.value)
    return
  }

  emit('update:modelValue', file)
}

const handleFileChange = (event) => {
  const file = event.target.files?.[0]
  setFile(file)
  event.target.value = ''
}

const handleDrop = (event) => {
  isDragging.value = false
  const file = event.dataTransfer.files?.[0]
  setFile(file)
}

const clearFile = () => {
  error.value = ''
  emit('update:modelValue', null)
}

const formatBytes = (bytes) => {
  if (!bytes) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return `${parseFloat((bytes / Math.pow(k, i)).toFixed(2))} ${sizes[i]}`
}
</script>
