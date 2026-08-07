<template>
  <div>
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center gap-3">
        <select v-model="filters.status" class="text-sm border border-gray-300 rounded-lg px-3 py-2 bg-white" @change="fetchData">
          <option value="">All Statuses</option>
          <option value="pending">Pending</option>
          <option value="in_progress">In Progress</option>
          <option value="completed">Completed</option>
          <option value="cancelled">Cancelled</option>
        </select>
        <input v-model="filters.date_from" type="date" class="text-sm border border-gray-300 rounded-lg px-3 py-2 bg-white" @change="fetchData" />
        <input v-model="filters.date_to" type="date" class="text-sm border border-gray-300 rounded-lg px-3 py-2 bg-white" @change="fetchData" />
      </div>
      <router-link to="/production/orders/create" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">+ New Order</router-link>
    </div>

    <div v-if="loading" class="flex justify-center py-20">
      <div class="w-10 h-10 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
    </div>
    <div v-else-if="error" class="bg-red-50 text-red-700 p-4 rounded-lg">{{ error }}</div>
    <div v-else-if="!items.length" class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center text-gray-500">No production orders found.</div>
    <div v-else class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-600">
          <tr>
            <th class="text-left px-4 py-3 font-medium">ID</th>
            <th class="text-left px-4 py-3 font-medium">Product</th>
            <th class="text-left px-4 py-3 font-medium">Quantity</th>
            <th class="text-left px-4 py-3 font-medium">Planned Date</th>
            <th class="text-left px-4 py-3 font-medium">Priority</th>
            <th class="text-left px-4 py-3 font-medium">Status</th>
            <th class="text-right px-4 py-3 font-medium">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="item in items" :key="item.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 font-mono text-xs">#{{ item.id }}</td>
            <td class="px-4 py-3">{{ item.product?.name || '-' }}</td>
            <td class="px-4 py-3">{{ item.quantity }}</td>
            <td class="px-4 py-3">{{ item.planned_date }}</td>
            <td class="px-4 py-3">
              <span class="px-2 py-0.5 rounded-full text-xs font-medium" :class="priorityClass(item.priority)">{{ item.priority }}</span>
            </td>
            <td class="px-4 py-3">
              <span class="px-2 py-0.5 rounded-full text-xs font-medium" :class="statusClass(item.status)">{{ item.status }}</span>
            </td>
            <td class="px-4 py-3 text-right">
              <router-link :to="`/production/orders/${item.id}`" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium mr-3">View</router-link>
              <button @click="confirmDelete(item)" class="text-red-600 hover:text-red-800 text-sm font-medium">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Delete Modal -->
    <div v-if="deleteItem" class="fixed inset-0 z-50 flex items-center justify-center" @click.self="deleteItem = null">
      <div class="bg-white rounded-xl shadow-2xl p-6 max-w-sm w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-800 mb-2">Confirm Delete</h3>
        <p class="text-gray-600 text-sm mb-4">Are you sure you want to delete order #{{ deleteItem.id }}? This action cannot be undone.</p>
        <div class="flex justify-end gap-3">
          <button @click="deleteItem = null" class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">Cancel</button>
          <button @click="handleDelete" :disabled="deleting" class="px-4 py-2 text-sm text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50">
            {{ deleting ? 'Deleting...' : 'Delete' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import axios from '@/utils/axios'

const items = ref([])
const loading = ref(true)
const error = ref('')
const deleteItem = ref(null)
const deleting = ref(false)
const filters = reactive({ status: '', date_from: '', date_to: '' })

function statusClass(s) {
  const map = { pending: 'bg-yellow-100 text-yellow-800', in_progress: 'bg-blue-100 text-blue-800', completed: 'bg-green-100 text-green-800', cancelled: 'bg-red-100 text-red-800' }
  return map[s] || 'bg-gray-100 text-gray-800'
}
function priorityClass(p) {
  const map = { low: 'bg-gray-100 text-gray-700', medium: 'bg-blue-100 text-blue-700', high: 'bg-orange-100 text-orange-700', urgent: 'bg-red-100 text-red-700' }
  return map[p] || 'bg-gray-100 text-gray-700'
}

async function fetchData() {
  loading.value = true
  error.value = ''
  try {
    const params = {}
    if (filters.status) params.status = filters.status
    if (filters.date_from) params.date_from = filters.date_from
    if (filters.date_to) params.date_to = filters.date_to
    const { data } = await axios.get('/production-orders', { params })
    items.value = data.data || data
  } catch (e) {
    error.value = 'Failed to load production orders.'
  } finally {
    loading.value = false
  }
}

function confirmDelete(item) { deleteItem.value = item }

async function handleDelete() {
  deleting.value = true
  try {
    await axios.delete(`/production-orders/${deleteItem.value.id}`)
    items.value = items.value.filter(i => i.id !== deleteItem.value.id)
    deleteItem.value = null
  } catch (e) {
    error.value = 'Failed to delete.'
  } finally {
    deleting.value = false
  }
}

onMounted(fetchData)
</script>
