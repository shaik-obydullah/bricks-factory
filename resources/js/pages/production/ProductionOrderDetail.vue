<template>
  <div class="max-w-4xl mx-auto">
    <div v-if="loading" class="flex justify-center py-20">
      <div class="w-10 h-10 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
    </div>
    <div v-else-if="error" class="bg-red-50 text-red-700 p-4 rounded-lg">{{ error }}</div>
    <div v-else>
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-xl font-semibold text-gray-800">Order #{{ item.id }}</h2>
          <span class="px-3 py-1 rounded-full text-sm font-medium" :class="statusClass(item.status)">{{ humanize(item.status) }}</span>
        </div>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
          <div><dt class="text-gray-500">Product</dt><dd class="font-medium text-gray-800">{{ item.product?.name || '-' }}</dd></div>
          <div><dt class="text-gray-500">Quantity</dt><dd class="font-medium text-gray-800">{{ item.quantity }}</dd></div>
          <div><dt class="text-gray-500">Planned Date</dt><dd class="font-medium text-gray-800">{{ formatDate(item.planned_date) }}</dd></div>
          <div><dt class="text-gray-500">Priority</dt><dd class="font-medium text-gray-800">{{ humanize(item.priority) }}</dd></div>
          <div class="sm:col-span-2"><dt class="text-gray-500">Notes</dt><dd class="font-medium text-gray-800">{{ item.notes || 'None' }}</dd></div>
        </dl>
      </div>

      <!-- Batches -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Batches</h3>
        <div v-if="!item.batches?.length" class="text-gray-500 text-sm text-center py-4">No batches for this order.</div>
        <table v-else class="w-full text-sm">
          <thead class="bg-gray-50 text-gray-600">
            <tr>
              <th class="text-left px-4 py-3 font-medium">ID</th>
              <th class="text-left px-4 py-3 font-medium">Machine</th>
              <th class="text-left px-4 py-3 font-medium">Shift</th>
              <th class="text-left px-4 py-3 font-medium">Quantity</th>
              <th class="text-left px-4 py-3 font-medium">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="b in item.batches" :key="b.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 font-mono text-xs">#{{ b.id }}</td>
              <td class="px-4 py-3">{{ b.machine?.name || '-' }}</td>
              <td class="px-4 py-3">{{ b.shift?.name || '-' }}</td>
              <td class="px-4 py-3">{{ b.quantity_produced }}</td>
              <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium" :class="statusClass(b.status)">{{ humanize(b.status) }}</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import axios from '@/utils/axios'

const route = useRoute()
const item = ref({})
const loading = ref(true)
const error = ref('')

function statusClass(s) {
  const map = { pending: 'bg-yellow-100 text-yellow-800', in_progress: 'bg-blue-100 text-blue-800', completed: 'bg-green-100 text-green-800', cancelled: 'bg-red-100 text-red-800' }
  return map[s] || 'bg-gray-100 text-gray-800'
}

onMounted(async () => {
  try {
    const { data } = await axios.get(`/production-orders/${route.params.id}`)
    item.value = data.data || data
  } catch (e) {
    error.value = 'Failed to load order.'
  } finally {
    loading.value = false
  }
})
</script>
