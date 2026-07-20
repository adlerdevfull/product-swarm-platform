<template>
  <div class="space-y-6">
    <h2 class="text-2xl font-bold text-slate-900">{{ t('swarm.title') }}</h2>

    <div class="grid md:grid-cols-2 gap-4">
      <div class="bg-white border rounded-xl p-5">
        <h3 class="font-semibold mb-2">{{ t('swarm.health') }}</h3>
        <pre class="text-xs bg-slate-900 text-emerald-400 p-3 rounded-lg overflow-auto">{{ healthJson }}</pre>
        <button @click="refreshHealth" class="mt-3 text-sm text-indigo-600 hover:underline">{{ t('swarm.refresh') }}</button>
      </div>
      <div class="bg-white border rounded-xl p-5">
        <h3 class="font-semibold mb-2">{{ t('swarm.links') }}</h3>
        <ul class="text-sm space-y-2 text-slate-600">
          <li><a class="text-indigo-600 hover:underline" href="http://localhost:9090" target="_blank">Prometheus :9090</a></li>
          <li><a class="text-indigo-600 hover:underline" href="http://localhost:3000" target="_blank">Grafana :3000</a> (admin/admin)</li>
          <li><a class="text-indigo-600 hover:underline" href="http://localhost:15675" target="_blank">RabbitMQ :15675</a> (guest/guest)</li>
          <li><a class="text-indigo-600 hover:underline" href="http://localhost:8141/metrics" target="_blank">/metrics scrape</a></li>
        </ul>
      </div>
    </div>

    <div class="bg-white border rounded-xl p-5">
      <h3 class="font-semibold mb-3">{{ t('swarm.commands') }}</h3>
      <pre class="text-xs bg-slate-100 p-4 rounded-lg overflow-auto text-slate-800"># Init swarm (once)
docker swarm init

# Build image & deploy stack
docker build -t product-platform:latest -f docker/Dockerfile --target production .
docker stack deploy -c docker-stack.yml product

# Scale app replicas
docker service scale product_app=5

# Rolling update
docker service update --image product-platform:v2 product_app

# Status
docker stack services product
docker service ps product_app</pre>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import api from '../services/api'
import { useI18n } from '../composables/useI18n'

const { t } = useI18n()

const healthJson = ref('Loading…')

const refreshHealth = async () => {
  const { data } = await api.get('/api/v1/health')
  healthJson.value = JSON.stringify(data, null, 2)
}

onMounted(refreshHealth)
</script>
