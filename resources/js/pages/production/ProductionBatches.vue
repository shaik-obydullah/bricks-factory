<template>
  <div>
    <div class="flex justify-end mb-4">
      <button @click="showForm = true" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">+ New Batch</button>
    </div>

    <div v-if="loading" class="flex justify-center py-20">
      <div class="w-10 h-10 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
    </div>
    <div v-else-if="error" class="bg-red-50 text-red-700 p-4 rounded-lg">{{ error }}</div>
    <div v-else-if="!items.length" class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center text-gray-500">No batches found.</div>
    <div v-else class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-600">
          <tr>
            <th class="text-left px-4 py-3 font-medium">ID</th>
            <th class="text-left px-4 py-3 font-medium">Order</th>
            <th class="text-left px-4 py-3 font-medium">Machine</th>
            <th class="text-left px-4 py-3 font-medium">Shift</th>
            <th class="text-left px-4 py-3 font-medium">Quantity</th>
            <th class="text-left px-4 py-3 font-medium">Status</th>
            <th class="text-right px-4 py-3 font-medium">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="item in items" :key="item.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 font-mono text-xs">#{{ item.id }}</td>
            <td class="px-4 py-3">
              <router-link :to="`/production/orders/${item.order_id}`" class="text-indigo-600 hover:text-indigo-800">#{{ item.order_id }}</router-link>
            </td>
            <td class="px-4 py-3">{{ item.machine?.name || '-' }}</td>
            <td class="px-4 py-3">{{ item.shift?.name || '-' }}</td>
            <td class="px-4 py-3">{{ item.quantity_produced }}</td>
            <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium" :class="statusClass(item.status)">{{ humanize(item.status) }}</span></td>
            <td class="px-4 py-3 text-right">
              <button v-if="item.status === 'running'" @click="completeBatch(item)" class="text-green-600 hover:text-green-800 text-sm font-medium">Complete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Create Modal -->
    <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showForm = false">
      <div class="bg-white rounded-xl shadow-2xl p-6 max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">New Batch</h3>
        <div v-if="formError" class="mb-4 bg-red-50 text-red-700 px-4 py-3 rounded-lg text-sm">{{ formError }}</div>
        <form @submit.prevent="createBatch" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Production Order</label>
            <select v-model="form.production_order_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
              <option value="">Select Order</option>
              <option v-for="o in orders" :key="o.id" :value="o.id">#{{ o.id }} - {{ o.product?.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Machine</label>
            <select v-model="form.machine_id" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
              <option value="">Select Machine</option>
              <option v-for="m in machines" :key="m.id" :value="m.id">{{ m.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Shift</label>
            <select v-model="form.shift" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
              <option value="morning">Morning</option>
              <option value="evening">Evening</option>
              <option value="night">Night</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
            <input v-model.number="form.quantity" type="number" min="1" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
          </div>
          <div class="flex justify-end gap-3 pt-2">
            <button type="button" @click="showForm = false" class="px-4 py-2.5 text-sm text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Cancel</button>
            <button type="submit" :disabled="saving" class="px-6 py-2.5 text-sm text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 disabled:opacity-50">
              {{ saving ? 'Creating...' : 'Create' }}
            </button>
          </div>
        </form>
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
const showForm = ref(false)
const saving = ref(false)
const formError = ref('')
const orders = ref([])
const machines = ref([])
const form = reactive({ production_order_id: '', machine_id: '', shift: 'morning', quantity: 1 })

function statusClass(s) {
  const map = { pending: 'bg-yellow-100 text-yellow-800', running: 'bg-blue-100 text-blue-800', completed: 'bg-green-100 text-green-800', cancelled: 'bg-red-100 text-red-800' }
  return map[s] || 'bg-gray-100 text-gray-800'
}

async function fetchData() {
  loading.value = true
  error.value = ''
  try {
    const { data } = await axios.get('/production-batches')
    items.value = data.data || data
  } catch (e) {
    error.value = 'Failed to load batches.'
  } finally {
    loading.value = false
  }
}

async function loadFormData() {
  try {
    const [oRes, mRes] = await Promise.all([axios.get('/production-orders'), axios.get('/machines')])
    orders.value = oRes.data.data || oRes.data
    machines.value = mRes.data.data || mRes.data
  } catch (e) {
    formError.value = 'Failed to load form data.'
  }
}

async function createBatch() {
  saving.value = true
  formError.value = ''
  try {
    const { data } = await axios.post('/production-batches', form)
    items.value.unshift(data.data || data)
    showForm.value = false
    form.production_order_id = ''; form.machine_id = ''; form.quantity = 1
  } catch (e) {
    formError.value = e.response?.data?.message || 'Failed to create batch.'
  } finally {
    saving.value = false
  }
}

async function completeBatch(item) {
  try {
    await axios.patch(`/production-batches/${item.id}/complete`)
    item.status = 'completed'
  } catch (e) {
    error.value = 'Failed to complete batch.'
  }
}

onMounted(() => { fetchData(); loadFormData() })
</script>
