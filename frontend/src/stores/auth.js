import { defineStore } from 'pinia'
import api from '@/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('token') || null,
    loading: false,
  }),
  getters: {
    isAuthenticated: (state) => !!state.token,
    primaryRole: (state) => state.user?.roles?.[0]?.name || null,
    hasRole: (state) => (role) => state.user?.roles?.some((item) => item.name === role) || false,
  },
  actions: {
    async login(email, password) {
      this.loading = true
      try {
        const response = await api.post('/login', { email, password })
        const { user, token } = response.data.data
        this.token = token
        this.user = user
        localStorage.setItem('token', token)
        return true
      } finally {
        this.loading = false
      }
    },
    async fetchMe() {
      if (!this.token) return

      try {
        const response = await api.get('/me')
        this.user = response.data.data
      } catch (error) {
        this.reset()
      }
    },
    async logout() {
      try {
        await api.post('/logout')
      } catch (error) {
        // ignore
      } finally {
        this.reset()
      }
    },
    reset() {
      this.token = null
      this.user = null
      localStorage.removeItem('token')
    },
  },
})
