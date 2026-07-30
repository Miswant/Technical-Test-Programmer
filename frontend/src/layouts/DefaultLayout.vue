<template>
  <div class="min-h-screen flex flex-col bg-gray-100">
    <header class="bg-white shadow-sm border-b">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
        <h1 class="text-lg font-bold text-gray-900">SI Persetujuan Kelayakan</h1>
        <div class="flex items-center gap-4">
          <span class="text-sm text-gray-600 font-medium">{{ authStore.user?.name }} ({{ authStore.user?.roles?.[0]?.name }})</span>
          <button @click="logout" class="text-sm bg-red-50 text-red-600 px-3 py-1.5 rounded-md hover:bg-red-100 font-semibold transition">
            Logout
          </button>
        </div>
      </div>
    </header>
    <div class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <router-view />
    </div>
  </div>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const router = useRouter()

const logout = async () => {
  await authStore.logout()
  router.push({ name: 'login' })
}
</script>
