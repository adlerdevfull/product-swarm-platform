<template>
  <div>
    <h2 class="text-2xl font-bold text-slate-900 mb-6">{{ t('dashboard.title') }}</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
      <div class="bg-white rounded-xl shadow-sm border p-5">
        <p class="text-xs uppercase text-slate-400">{{ t('dashboard.products') }}</p>
        <p class="text-3xl font-bold text-indigo-600 mt-1">{{ products.length }}</p>
      </div>
      <div class="bg-white rounded-xl shadow-sm border p-5">
        <p class="text-xs uppercase text-slate-400">{{ t('dashboard.orders') }}</p>
        <p class="text-3xl font-bold text-emerald-600 mt-1">{{ orders.length }}</p>
      </div>
      <div class="bg-white rounded-xl shadow-sm border p-5">
        <p class="text-xs uppercase text-slate-400">{{ t('dashboard.health') }}</p>
        <p class="text-lg font-semibold mt-1" :class="health?.status === 'ok' ? 'text-emerald-600' : 'text-red-500'">
          {{ health?.status || '…' }}
          <span class="text-xs font-normal text-slate-400 block">instance: {{ health?.instance }}</span>
        </p>
      </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border p-5">
      <h3 class="font-semibold mb-3">{{ t('dashboard.stackTitle') }}</h3>
      <ul class="text-sm text-slate-600 space-y-1 list-disc list-inside">
        <li>PHP + Symfony · Hexagonal / DDD · JWT</li>
        <li>Vue 3 + TypeScript · MySQL · Redis · RabbitMQ</li>
        <li>Docker + Swarm · Prometheus · Grafana</li>
      </ul>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import api from '../services/api'
import { useI18n } from '../composables/useI18n'

const { t } = useI18n()

const products = ref<unknown[]>([])
const orders = ref<unknown[]>([])
const health = ref<{ status: string; instance: string } | null>(null)

onMounted(async () => {
  const [p, o, h] = await Promise.all([
    api.get('/api/v1/products'),
    api.get('/api/v1/orders'),
    api.get('/api/v1/health'),
  ])
  products.value = p.data.data
  orders.value = o.data.data
  health.value = h.data
})
</script>
