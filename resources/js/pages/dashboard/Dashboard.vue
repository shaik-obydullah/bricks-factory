<template>
  <div>
    <div v-if="loading" class="flex justify-center py-20">
      <div class="w-10 h-10 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
    </div>
    <div v-else-if="error" class="bg-red-50 text-red-700 p-4 rounded-lg">{{ error }}</div>
    <div v-else>
      <!-- Summary Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Total Orders</p>
              <p class="text-2xl font-bold text-gray-800 mt-1">{{ stats.total_orders || 0 }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Products</p>
              <p class="text-2xl font-bold text-gray-800 mt-1">{{ stats.total_products || 0 }}</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center text-green-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Active Batches</p>
              <p class="text-2xl font-bold text-gray-800 mt-1">{{ stats.active_batches || 0 }}</p>
            </div>
            <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center text-amber-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" /></svg>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Low Stock Items</p>
              <p class="text-2xl font-bold text-gray-800 mt-1">{{ stats.low_stock_items || 0 }}</p>
            </div>
            <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center text-red-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" /></svg>
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Production Summary -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Production Summary</h3>
          <div class="space-y-3">
            <div class="flex justify-between items-center">
              <span class="text-gray-600">Pending Orders</span>
              <span class="font-semibold">{{ stats.pending_orders || 0 }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-gray-600">In Progress</span>
              <span class="font-semibold">{{ stats.in_progress_orders || 0 }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-gray-600">Completed Today</span>
              <span class="font-semibold">{{ stats.completed_today || 0 }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-gray-600">Machines Active</span>
              <span class="font-semibold">{{ stats.active_machines || 0 }} / {{ stats.total_machines || 0 }}</span>
            </div>
          </div>
        </div>

        <!-- Inventory Summary -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Inventory Summary</h3>
          <div class="space-y-3">
            <div class="flex justify-between items-center">
              <span class="text-gray-600">Total Products</span>
              <span class="font-semibold">{{ stats.total_products || 0 }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-gray-600">Total Raw Materials</span>
              <span class="font-semibold">{{ stats.total_raw_materials || 0 }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-gray-600">Stock Movements Today</span>
              <span class="font-semibold">{{ stats.stock_movements_today || 0 }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-gray-600">Warehouses</span>
              <span class="font-semibold">{{ stats.total_warehouses || 0 }}</span>
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Activity -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Activity</h3>
          <div v-if="!recentActivity.length" class="text-gray-500 text-sm py-4 text-center">No recent activity.</div>
          <div v-else class="space-y-3">
            <div v-for="item in recentActivity" :key="item.id" class="flex items-start gap-3 pb-3 border-b border-gray-100 last:border-0">
              <div class="w-2 h-2 mt-2 rounded-full" :class="activityColor(item.type)"></div>
              <div>
                <p class="text-sm text-gray-700">{{ item.description }}</p>
                <p class="text-xs text-gray-400">{{ formatDate(item.created_at) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Monthly Production Chart -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Monthly Production</h3>
          <div v-if="!monthlyProduction.length" class="h-48 flex items-center justify-center bg-gray-50 rounded-lg">
            <p class="text-gray-400">No production data available.</p>
          </div>
          <div v-else class="h-48 flex items-end justify-between gap-2 px-2">
            <div v-for="item in monthlyProduction" :key="item.month" class="flex-1 flex flex-col items-center gap-1 h-full justify-end">
              <span class="text-[10px] text-gray-500 font-medium">{{ formatQty(item.total) }}</span>
              <div class="w-full rounded-t-lg bg-indigo-500 hover:bg-indigo-600 transition-colors"
                   :style="{ height: barHeight(item.total) }"
                   :title="item.label + ': ' + item.total.toLocaleString()"></div>
              <span class="text-xs text-gray-500 whitespace-nowrap">{{ item.label }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from '@/utils/axios'

const loading = ref(true)
const error = ref('')
const stats = ref({})
const recentActivity = ref([])
const monthlyProduction = ref([])

function activityColor(type) {
  const colors = { production: 'bg-blue-500', inventory: 'bg-green-500', quality: 'bg-amber-500', sales: 'bg-purple-500' }
  return colors[type] || 'bg-gray-500'
}

function barHeight(total) {
  const max = Math.max(...monthlyProduction.value.map(m => m.total), 1)
  const pct = max > 0 ? (total / max) * 100 : 0
  return Math.max(pct, 4) + '%'
}

function formatQty(total) {
  if (total >= 1000) return (total / 1000).toFixed(1) + 'k'
  return String(total)
}

onMounted(async () => {
  try {
    const { data } = await axios.get('/reports/dashboard')
    stats.value = data.data || data
    recentActivity.value = data.recent_activity || []
    monthlyProduction.value = data.monthly_production || []
  } catch (e) {
    error.value = 'Failed to load dashboard data.'
  } finally {
    loading.value = false
  }
})
</script>
