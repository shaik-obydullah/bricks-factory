<template>
  <div>
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center gap-2 flex-wrap">
        <input v-model="filters.search" @input="fetchData" placeholder="Search invoice # or customer..." class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-64 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
        <select v-model="filters.status" @change="fetchData" class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
          <option value="">All Statuses</option>
          <option value="draft">Draft</option>
          <option value="sent">Sent</option>
          <option value="paid">Paid</option>
          <option value="partial">Partial</option>
          <option value="overdue">Overdue</option>
          <option value="cancelled">Cancelled</option>
        </select>
        <input v-model="filters.date_from" type="date" @change="fetchData" class="border border-gray-300 rounded-lg px-3 py-2 text-sm" />
        <span class="text-gray-400 text-sm">to</span>
        <input v-model="filters.date_to" type="date" @change="fetchData" class="border border-gray-300 rounded-lg px-3 py-2 text-sm" />
        <button v-if="hasFilters" @click="clearFilters" class="text-sm text-gray-500 hover:text-gray-700 border border-gray-300 rounded-lg px-3 py-2 bg-white">Clear</button>
      </div>
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
            <th class="text-left px-4 py-3 font-medium">Customer</th>
            <th class="text-left px-4 py-3 font-medium">Order #</th>
            <th class="text-left px-4 py-3 font-medium">Date</th>
            <th class="text-left px-4 py-3 font-medium">Amount</th>
            <th class="text-left px-4 py-3 font-medium">Paid</th>
            <th class="text-left px-4 py-3 font-medium">Status</th>
            <th class="text-right px-4 py-3 font-medium">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="item in items" :key="item.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 font-mono text-xs">{{ item.invoice_number || '#' + item.id }}</td>
            <td class="px-4 py-3">{{ item.order?.customer?.name || '-' }}</td>
            <td class="px-4 py-3 font-mono text-xs">#{{ item.order_id }}</td>
            <td class="px-4 py-3">{{ formatDate(item.invoice_date) }}</td>
            <td class="px-4 py-3 font-medium">${{ Number(item.amount || 0).toFixed(2) }}</td>
            <td class="px-4 py-3">${{ Number(item.paid_amount || 0).toFixed(2) }}</td>
            <td class="px-4 py-3">
              <span class="px-2 py-0.5 rounded-full text-xs font-medium" :class="statusClass(item.status)">{{ humanize(item.status) }}</span>
            </td>
            <td class="px-4 py-3 text-right whitespace-nowrap">
              <button @click="openInvoice(item)" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium mr-3">View / Print</button>
              <button @click="openForm(item)" class="text-gray-600 hover:text-gray-800 text-sm font-medium mr-3">Edit</button>
              <button @click="confirmDelete(item)" class="text-red-600 hover:text-red-800 text-sm font-medium">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Invoice View / Print Modal -->
    <div v-if="viewInvoice" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="viewInvoice = null">
      <div class="bg-white rounded-xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
        <div class="invoice-print p-6">
          <div class="flex items-start justify-between mb-6">
            <div>
              <h1 class="text-2xl font-bold text-gray-900">INVOICE</h1>
              <p class="text-sm text-gray-500">{{ viewInvoice.invoice_number }}</p>
            </div>
            <div class="text-right text-sm">
              <p class="font-semibold text-gray-800">Bricks Factory</p>
              <p class="text-gray-500">info@bricksfactory.com</p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-6 mb-6">
            <div>
              <p class="text-xs uppercase tracking-wide text-gray-500 mb-1">Bill To</p>
              <p class="font-semibold text-gray-800">{{ viewInvoice.order?.customer?.name || '-' }}</p>
              <p class="text-sm text-gray-600">{{ viewInvoice.order?.customer?.company || '' }}</p>
              <p class="text-sm text-gray-600">{{ viewInvoice.order?.customer?.phone || '' }}</p>
              <p class="text-sm text-gray-600">{{ viewInvoice.order?.customer?.address || '' }}</p>
            </div>
            <div class="text-right text-sm space-y-1">
              <div><span class="text-gray-500">Invoice Date: </span><span class="font-medium">{{ formatDate(viewInvoice.invoice_date) }}</span></div>
              <div><span class="text-gray-500">Due Date: </span><span class="font-medium">{{ formatDate(viewInvoice.due_date) }}</span></div>
              <div><span class="text-gray-500">Sales Order: </span><span class="font-medium">#{{ viewInvoice.order_id }}</span></div>
              <div><span class="text-gray-500">Status: </span><span class="px-2 py-0.5 rounded-full text-xs font-medium" :class="statusClass(viewInvoice.status)">{{ humanize(viewInvoice.status) }}</span></div>
            </div>
          </div>

          <table class="w-full text-sm mb-6">
            <thead class="bg-gray-50 text-gray-600">
              <tr>
                <th class="text-left px-3 py-2 font-medium">Product</th>
                <th class="text-right px-3 py-2 font-medium">Qty</th>
                <th class="text-right px-3 py-2 font-medium">Unit Price</th>
                <th class="text-right px-3 py-2 font-medium">Total</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="li in viewInvoice.order?.items || []" :key="li.id">
                <td class="px-3 py-2">{{ li.product?.name || '-' }}</td>
                <td class="px-3 py-2 text-right">{{ li.quantity }}</td>
                <td class="px-3 py-2 text-right">${{ Number(li.unit_price || 0).toFixed(2) }}</td>
                <td class="px-3 py-2 text-right font-medium">${{ Number(li.total || li.quantity * li.unit_price || 0).toFixed(2) }}</td>
              </tr>
              <tr v-if="!(viewInvoice.order?.items || []).length">
                <td colspan="4" class="px-3 py-2 text-center text-gray-400">No items</td>
              </tr>
            </tbody>
          </table>

          <div class="flex justify-end mb-6">
            <div class="text-right text-sm space-y-1 w-56">
              <div class="flex justify-between"><span class="text-gray-500">Subtotal</span><span class="font-medium">${{ Number(viewInvoice.amount || 0).toFixed(2) }}</span></div>
              <div class="flex justify-between"><span class="text-gray-500">Paid</span><span class="font-medium">-${{ Number(viewInvoice.paid_amount || 0).toFixed(2) }}</span></div>
              <div class="flex justify-between border-t border-gray-200 pt-1"><span class="font-semibold text-gray-800">Balance Due</span><span class="font-bold text-gray-900">${{ balanceDue.toFixed(2) }}</span></div>
            </div>
          </div>

          <div v-if="viewInvoice.payments?.length" class="mb-6">
            <p class="text-xs uppercase tracking-wide text-gray-500 mb-2">Payments</p>
            <table class="w-full text-sm">
              <thead class="bg-gray-50 text-gray-600">
                <tr>
                  <th class="text-left px-3 py-2 font-medium">Date</th>
                  <th class="text-left px-3 py-2 font-medium">Method</th>
                  <th class="text-right px-3 py-2 font-medium">Amount</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="p in viewInvoice.payments" :key="p.id">
                  <td class="px-3 py-2">{{ formatDate(p.payment_date || p.created_at) }}</td>
                  <td class="px-3 py-2">{{ p.method || '-' }}</td>
                  <td class="px-3 py-2 text-right">${{ Number(p.amount || 0).toFixed(2) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-200 no-print">
          <button @click="viewInvoice = null" class="px-4 py-2.5 text-sm text-gray-700 bg-gray-100 rounded-lg">Close</button>
          <button @click="printInvoice" class="px-6 py-2.5 text-sm text-white bg-indigo-600 rounded-lg">Print / Save as PDF</button>
        </div>
      </div>
    </div>

    <!-- Form Modal -->
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
import { ref, reactive, computed, onMounted } from 'vue'
import axios from '@/utils/axios'

const items = ref([]); const salesOrders = ref([])
const loading = ref(true); const error = ref('')
const formVisible = ref(false); const editingItem = ref(null); const saving = ref(false); const formError = ref('')
const deleteItem = ref(null); const deleting = ref(false)
const viewInvoice = ref(null)
const form = reactive({ sales_order_id: '', invoice_date: '', status: 'draft' })
const filters = reactive({ search: '', status: '', date_from: '', date_to: '' })

const hasFilters = computed(() => filters.search !== '' || filters.status !== '' || filters.date_from !== '' || filters.date_to !== '')
const balanceDue = computed(() => Number(viewInvoice.value?.amount || 0) - Number(viewInvoice.value?.paid_amount || 0))

function statusClass(s) {
  const map = { draft: 'bg-gray-100 text-gray-800', sent: 'bg-blue-100 text-blue-800', paid: 'bg-green-100 text-green-800', partial: 'bg-indigo-100 text-indigo-800', overdue: 'bg-red-100 text-red-800', cancelled: 'bg-yellow-100 text-yellow-800' }
  return map[s] || 'bg-gray-100 text-gray-800'
}

async function fetchData() {
  loading.value = true
  try {
    const params = {}
    if (filters.search) params.search = filters.search
    if (filters.status) params.status = filters.status
    if (filters.date_from) params.date_from = filters.date_from
    if (filters.date_to) params.date_to = filters.date_to
    const { data } = await axios.get('/invoices', { params })
    items.value = data.data || data
  }
  catch (e) { error.value = 'Failed to load invoices.' }
  finally { loading.value = false }
}

function clearFilters() {
  filters.search = ''; filters.status = ''; filters.date_from = ''; filters.date_to = ''
  fetchData()
}

async function openInvoice(item) {
  try {
    const { data } = await axios.get(`/invoices/${item.id}`)
    viewInvoice.value = data.data || data
  } catch (e) {
    viewInvoice.value = item
  }
}

function printInvoice() { window.print() }

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
    else { const { data } = await axios.post('/invoices', form); items.value.unshift(data.data || data) }
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
