<template>
  <router-view v-if="$route.path === '/login'" />
  <div v-else class="flex h-screen bg-slate-50">
    <aside class="w-64 bg-slate-900 text-slate-100 flex flex-col shadow-xl">
      <div class="p-6 border-b border-slate-700">
        <h1 class="text-lg font-bold tracking-tight">⚡ Product Swarm</h1>
        <p class="text-xs text-slate-400 mt-1">Symfony · Docker Swarm · Observability</p>
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
      <div class="p-4 border-t border-slate-700">
        <button @click="logout" class="w-full text-left text-sm text-slate-400 hover:text-red-400 transition">
          Logout
        </button>
      </div>
    </aside>
    <main class="flex-1 overflow-auto p-8">
      <router-view />
    </main>
  </div>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router'
import { useAuth } from './composables/useAuth'

const router = useRouter()
const { logout: doLogout } = useAuth()

const nav = [
  { to: '/dashboard', icon: '📊', label: 'Dashboard' },
  { to: '/products', icon: '📦', label: 'Products' },
  { to: '/orders', icon: '🛒', label: 'Orders' },
  { to: '/swarm', icon: '🐝', label: 'Swarm & Ops' },
]

const logout = () => {
  doLogout()
  router.push('/login')
}
</script>
