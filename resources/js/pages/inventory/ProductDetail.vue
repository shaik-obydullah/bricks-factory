<template>
  <div class="max-w-4xl mx-auto">
    <div v-if="loading" class="flex justify-center py-20">
      <div class="w-10 h-10 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
    </div>
    <div v-else-if="error" class="bg-red-50 text-red-700 p-4 rounded-lg">{{ error }}</div>
    <div v-else>
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">{{ item.name }}</h2>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
          <div><dt class="text-gray-500">SKU</dt><dd class="font-medium text-gray-800">{{ item.sku || '-' }}</dd></div>
          <div><dt class="text-gray-500">Category</dt><dd class="font-medium text-gray-800">{{ item.category?.name || '-' }}</dd></div>
          <div><dt class="text-gray-500">Price</dt><dd class="font-medium text-gray-800">${{ Number(item.price || 0).toFixed(2) }}</dd></div>
          <div><dt class="text-gray-500">Description</dt><dd class="font-medium text-gray-800">{{ item.description || '-' }}</dd></div>
        </dl>
      </div>

      <!-- Stock per Warehouse -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Stock Levels by Warehouse</h3>
        <div v-if="!stockLevels.length" class="text-gray-500 text-sm text-center py-4">No stock data.</div>
        <table v-else class="w-full text-sm">
          <thead class="bg-gray-50 text-gray-600">
            <tr>
              <th class="text-left px-4 py-3 font-medium">Warehouse</th>
              <th class="text-left px-4 py-3 font-medium">Quantity</th>
              <th class="text-left px-4 py-3 font-medium">Min Stock</th>
              <th class="text-left px-4 py-3 font-medium">Max Stock</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="sl in stockLevels" :key="sl.id" class="hover:bg-gray-50">
              <td class="px-4 py-3">{{ sl.warehouse?.name || '-' }}</td>
              <td class="px-4 py-3">{{ sl.quantity }}</td>
              <td class="px-4 py-3">{{ sl.min_stock || '-' }}</td>
              <td class="px-4 py-3">{{ sl.max_stock || '-' }}</td>
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
const stockLevels = ref([])
const loading = ref(true)
const error = ref('')

onMounted(async () => {
  try {
    const { data } = await axios.get(`/products/${route.params.id}`)
    const d = data.data || data
    item.value = d
    stockLevels.value = d.stock_levels || []
  } catch (e) { error.value = 'Failed to load product.' }
  finally { loading.value = false }
})
</script>
