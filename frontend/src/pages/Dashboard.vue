<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h2 class="text-2xl font-bold text-gray-800">Dashboard</h2>
      <router-link v-if="authStore.hasRole('pemohon')" :to="{ name: 'projects.create' }" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 font-semibold transition">
        Buat Permohonan Baru
      </router-link>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
      <div v-for="(val, key) in stats" :key="key" class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 flex flex-col justify-between">
        <span class="text-sm font-medium text-gray-500 capitalize">{{ key.replace('_', ' ') }}</span>
        <span class="text-2xl font-bold text-gray-900 mt-2">{{ val }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/api'

const authStore = useAuthStore()
const stats = ref({
  draft: 0,
  submitted: 0,
  revision_required: 0,
  revised: 0,
  approved: 0,
  rejected: 0,
  total: 0
})

onMounted(async () => {
  try {
    const response = await api.get('/dashboard')
    stats.value = response.data.data
  } catch (e) {
    //
  }
})
</script>
