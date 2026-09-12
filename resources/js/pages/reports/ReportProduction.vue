<template>
  <div>
    <div class="flex justify-end mb-4">
      <div class="flex items-center gap-2">
        <input v-model="filters.from" type="date" class="border border-gray-300 rounded-lg px-3 py-2 text-sm" />
        <input v-model="filters.to" type="date" class="border border-gray-300 rounded-lg px-3 py-2 text-sm" />
        <button @click="fetchData" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700">Filter</button>
      </div>
    </div>

    <div v-if="loading" class="flex justify-center py-20">
      <div class="w-10 h-10 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
    </div>
    <div v-else-if="error" class="bg-red-50 text-red-700 p-4 rounded-lg">{{ error }}</div>
    <template v-else>
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
          <p class="text-sm text-gray-500">Total Produced</p>
          <p class="text-2xl font-bold text-indigo-600 mt-1">{{ report.total_produced ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
          <p class="text-sm text-gray-500">Total Rejected</p>
          <p class="text-2xl font-bold text-red-600 mt-1">{{ report.total_rejected ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
          <p class="text-sm text-gray-500">Batches</p>
          <p class="text-2xl font-bold text-gray-800 mt-1">{{ report.batches_count ?? 0 }}</p>
        </div>
      </div>

      <div v-if="!report.batches?.length" class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center text-gray-500">No batches in this period.</div>
      <div v-else class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 text-gray-600">
            <tr>
              <th class="text-left px-4 py-3 font-medium">Order</th>
              <th class="text-left px-4 py-3 font-medium">Product</th>
              <th class="text-left px-4 py-3 font-medium">Shift</th>
              <th class="text-left px-4 py-3 font-medium">Machine</th>
              <th class="text-right px-4 py-3 font-medium">Produced</th>
              <th class="text-right px-4 py-3 font-medium">Rejected</th>
              <th class="text-left px-4 py-3 font-medium">Status</th>
              <th class="text-left px-4 py-3 font-medium">Date</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="batch in report.batches" :key="batch.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 font-mono text-xs">{{ batch.order?.order_number || '-' }}</td>
              <td class="px-4 py-3 font-medium">{{ batch.order?.product?.name || '-' }}</td>
              <td class="px-4 py-3">{{ batch.shift?.name || '-' }}</td>
              <td class="px-4 py-3">{{ batch.machine?.name || '-' }}</td>
              <td class="px-4 py-3 text-right">{{ batch.quantity_produced }}</td>
              <td class="px-4 py-3 text-right text-red-600">{{ batch.quantity_rejected }}</td>
              <td class="px-4 py-3">
                <span :class="batch.status === 'completed' ? 'bg-green-100 text-green-700' : batch.status === 'running' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600'" class="px-2 py-1 rounded-full text-xs font-medium">{{ humanize(batch.status) }}</span>
              </td>
              <td class="px-4 py-3 text-gray-500">{{ formatDate(batch.created_at) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import axios from '@/utils/axios'

const report = ref({})
const loading = ref(true)
const error = ref('')
const filters = reactive({ from: '', to: '' })

async function fetchData() {
  loading.value = true
  try {
    const params = {}
    if (filters.from) params.from = filters.from
    if (filters.to) params.to = filters.to
    const { data } = await axios.get('/reports/production', { params })
    report.value = data
  } catch (e) { error.value = 'Failed to load report.' }
  finally { loading.value = false }
}

onMounted(fetchData)
</script>
