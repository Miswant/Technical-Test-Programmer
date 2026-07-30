<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-6xl grid lg:grid-cols-2 bg-white rounded-2xl shadow-2xl overflow-hidden">
      <section class="hidden lg:flex flex-col justify-between bg-indigo-700 text-white p-10">
        <div>
          <p class="text-sm font-semibold uppercase tracking-widest text-indigo-200">Sistem Informasi</p>
          <h1 class="mt-4 text-4xl font-bold leading-tight">Persetujuan Dokumen Kelayakan</h1>
          <p class="mt-4 text-indigo-100 leading-relaxed">
            Kelola pengajuan, verifikasi administrasi, audit trail, dan keputusan persetujuan dokumen secara aman dan terstruktur.
          </p>
        </div>
        <div class="grid grid-cols-3 gap-4 text-center">
          <div class="bg-white/10 rounded-xl p-4">
            <p class="text-2xl font-bold">10K+</p>
            <p class="text-xs text-indigo-100 mt-1">Permohonan</p>
          </div>
          <div class="bg-white/10 rounded-xl p-4">
            <p class="text-2xl font-bold">2K+</p>
            <p class="text-xs text-indigo-100 mt-1">Pengguna</p>
          </div>
          <div class="bg-white/10 rounded-xl p-4">
            <p class="text-2xl font-bold">RBAC</p>
            <p class="text-xs text-indigo-100 mt-1">Akses Role</p>
          </div>
        </div>
      </section>

      <section class="p-8 sm:p-10 lg:p-12">
        <div class="max-w-md mx-auto">
          <div class="text-center lg:text-left">
            <p class="text-sm font-semibold uppercase tracking-widest text-indigo-600">Login</p>
            <h2 class="mt-2 text-3xl font-bold text-gray-900">Masuk ke akun Anda</h2>
            <p class="mt-2 text-sm text-gray-500">Gunakan akun pemohon atau penilai yang terdaftar.</p>
          </div>

          <form class="mt-8 space-y-5" @submit.prevent="handleLogin">
            <div>
              <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
              <input
                id="email"
                v-model.trim="form.email"
                type="email"
                autocomplete="email"
                :class="inputClass(errors.email)"
                placeholder="pemohon1@persetujuan.id"
                :disabled="authStore.loading"
              />
              <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
            </div>

            <div>
              <div class="flex items-center justify-between">
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <button type="button" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700" @click="showPassword = !showPassword">
                  {{ showPassword ? 'Sembunyikan' : 'Tampilkan' }}
                </button>
              </div>
              <input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="current-password"
                :class="inputClass(errors.password)"
                placeholder="password"
                :disabled="authStore.loading"
              />
              <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password }}</p>
            </div>

            <div v-if="serverError" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
              {{ serverError }}
            </div>

            <button
              type="submit"
              :disabled="authStore.loading"
              class="w-full inline-flex justify-center items-center rounded-lg bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 disabled:opacity-60 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
              <span v-if="authStore.loading" class="mr-2 h-4 w-4 rounded-full border-2 border-white border-t-transparent animate-spin"></span>
              {{ authStore.loading ? 'Memproses...' : 'Masuk' }}
            </button>
          </form>

          <div class="mt-6 rounded-lg bg-gray-50 border border-gray-200 p-4 text-sm text-gray-600">
            <p class="font-semibold text-gray-800">Akun seed default:</p>
            <p class="mt-1">Pemohon: pemohon1@persetujuan.id / password</p>
            <p>Penilai: penilai1@persetujuan.id / password</p>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const form = reactive({
  email: '',
  password: '',
})

const errors = reactive({
  email: '',
  password: '',
})

const serverError = ref('')
const showPassword = ref(false)

onMounted(() => {
  if (authStore.isAuthenticated) {
    router.replace({ name: 'dashboard' })
  }
})

const inputClass = (error) => [
  'mt-1 block w-full rounded-lg border px-3 py-3 shadow-sm focus:outline-none focus:ring-2 disabled:bg-gray-100 disabled:cursor-not-allowed',
  error ? 'border-red-300 focus:border-red-500 focus:ring-red-200' : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-200',
]

const validate = () => {
  errors.email = ''
  errors.password = ''

  if (!form.email) {
    errors.email = 'Email wajib diisi.'
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    errors.email = 'Format email tidak valid.'
  }

  if (!form.password) {
    errors.password = 'Password wajib diisi.'
  } else if (form.password.length < 6) {
    errors.password = 'Password minimal 6 karakter.'
  }

  return !errors.email && !errors.password
}

const handleLogin = async () => {
  serverError.value = ''

  if (!validate()) {
    return
  }

  try {
    await authStore.login(form.email, form.password)
    await router.replace({ name: 'dashboard' })
  } catch (error) {
    const responseErrors = error.response?.data?.errors

    if (responseErrors) {
      errors.email = responseErrors.email?.[0] || ''
      errors.password = responseErrors.password?.[0] || ''
    }

    serverError.value = error.response?.data?.message || 'Login gagal. Periksa kembali email dan password Anda.'
  }
}
</script>
