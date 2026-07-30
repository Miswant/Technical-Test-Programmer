<template>
  <div class="relative" ref="menuRef">
    <button
      @click="toggle"
      class="relative p-2 text-gray-500 hover:text-indigo-600 rounded-full hover:bg-gray-100 transition"
      :aria-expanded="open"
      aria-label="Notification"
    >
      <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
      </svg>
      <span
        v-if="notificationStore.hasUnread"
        class="absolute top-1 right-1 inline-flex h-2.5 w-2.5 rounded-full bg-red-500 ring-2 ring-white"
      ></span>
    </button>

    <transition
      enter-active-class="transition ease-out duration-150"
      enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition ease-in duration-100"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-95"
    >
      <div
        v-if="open"
        class="absolute right-0 mt-2 w-80 max-w-[90vw] bg-white border border-gray-200 rounded-xl shadow-xl overflow-hidden z-50"
      >
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
          <div>
            <h4 class="text-sm font-bold text-gray-900">Notifikasi</h4>
            <p class="text-xs text-gray-500">{{ notificationStore.unreadCount }} belum dibaca</p>
          </div>
          <button
            v-if="notificationStore.items.length > 0"
            @click="markAllAsRead"
            class="text-xs font-semibold text-indigo-600 hover:text-indigo-700"
          >
            Tandai semua
          </button>
        </div>

        <div v-if="notificationStore.loading" class="px-4 py-8 text-center text-sm text-gray-500">
          Memuat notifikasi...
        </div>

        <div v-else-if="notificationStore.items.length === 0" class="px-4 py-8 text-center text-sm text-gray-500">
          Belum ada notifikasi.
        </div>

        <div v-else class="max-h-96 overflow-y-auto divide-y divide-gray-100">
          <button
            v-for="item in notificationStore.items"
            :key="item.id"
            @click="handleRead(item)"
            class="w-full text-left px-4 py-3 hover:bg-gray-50 transition flex gap-3"
            :class="item.read_at ? 'bg-white' : 'bg-indigo-50/40'"
          >
            <div class="mt-1 h-2.5 w-2.5 rounded-full shrink-0" :class="item.read_at ? 'bg-gray-300' : 'bg-indigo-500'"></div>
            <div class="min-w-0 flex-1">
              <p class="text-sm font-semibold text-gray-900 truncate">{{ item.data?.project_code || 'Notifikasi' }}</p>
              <p class="text-xs text-gray-600 mt-0.5 line-clamp-2">{{ summary(item) }}</p>
              <p class="text-[11px] text-gray-400 mt-1">{{ formatDate(item.created_at) }}</p>
            </div>
          </button>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue'
import { useNotificationStore } from '@/stores/notification'

const notificationStore = useNotificationStore()
const open = ref(false)
const menuRef = ref(null)

const toggle = async () => {
  open.value = !open.value
  if (open.value && notificationStore.items.length === 0) {
    await notificationStore.fetchNotifications()
  }
}

const handleClickOutside = (event) => {
  if (menuRef.value && !menuRef.value.contains(event.target)) {
    open.value = false
  }
}

const summary = (item) => item.data?.remarks || item.data?.status || 'Notifikasi sistem'

const formatDate = (dateStr) => {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const handleRead = async (item) => {
  if (!item.read_at) {
    await notificationStore.markAsRead(item.id)
  }
  open.value = false
}

const markAllAsRead = async () => {
  await notificationStore.markAllAsRead()
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>
