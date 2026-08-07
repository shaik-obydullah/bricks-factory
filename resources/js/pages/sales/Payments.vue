<template>
  <div>
    <div class="flex justify-end mb-4">
      <button @click="openForm()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700">+ Record Payment</button>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-4 flex flex-wrap items-end gap-3">
      <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">From</label>
        <input v-model="filters.date_from" type="date" class="border border-gray-300 rounded-lg px-3 py-2 text-sm" />
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">To</label>
        <input v-model="filters.date_to" type="date" class="border border-gray-300 rounded-lg px-3 py-2 text-sm" />
      </div>
      <button @click="applyFilters" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700">Filter</button>
      <button v-if="filters.date_from || filters.date_to" @click="clearFilters" class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Clear</button>
    </div>

    <div v-if="loading" class="flex justify-center py-20">
      <div class="w-10 h-10 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
    </div>
    <div v-else-if="error" class="bg-red-50 text-red-700 p-4 rounded-lg">{{ error }}</div>
    <div v-else-if="!items.length" class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center text-gray-500">No payments recorded.</div>
    <div v-else class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-600">
          <tr>
            <th class="text-left px-4 py-3 font-medium">Date</th>
            <th class="text-left px-4 py-3 font-medium">Invoice</th>
            <th class="text-left px-4 py-3 font-medium">Amount</th>
            <th class="text-left px-4 py-3 font-medium">Method</th>
            <th class="text-left px-4 py-3 font-medium">Reference</th>
            <th class="text-right px-4 py-3 font-medium">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="item in items" :key="item.id" class="hover:bg-gray-50">
            <td class="px-4 py-3">{{ formatDate(item.payment_date || item.created_at) }}</td>
            <td class="px-4 py-3">#{{ item.invoice_id }}</td>
            <td class="px-4 py-3">${{ Number(item.amount || 0).toFixed(2) }}</td>
            <td class="px-4 py-3">{{ item.method ? item.method.charAt(0).toUpperCase() + item.method.slice(1) : '-' }}</td>
            <td class="px-4 py-3 text-xs">{{ item.reference || '-' }}</td>
            <td class="px-4 py-3 text-right">
              <button @click="confirmDelete(item)" class="text-red-600 hover:text-red-800 text-sm font-medium">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="formVisible" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="formVisible = false">
      <div class="bg-white rounded-xl shadow-2xl p-6 max-w-lg w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Record Payment</h3>
        <div v-if="formError" class="mb-4 bg-red-50 text-red-700 px-4 py-3 rounded-lg text-sm">{{ formError }}</div>
        <form @submit.prevent="createPayment" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Invoice</label>
            <select v-model="form.invoice_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm">
              <option value="">Select Invoice</option>
              <option v-for="inv in invoices" :key="inv.id" :value="inv.id">#{{ inv.id }} - ${{ Number(inv.total || 0).toFixed(2) }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
            <input v-model.number="form.amount" type="number" step="0.01" min="0.01" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Payment Date</label>
            <input v-model="form.payment_date" type="date" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
            <select v-model="form.payment_method" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm">
              <option value="cash">Cash</option>
              <option value="bank">Bank Transfer</option>
              <option value="nagad">Nagad</option>
              <option value="bkash">Bkash</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Reference</label>
            <input v-model="form.reference" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm" />
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
        <p class="text-gray-600 text-sm mb-4">Delete this payment? This cannot be undone.</p>
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

const items = ref([]); const invoices = ref([])
const loading = ref(true); const error = ref('')
const formVisible = ref(false); const saving = ref(false); const formError = ref('')
const deleteItem = ref(null); const deleting = ref(false)
const form = reactive({ invoice_id: '', amount: 0, payment_date: '', payment_method: 'cash', reference: '' })
const filters = reactive({ date_from: '', date_to: '' })

async function fetchData() {
  loading.value = true
  try {
    const { data } = await axios.get('/payments', { params: { date_from: filters.date_from || undefined, date_to: filters.date_to || undefined } })
    items.value = data.data || data
  }
  catch (e) { error.value = 'Failed to load payments.' }
  finally { loading.value = false }
}

function applyFilters() { fetchData() }
function clearFilters() { filters.date_from = ''; filters.date_to = ''; fetchData() }

async function openForm() {
  formVisible.value = true; formError.value = ''
  form.invoice_id = ''; form.amount = 0; form.payment_date = new Date().toISOString().substring(0, 10); form.payment_method = 'cash'; form.reference = ''
  try { const { data } = await axios.get('/invoices'); invoices.value = data.data || data } catch (e) {}
}

async function createPayment() {
  saving.value = true; formError.value = ''
  try {
    const { data } = await axios.post('/payments', form)
    items.value.unshift(data.data || data)
    formVisible.value = false
  } catch (e) { formError.value = e.response?.data?.message || 'Failed to record payment.' }
  finally { saving.value = false }
}

function confirmDelete(item) { deleteItem.value = item }
async function handleDelete() {
  deleting.value = true
  try { await axios.delete(`/payments/${deleteItem.value.id}`); items.value = items.value.filter(i => i.id !== deleteItem.value.id); deleteItem.value = null }
  catch (e) { error.value = 'Failed to delete.' }
  finally { deleting.value = false }
}

onMounted(fetchData)
</script>
