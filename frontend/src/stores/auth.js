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
    hasRole: (state) => (role) => state.user?.roles?.some(r => r.name === role) || false,
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
      } catch (error) {
        throw error
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
        this.logout()
      }
    },
    async logout() {
      try {
        await api.post('/logout')
      } catch (e) {
        // ignore
      } finally {
        this.token = null
        this.user = null
        localStorage.removeItem('token')
      }
    },
  },
})
