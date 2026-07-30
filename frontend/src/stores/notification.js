import { defineStore } from 'pinia'
import api from '@/api'

export const useNotificationStore = defineStore('notification', {
  state: () => ({
    items: [],
    unreadCount: 0,
    loading: false,
  }),
  getters: {
    hasUnread: (state) => state.unreadCount > 0,
  },
  actions: {
    async fetchNotifications() {
      this.loading = true
      try {
        const response = await api.get('/notifications')
        this.items = response.data.data || []
        this.unreadCount = response.data.unread_count ?? this.items.filter((item) => !item.read_at).length
        return this.items
      } finally {
        this.loading = false
      }
    },
    async markAsRead(id) {
      await api.post(`/notifications/${id}/read`)
      this.items = this.items.map((item) => (item.id === id ? { ...item, read_at: new Date().toISOString() } : item))
      this.unreadCount = Math.max(this.unreadCount - 1, 0)
    },
    async markAllAsRead() {
      await api.post('/notifications/read-all')
      this.items = this.items.map((item) => ({ ...item, read_at: item.read_at || new Date().toISOString() }))
      this.unreadCount = 0
    },
    syncUnreadCount(count) {
      this.unreadCount = count
    },
    clear() {
      this.items = []
      this.unreadCount = 0
    },
  },
})
