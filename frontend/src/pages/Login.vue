<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 p-4">
    <div class="absolute top-4 right-4"><LanguageSwitcher /></div>
    <form @submit.prevent="onSubmit" class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md space-y-5">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">{{ t('login.title') }}</h1>
        <p class="text-sm text-slate-500 mt-1">{{ t('login.subtitle') }}</p>
      </div>
      <div v-if="error" class="text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2">{{ error }}</div>
      <label class="block text-sm">
        <span class="text-slate-600">{{ t('login.email') }}</span>
        <input v-model="email" type="email" required class="mt-1 w-full border rounded-lg px-3 py-2" />
      </label>
      <label class="block text-sm">
        <span class="text-slate-600">{{ t('login.password') }}</span>
        <input v-model="password" type="password" required class="mt-1 w-full border rounded-lg px-3 py-2" />
      </label>
      <button
        type="submit"
        :disabled="loading"
        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 rounded-lg transition disabled:opacity-50"
      >
        {{ loading ? t('login.loading') : t('login.submit') }}
      </button>
      <p class="text-xs text-slate-400 text-center">{{ t('login.hint') }}</p>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '../composables/useAuth'
import { useI18n } from '../composables/useI18n'
import LanguageSwitcher from '../components/LanguageSwitcher.vue'

const router = useRouter()
const { login } = useAuth()
const { t } = useI18n()
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
  } catch {
    error.value = t('login.error')
  } finally {
    loading.value = false
  }
}
</script>
