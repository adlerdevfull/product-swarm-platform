<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-2xl font-bold text-slate-900">Orders</h2>
    </div>

    <form @submit.prevent="place" class="bg-white border rounded-xl p-5 mb-6 flex flex-wrap gap-3 items-end">
      <label class="text-sm">
        <span class="text-slate-500">Product</span>
        <select v-model.number="productId" class="block mt-1 border rounded-lg px-3 py-2 min-w-[220px]">
          <option v-for="p in published" :key="p.id" :value="p.id">
            {{ p.sku }} — {{ p.name }} (stock {{ p.stock }})
          </option>
        </select>
      </label>
      <label class="text-sm">
        <span class="text-slate-500">Qty</span>
        <input v-model.number="quantity" type="number" min="1" class="block mt-1 border rounded-lg px-3 py-2 w-24" />
      </label>
      <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm">Place order</button>
      <p v-if="error" class="text-red-600 text-sm w-full">{{ error }}</p>
    </form>

    <div class="bg-white border rounded-xl overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-slate-500">
          <tr>
            <th class="px-4 py-3">Number</th>
            <th class="px-4 py-3">SKU</th>
            <th class="px-4 py-3">Qty</th>
            <th class="px-4 py-3">Total</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="o in orders" :key="o.id" class="border-t">
            <td class="px-4 py-3 font-mono text-xs">{{ o.order_number }}</td>
            <td class="px-4 py-3">{{ o.product_sku }}</td>
            <td class="px-4 py-3">{{ o.quantity }}</td>
            <td class="px-4 py-3">€{{ o.total_euros.toFixed(2) }}</td>
            <td class="px-4 py-3">
              <span class="px-2 py-0.5 rounded-full text-xs bg-slate-100">{{ o.status }}</span>
            </td>
            <td class="px-4 py-3 space-x-2">
              <button
                v-for="s in nextStatuses(o.status)"
                :key="s"
                @click="transition(o.id, s)"
                class="text-xs text-indigo-600 hover:underline"
              >
                → {{ s }}
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import api from '../services/api'

interface Product {
  id: number
  sku: string
  name: string
  stock: number
  status: string
}
interface Order {
  id: number
  order_number: string
  product_sku: string
  quantity: number
  total_euros: number
  status: string
}

const products = ref<Product[]>([])
const orders = ref<Order[]>([])
const productId = ref(0)
const quantity = ref(1)
const error = ref('')

const published = computed(() => products.value.filter((p) => p.status === 'published'))

const nextStatuses = (status: string): string[] => {
  const map: Record<string, string[]> = {
    pending: ['confirmed', 'cancelled'],
    confirmed: ['shipped', 'cancelled'],
    shipped: ['delivered'],
  }
  return map[status] || []
}

const load = async () => {
  const [p, o] = await Promise.all([api.get('/api/v1/products'), api.get('/api/v1/orders')])
  products.value = p.data.data
  orders.value = o.data.data
  if (!productId.value && published.value[0]) productId.value = published.value[0].id
}

const place = async () => {
  error.value = ''
  try {
    await api.post('/api/v1/orders', { product_id: productId.value, quantity: quantity.value })
    await load()
  } catch (e: unknown) {
    const err = e as { response?: { data?: { error?: string } } }
    error.value = err.response?.data?.error || 'Failed to place order'
  }
}

const transition = async (id: number, status: string) => {
  await api.post(`/api/v1/orders/${id}/transition`, { status })
  await load()
}

onMounted(load)
</script>
