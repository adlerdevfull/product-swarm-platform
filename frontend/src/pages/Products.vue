<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-2xl font-bold text-slate-900">Products</h2>
      <button @click="showForm = !showForm" class="bg-indigo-600 text-white text-sm px-4 py-2 rounded-lg">
        {{ showForm ? 'Cancel' : '+ New product' }}
      </button>
    </div>

    <form v-if="showForm" @submit.prevent="create" class="bg-white border rounded-xl p-5 mb-6 grid grid-cols-2 gap-3">
      <input v-model="form.sku" placeholder="SKU" required class="border rounded-lg px-3 py-2 text-sm" />
      <input v-model="form.name" placeholder="Name" required class="border rounded-lg px-3 py-2 text-sm" />
      <input v-model.number="form.price_cents" type="number" placeholder="Price (cents)" class="border rounded-lg px-3 py-2 text-sm" />
      <input v-model.number="form.stock" type="number" placeholder="Stock" class="border rounded-lg px-3 py-2 text-sm" />
      <textarea v-model="form.description" placeholder="Description" class="border rounded-lg px-3 py-2 text-sm col-span-2" />
      <button type="submit" class="col-span-2 bg-emerald-600 text-white py-2 rounded-lg text-sm">Create</button>
    </form>

    <div class="bg-white border rounded-xl overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-slate-500">
          <tr>
            <th class="px-4 py-3">SKU</th>
            <th class="px-4 py-3">Name</th>
            <th class="px-4 py-3">Price</th>
            <th class="px-4 py-3">Stock</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="p in products" :key="p.id" class="border-t">
            <td class="px-4 py-3 font-mono text-xs">{{ p.sku }}</td>
            <td class="px-4 py-3">{{ p.name }}</td>
            <td class="px-4 py-3">€{{ p.price_euros.toFixed(2) }}</td>
            <td class="px-4 py-3">{{ p.stock }}</td>
            <td class="px-4 py-3">
              <span class="px-2 py-0.5 rounded-full text-xs" :class="statusClass(p.status)">{{ p.status }}</span>
            </td>
            <td class="px-4 py-3 text-right">
              <button
                v-if="p.status === 'draft'"
                @click="publish(p.id)"
                class="text-indigo-600 hover:underline text-xs"
              >
                Publish
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import api from '../services/api'

interface Product {
  id: number
  sku: string
  name: string
  price_euros: number
  stock: number
  status: string
}

const products = ref<Product[]>([])
const showForm = ref(false)
const form = reactive({
  sku: '',
  name: '',
  description: '',
  price_cents: 1000,
  stock: 10,
})

const load = async () => {
  const { data } = await api.get('/api/v1/products')
  products.value = data.data
}

const create = async () => {
  await api.post('/api/v1/products', form)
  showForm.value = false
  Object.assign(form, { sku: '', name: '', description: '', price_cents: 1000, stock: 10 })
  await load()
}

const publish = async (id: number) => {
  await api.post(`/api/v1/products/${id}/publish`)
  await load()
}

const statusClass = (s: string) =>
  ({
    draft: 'bg-slate-100 text-slate-600',
    published: 'bg-emerald-100 text-emerald-700',
    archived: 'bg-amber-100 text-amber-700',
  }[s] || 'bg-slate-100')

onMounted(load)
</script>
