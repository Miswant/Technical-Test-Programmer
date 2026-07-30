<template>
  <div class="min-h-screen bg-gray-50 flex">
    <!-- Sidebar - Desktop -->
    <aside class="hidden md:flex flex-col w-64 bg-slate-900 text-white shrink-0 border-r border-slate-800">
      <div class="p-6 border-b border-slate-800">
        <h1 class="text-xl font-bold tracking-tight">SI Persetujuan</h1>
        <p class="text-xs text-slate-400 mt-1">Dokumen Kelayakan</p>
      </div>

      <nav class="flex-1 p-4 space-y-1">
        <router-link
          v-for="item in menuItems"
          :key="item.name"
          :to="{ name: item.routeName }"
          class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold transition"
          :class="isActive(item.routeName) ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white'"
        >
          <component :is="item.icon" class="h-5 w-5 shrink-0" />
          {{ item.label }}
        </router-link>
      </nav>

      <div class="p-4 border-t border-slate-800 bg-slate-950/40">
        <div class="flex items-center gap-3">
          <div class="h-9 w-9 rounded-full bg-indigo-500 flex items-center justify-center font-bold text-white text-sm">
            {{ userInitial }}
          </div>
          <div class="min-w-0 flex-1">
            <p class="text-sm font-semibold text-white truncate">{{ authStore.user?.name }}</p>
            <p class="text-xs text-slate-400 capitalize truncate">{{ primaryRole }}</p>
          </div>
        </div>
      </div>
    </aside>

    <!-- Mobile Navigation Drawer / Header -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
      <header class="bg-white border-b border-gray-200 h-16 flex items-center justify-between px-4 sm:px-6 shrink-0 shadow-sm">
        <div class="flex items-center gap-4">
          <button @click="showMobileMenu = !showMobileMenu" class="md:hidden p-2 rounded-lg hover:bg-gray-100 text-gray-600">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
          <span class="font-bold text-gray-800 md:hidden">SI Persetujuan</span>
        </div>

        <div class="flex items-center gap-4">
          <!-- Notification Bell -->
          <div class="relative">
            <button class="p-2 text-gray-500 hover:text-indigo-600 rounded-full hover:bg-gray-100 transition">
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
              </svg>
            </button>
          </div>

          <div class="h-6 w-px bg-gray-200"></div>

          <button @click="handleLogout" class="text-sm font-semibold text-red-600 hover:text-red-700 transition">
            Keluar
          </button>
        </div>
      </header>

      <!-- Mobile Menu Sidebar Overlay -->
      <transition enter-active-class="transition-opacity ease-linear duration-300" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition-opacity ease-linear duration-300" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="showMobileMenu" class="fixed inset-0 z-40 bg-slate-900/60 md:hidden" @click="showMobileMenu = false"></div>
      </transition>

      <!-- Mobile Menu Sidebar Drawer -->
      <transition enter-active-class="transition ease-in-out duration-300 transform" enter-from-class="-translate-x-full" enter-to-class="translate-x-0" leave-active-class="transition ease-in-out duration-300 transform" leave-from-class="translate-x-0" leave-to-class="-translate-x-full">
        <aside v-if="showMobileMenu" class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 text-white flex flex-col md:hidden">
          <div class="p-6 border-b border-slate-800 flex justify-between items-center">
            <div>
              <h1 class="text-xl font-bold tracking-tight">SI Persetujuan</h1>
              <p class="text-xs text-slate-400 mt-1">Dokumen Kelayakan</p>
            </div>
            <button @click="showMobileMenu = false" class="text-slate-400 hover:text-white">
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <nav class="flex-1 p-4 space-y-1">
            <router-link
              v-for="item in menuItems"
              :key="item.name"
              :to="{ name: item.routeName }"
              class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold transition"
              :class="isActive(item.routeName) ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white'"
              @click="showMobileMenu = false"
            >
              <component :is="item.icon" class="h-5 w-5 shrink-0" />
              {{ item.label }}
            </router-link>
          </nav>

          <div class="p-4 border-t border-slate-800 bg-slate-950/40">
            <div class="flex items-center gap-3">
              <div class="h-9 w-9 rounded-full bg-indigo-500 flex items-center justify-center font-bold text-white text-sm">
                {{ userInitial }}
              </div>
              <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-white truncate">{{ authStore.user?.name }}</p>
                <p class="text-xs text-slate-400 capitalize truncate">{{ primaryRole }}</p>
              </div>
            </div>
          </div>
        </aside>
      </transition>

      <!-- Main Content Area -->
      <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const showMobileMenu = ref(false)

const primaryRole = computed(() => {
  return authStore.user?.roles?.[0]?.name || ''
})

const userInitial = computed(() => {
  return authStore.user?.name?.charAt(0).toUpperCase() || 'U'
})

const menuItems = computed(() => {
  const items = [
    {
      label: 'Dashboard',
      routeName: 'dashboard',
      icon: {
        template: `<svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>`
      }
    },
    {
      label: 'Permohonan',
      routeName: 'projects.index',
      icon: {
        template: `<svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>`
      }
    }
  ]

  if (authStore.hasRole('pemohon')) {
    items.push({
      label: 'Buat Baru',
      routeName: 'projects.create',
      icon: {
        template: `<svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>`
      }
    })
  }

  return items
})

const isActive = (routeName) => {
  return route.name === routeName || route.name?.startsWith(routeName + '.')
}

const handleLogout = async () => {
  await authStore.logout()
  router.push({ name: 'login' })
}
</script>
