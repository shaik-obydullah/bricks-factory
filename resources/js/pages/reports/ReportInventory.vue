<template>
  <div>
    <div class="flex justify-end mb-4">
      <button @click="fetchData" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700">Refresh</button>
    </div>

    <div v-if="loading" class="flex justify-center py-20">
      <div class="w-10 h-10 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
    </div>
    <div v-else-if="error" class="bg-red-50 text-red-700 p-4 rounded-lg">{{ error }}</div>
    <template v-else>
      <div v-if="report.low_stock_alerts?.length" class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4">
        <p class="text-sm font-semibold text-red-700 mb-2">{{ report.low_stock_alerts.length }} low stock alert(s)</p>
        <p class="text-sm text-red-600">{{ report.low_stock_alerts.join(', ') }}</p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
          <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
            <h3 class="font-semibold text-gray-800 text-sm">Products</h3>
          </div>
          <table class="w-full text-sm">
            <thead class="text-gray-500">
              <tr>
                <th class="text-left px-4 py-2 font-medium">Name</th>
                <th class="text-left px-4 py-2 font-medium">Code</th>
                <th class="text-right px-4 py-2 font-medium">Total Stock</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="product in report.products" :key="product.id" class="hover:bg-gray-50">
                <td class="px-4 py-2.5 font-medium">{{ product.name }}</td>
                <td class="px-4 py-2.5 font-mono text-xs">{{ product.code }}</td>
                <td class="px-4 py-2.5 text-right">{{ productTotal(product) }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
          <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
            <h3 class="font-semibold text-gray-800 text-sm">Raw Materials</h3>
          </div>
          <table class="w-full text-sm">
            <thead class="text-gray-500">
              <tr>
                <th class="text-left px-4 py-2 font-medium">Name</th>
                <th class="text-left px-4 py-2 font-medium">Unit</th>
                <th class="text-right px-4 py-2 font-medium">Stock</th>
                <th class="text-right px-4 py-2 font-medium">Min</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="material in report.raw_materials" :key="material.id" class="hover:bg-gray-50">
                <td class="px-4 py-2.5 font-medium">{{ material.name }}</td>
                <td class="px-4 py-2.5">{{ material.unit }}</td>
                <td class="px-4 py-2.5 text-right" :class="material.current_stock <= material.minimum_stock ? 'text-red-600 font-semibold' : ''">{{ material.current_stock }}</td>
                <td class="px-4 py-2.5 text-right text-gray-500">{{ material.minimum_stock }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from '@/utils/axios'

const report = ref({})
const loading = ref(true)
const error = ref('')

function productTotal(product) {
  return (product.product_stocks || []).reduce((sum, s) => sum + Number(s.quantity || 0), 0)
}

async function fetchData() {
  loading.value = true
  try {
    const { data } = await axios.get('/reports/inventory')
    report.value = data
  } catch (e) { error.value = 'Failed to load report.' }
  finally { loading.value = false }
}

onMounted(fetchData)
</script>
