import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  {
    path: '/login',
    name: 'login',
    component: () => import('@/pages/Login.vue'),
    meta: { guest: true },
  },
  {
    path: '/',
    component: () => import('@/layouts/DefaultLayout.vue'),
    meta: { auth: true },
    children: [
      {
        path: '',
        name: 'dashboard',
        component: () => import('@/pages/Dashboard.vue'),
      },
      {
        path: 'projects',
        name: 'projects.index',
        component: () => import('@/pages/projects/Index.vue'),
      },
      {
        path: 'projects/create',
        name: 'projects.create',
        component: () => import('@/pages/projects/Create.vue'),
      },
      {
        path: 'projects/:id/edit',
        name: 'projects.edit',
        component: () => import('@/pages/projects/Create.vue'),
      },
      {
        path: 'projects/:id/logs',
        name: 'projects.logs',
        component: () => import('@/pages/projects/Logs.vue'),
      },
      {
        path: 'projects/:id',
        name: 'projects.show',
        component: () => import('@/pages/projects/Show.vue'),
      },
    ],
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()

  if (authStore.isAuthenticated && !authStore.user) {
    await authStore.fetchMe()
  }

  if (to.meta.auth && !authStore.isAuthenticated) {
    return next({ name: 'login' })
  }

  if (to.meta.guest && authStore.isAuthenticated) {
    return next({ name: 'dashboard' })
  }

  next()
})

export default router
