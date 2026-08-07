<template>
  <div>
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-lg font-semibold text-gray-800">Quality Report</h2>
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
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
          <p class="text-sm text-gray-500">Total Checks</p>
          <p class="text-2xl font-bold text-gray-800 mt-1">{{ report.total_checks ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
          <p class="text-sm text-gray-500">Passed</p>
          <p class="text-2xl font-bold text-green-600 mt-1">{{ report.passed ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
          <p class="text-sm text-gray-500">Failed</p>
          <p class="text-2xl font-bold text-red-600 mt-1">{{ report.failed ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
          <p class="text-sm text-gray-500">Pending</p>
          <p class="text-2xl font-bold text-yellow-600 mt-1">{{ report.pending ?? 0 }}</p>
        </div>
      </div>

      <div v-if="!report.checks?.length" class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center text-gray-500">No quality checks in this period.</div>
      <div v-else class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 text-gray-600">
            <tr>
              <th class="text-left px-4 py-3 font-medium">Batch</th>
              <th class="text-left px-4 py-3 font-medium">Product</th>
              <th class="text-left px-4 py-3 font-medium">Inspector</th>
              <th class="text-left px-4 py-3 font-medium">Date</th>
              <th class="text-left px-4 py-3 font-medium">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="check in report.checks" :key="check.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 font-mono text-xs">{{ check.batch?.id ? 'Batch #' + check.batch.id : '-' }}</td>
              <td class="px-4 py-3 font-medium">{{ check.batch?.order?.product?.name || '-' }}</td>
              <td class="px-4 py-3">{{ check.inspector?.name || '-' }}</td>
              <td class="px-4 py-3">{{ check.check_date }}</td>
              <td class="px-4 py-3">
                <span :class="check.status === 'passed' ? 'bg-green-100 text-green-700' : check.status === 'failed' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700'" class="px-2 py-1 rounded-full text-xs font-medium">{{ check.status }}</span>
              </td>
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
    const { data } = await axios.get('/reports/quality', { params })
    report.value = data
  } catch (e) { error.value = 'Failed to load report.' }
  finally { loading.value = false }
}

onMounted(fetchData)
</script>
