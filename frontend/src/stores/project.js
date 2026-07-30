import { defineStore } from 'pinia'
import api from '@/api'

export const useProjectStore = defineStore('project', {
  state: () => ({
    items: [],
    current: null,
    meta: null,
    loading: false,
    submitting: false,
    filters: {
      search: '',
      status: '',
      date_from: '',
      date_to: '',
      per_page: 15,
    },
  }),
  getters: {
    totalItems: (state) => state.meta?.total || 0,
    hasNextPage: (state) => !!state.meta?.next_cursor,
    hasPreviousPage: (state) => !!state.meta?.previous_cursor,
  },
  actions: {
    setFilters(payload = {}) {
      this.filters = {
        ...this.filters,
        ...payload,
      }
    },
    resetFilters() {
      this.filters = {
        search: '',
        status: '',
        date_from: '',
        date_to: '',
        per_page: 15,
      }
    },
    async fetchProjects(cursor = null) {
      this.loading = true
      try {
        const response = await api.get('/projects', {
          params: {
            ...this.filters,
            ...(cursor ? { cursor } : {}),
          },
        })

        this.items = response.data.data || []
        this.meta = response.data.meta || null
        return response.data
      } finally {
        this.loading = false
      }
    },
    async fetchProject(id) {
      this.loading = true
      try {
        const response = await api.get(`/projects/${id}`)
        this.current = response.data.data
        return response.data.data
      } finally {
        this.loading = false
      }
    },
    async createProject(payload) {
      this.submitting = true
      try {
        const response = await api.post('/projects', payload)
        return response.data.data
      } finally {
        this.submitting = false
      }
    },
    async updateProject(id, payload) {
      this.submitting = true
      try {
        const response = await api.patch(`/projects/${id}`, payload)
        return response.data.data
      } finally {
        this.submitting = false
      }
    },
    async uploadDocument(id, file) {
      const formData = new FormData()
      formData.append('file', file)

      const response = await api.post(`/projects/${id}/documents`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })

      return response.data.data
    },
    async transitionProject(id, payload) {
      const response = await api.post(`/projects/${id}/transition`, payload)
      return response.data.data
    },
    async fetchLogs(id, cursor = null) {
      const response = await api.get(`/projects/${id}/logs`, {
        params: {
          per_page: 10,
          ...(cursor ? { cursor } : {}),
        },
      })

      return response.data
    },
  },
})
