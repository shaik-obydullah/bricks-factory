<template>
  <div>
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-lg font-semibold text-gray-800">Invoices</h2>
      <button @click="openForm()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700">+ Generate Invoice</button>
    </div>

    <div v-if="loading" class="flex justify-center py-20">
      <div class="w-10 h-10 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
    </div>
    <div v-else-if="error" class="bg-red-50 text-red-700 p-4 rounded-lg">{{ error }}</div>
    <div v-else-if="!items.length" class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center text-gray-500">No invoices found.</div>
    <div v-else class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-600">
          <tr>
            <th class="text-left px-4 py-3 font-medium">Invoice #</th>
            <th class="text-left px-4 py-3 font-medium">Sales Order</th>
            <th class="text-left px-4 py-3 font-medium">Date</th>
            <th class="text-left px-4 py-3 font-medium">Total</th>
            <th class="text-left px-4 py-3 font-medium">Status</th>
            <th class="text-right px-4 py-3 font-medium">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="item in items" :key="item.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 font-mono text-xs">#{{ item.id }}</td>
            <td class="px-4 py-3">#{{ item.sales_order_id }}</td>
            <td class="px-4 py-3">{{ item.invoice_date || item.created_at?.substring(0, 10) }}</td>
            <td class="px-4 py-3">${{ Number(item.total || 0).toFixed(2) }}</td>
            <td class="px-4 py-3">
              <span class="px-2 py-0.5 rounded-full text-xs font-medium" :class="statusClass(item.status)">{{ item.status }}</span>
            </td>
            <td class="px-4 py-3 text-right">
              <button @click="openForm(item)" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium mr-3">Edit</button>
              <button @click="confirmDelete(item)" class="text-red-600 hover:text-red-800 text-sm font-medium">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="formVisible" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="formVisible = false">
      <div class="bg-white rounded-xl shadow-2xl p-6 max-w-lg w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ editingItem ? 'Edit' : 'Generate' }} Invoice</h3>
        <div v-if="formError" class="mb-4 bg-red-50 text-red-700 px-4 py-3 rounded-lg text-sm">{{ formError }}</div>
        <form @submit.prevent="handleSave" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Sales Order</label>
            <select v-model="form.sales_order_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm">
              <option value="">Select Order</option>
              <option v-for="o in salesOrders" :key="o.id" :value="o.id">#{{ o.id }} - {{ o.customer?.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Invoice Date</label>
            <input v-model="form.invoice_date" type="date" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select v-model="form.status" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm">
              <option value="draft">Draft</option>
              <option value="sent">Sent</option>
              <option value="paid">Paid</option>
              <option value="overdue">Overdue</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>
          <div class="flex justify-end gap-3 pt-2">
            <button type="button" @click="formVisible = false" class="px-4 py-2.5 text-sm text-gray-700 bg-gray-100 rounded-lg">Cancel</button>
            <button type="submit" :disabled="saving" class="px-6 py-2.5 text-sm text-white bg-indigo-600 rounded-lg disabled:opacity-50">{{ saving ? 'Saving...' : 'Save' }}</button>
          </div>
        </form>
      </div>
    </div>

    <div v-if="deleteItem" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="deleteItem = null">
      <div class="bg-white rounded-xl shadow-2xl p-6 max-w-sm w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-800 mb-2">Confirm Delete</h3>
        <p class="text-gray-600 text-sm mb-4">Delete invoice #{{ deleteItem.id }}? This cannot be undone.</p>
        <div class="flex justify-end gap-3">
          <button @click="deleteItem = null" class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded-lg">Cancel</button>
          <button @click="handleDelete" :disabled="deleting" class="px-4 py-2 text-sm text-white bg-red-600 rounded-lg disabled:opacity-50">{{ deleting ? 'Deleting...' : 'Delete' }}</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import axios from '@/utils/axios'

const items = ref([]); const salesOrders = ref([])
const loading = ref(true); const error = ref('')
const formVisible = ref(false); const editingItem = ref(null); const saving = ref(false); const formError = ref('')
const deleteItem = ref(null); const deleting = ref(false)
const form = reactive({ sales_order_id: '', invoice_date: '', status: 'draft' })

function statusClass(s) {
  const map = { draft: 'bg-gray-100 text-gray-800', sent: 'bg-blue-100 text-blue-800', paid: 'bg-green-100 text-green-800', overdue: 'bg-red-100 text-red-800', cancelled: 'bg-yellow-100 text-yellow-800' }
  return map[s] || 'bg-gray-100 text-gray-800'
}

async function fetchData() {
  loading.value = true
  try { const { data } = await axios.get('/invoices'); items.value = data.data || data }
  catch (e) { error.value = 'Failed to load invoices.' }
  finally { loading.value = false }
}

async function openForm(item) {
  editingItem.value = item
  try { const { data } = await axios.get('/sales-orders'); salesOrders.value = data.data || data } catch (e) {}
  if (item) {
    form.sales_order_id = item.sales_order_id; form.invoice_date = item.invoice_date || ''; form.status = item.status
  } else {
    form.sales_order_id = ''; form.invoice_date = new Date().toISOString().substring(0, 10); form.status = 'draft'
  }
  formVisible.value = true; formError.value = ''
}

async function handleSave() {
  saving.value = true; formError.value = ''
  try {
    if (editingItem.value) { const { data } = await axios.put(`/invoices/${editingItem.value.id}`, form); Object.assign(editingItem.value, data.data || data) }
    else { const { data } = await axios.post('/invoices', form); items.value.push(data.data || data) }
    formVisible.value = false
  } catch (e) { formError.value = e.response?.data?.message || 'Failed to save.' }
  finally { saving.value = false }
}

function confirmDelete(item) { deleteItem.value = item }
async function handleDelete() {
  deleting.value = true
  try { await axios.delete(`/invoices/${deleteItem.value.id}`); items.value = items.value.filter(i => i.id !== deleteItem.value.id); deleteItem.value = null }
  catch (e) { error.value = 'Failed to delete.' }
  finally { deleting.value = false }
}

onMounted(fetchData)
</script>
