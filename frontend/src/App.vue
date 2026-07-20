<template>
  <router-view v-if="$route.path === '/login'" />
  <div v-else class="flex h-screen bg-slate-50">
    <aside class="w-64 bg-slate-900 text-slate-100 flex flex-col shadow-xl">
      <div class="p-6 border-b border-slate-700">
        <h1 class="text-lg font-bold tracking-tight">⚡ {{ t('appName') }}</h1>
        <p class="text-xs text-slate-400 mt-1">{{ t('appTagline') }}</p>
      </div>
      <nav class="flex-1 p-4 space-y-1">
        <router-link
          v-for="item in nav"
          :key="item.to"
          :to="item.to"
          class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-slate-300 hover:bg-slate-800 hover:text-white transition"
          active-class="!bg-indigo-600 !text-white font-medium"
        >
          <span>{{ item.icon }}</span>{{ item.label }}
        </router-link>
      </nav>
      <div class="p-4 border-t border-slate-700 space-y-3">
        <LanguageSwitcher />
        <button @click="logout" class="w-full text-left text-sm text-slate-400 hover:text-red-400 transition">
          {{ t('nav.logout') }}
        </button>
      </div>
    </aside>
    <main class="flex-1 overflow-auto p-8">
      <router-view />
    </main>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from './composables/useAuth'
import { useI18n } from './composables/useI18n'
import LanguageSwitcher from './components/LanguageSwitcher.vue'

const router = useRouter()
const { logout: doLogout } = useAuth()
const { t, locale } = useI18n()

const nav = computed(() => {
  void locale.value
  return [
    { to: '/dashboard', icon: '📊', label: t('nav.dashboard') },
    { to: '/products', icon: '📦', label: t('nav.products') },
    { to: '/orders', icon: '🛒', label: t('nav.orders') },
    { to: '/swarm', icon: '🐝', label: t('nav.swarm') },
  ]
})

const logout = () => {
  doLogout()
  router.push('/login')
}
</script>
