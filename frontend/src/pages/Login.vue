<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900">
    <form @submit.prevent="onSubmit" class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md space-y-5">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">Product Swarm</h1>
        <p class="text-sm text-slate-500 mt-1">Sign in to manage catalog & orders</p>
      </div>
      <div v-if="error" class="text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2">{{ error }}</div>
      <label class="block text-sm">
        <span class="text-slate-600">Email</span>
        <input v-model="email" type="email" required class="mt-1 w-full border rounded-lg px-3 py-2" />
      </label>
      <label class="block text-sm">
        <span class="text-slate-600">Password</span>
        <input v-model="password" type="password" required class="mt-1 w-full border rounded-lg px-3 py-2" />
      </label>
      <button
        type="submit"
        :disabled="loading"
        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 rounded-lg transition disabled:opacity-50"
      >
        {{ loading ? 'Signing in…' : 'Sign in' }}
      </button>
      <p class="text-xs text-slate-400 text-center">admin@product.test / password</p>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '../composables/useAuth'

const router = useRouter()
const { login } = useAuth()
const email = ref('admin@product.test')
const password = ref('password')
const error = ref('')
const loading = ref(false)

const onSubmit = async () => {
  loading.value = true
  error.value = ''
  try {
    await login(email.value, password.value)
    router.push('/dashboard')
  } catch (e: unknown) {
    const err = e as { response?: { status?: number; data?: { message?: string; error?: string } }; message?: string }
    if (err.response?.status === 401) {
      error.value = 'Invalid credentials'
    } else if (err.response?.data?.message || err.response?.data?.error) {
      error.value = String(err.response.data.message || err.response.data.error)
    } else if (!err.response) {
      error.value = 'API unreachable — is docker compose up? (http://localhost:8141)'
    } else {
      error.value = `Login failed (HTTP ${err.response.status})`
    }
  } finally {
    loading.value = false
  }
}
</script>
