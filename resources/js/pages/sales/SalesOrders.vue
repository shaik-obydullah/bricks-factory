<template>
  <div>
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center gap-3">
        <select v-model="filters.status" class="text-sm border border-gray-300 rounded-lg px-3 py-2 bg-white" @change="fetchData">
          <option value="">All Statuses</option>
          <option value="pending">Pending</option>
          <option value="confirmed">Confirmed</option>
          <option value="processing">Processing</option>
          <option value="shipped">Shipped</option>
          <option value="delivered">Delivered</option>
          <option value="cancelled">Cancelled</option>
        </select>
      </div>
      <button @click="openForm()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700">+ New Order</button>
    </div>

    <div v-if="loading" class="flex justify-center py-20">
      <div class="w-10 h-10 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
    </div>
    <div v-else-if="error" class="bg-red-50 text-red-700 p-4 rounded-lg">{{ error }}</div>
    <div v-else-if="!items.length" class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center text-gray-500">No sales orders found.</div>
    <div v-else class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-600">
          <tr>
            <th class="text-left px-4 py-3 font-medium">Order #</th>
            <th class="text-left px-4 py-3 font-medium">Customer</th>
            <th class="text-left px-4 py-3 font-medium">Date</th>
            <th class="text-left px-4 py-3 font-medium">Total</th>
            <th class="text-left px-4 py-3 font-medium">Status</th>
            <th class="text-right px-4 py-3 font-medium">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="item in items" :key="item.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 font-mono text-xs">#{{ item.id }}</td>
            <td class="px-4 py-3">{{ item.customer?.name || '-' }}</td>
            <td class="px-4 py-3">{{ item.order_date || item.created_at?.substring(0, 10) }}</td>
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

    <!-- Form Modal -->
    <div v-if="formVisible" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="formVisible = false">
      <div class="bg-white rounded-xl shadow-2xl p-6 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ editingItem ? 'Edit' : 'New' }} Sales Order</h3>
        <div v-if="formError" class="mb-4 bg-red-50 text-red-700 px-4 py-3 rounded-lg text-sm">{{ formError }}</div>
        <form @submit.prevent="handleSave" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
              <select v-model="form.customer_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm">
                <option value="">Select Customer</option>
                <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Order Date</label>
              <input v-model="form.order_date" type="date" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm" />
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select v-model="form.status" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm">
              <option value="pending">Pending</option>
              <option value="confirmed">Confirmed</option>
              <option value="processing">Processing</option>
              <option value="shipped">Shipped</option>
              <option value="delivered">Delivered</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Order Items</label>
            <div v-for="(item, idx) in form.items" :key="idx" class="flex gap-2 mb-2">
              <select v-model="item.product_id" class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">Select Product</option>
                <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
              </select>
              <input v-model.number="item.quantity" type="number" min="1" placeholder="Qty" class="w-20 border border-gray-300 rounded-lg px-3 py-2 text-sm" />
              <input v-model.number="item.unit_price" type="number" step="0.01" min="0" placeholder="Price" class="w-24 border border-gray-300 rounded-lg px-3 py-2 text-sm" />
              <button type="button" @click="removeItem(idx)" class="text-red-500 hover:text-red-700 px-2">&times;</button>
            </div>
            <button type="button" @click="addItem" class="text-sm text-indigo-600 hover:text-indigo-800">+ Add Item</button>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea v-model="form.notes" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm"></textarea>
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
        <p class="text-gray-600 text-sm mb-4">Delete order #{{ deleteItem.id }}? This cannot be undone.</p>
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

const items = ref([]); const loading = ref(true); const error = ref('')
const formVisible = ref(false); const editingItem = ref(null); const saving = ref(false); const formError = ref('')
const deleteItem = ref(null); const deleting = ref(false)
const customers = ref([]); const products = ref([])
const filters = reactive({ status: '' })
const form = reactive({ customer_id: '', order_date: '', status: 'pending', notes: '', items: [] })

function statusClass(s) {
  const map = { pending: 'bg-yellow-100 text-yellow-800', confirmed: 'bg-blue-100 text-blue-800', processing: 'bg-indigo-100 text-indigo-800', shipped: 'bg-purple-100 text-purple-800', delivered: 'bg-green-100 text-green-800', cancelled: 'bg-red-100 text-red-800' }
  return map[s] || 'bg-gray-100 text-gray-800'
}

async function fetchData() {
  loading.value = true
  try {
    const params = {}
    if (filters.status) params.status = filters.status
    const { data } = await axios.get('/sales-orders', { params })
    items.value = data.data || data
  } catch (e) { error.value = 'Failed to load orders.' }
  finally { loading.value = false }
}

async function openForm(item) {
  editingItem.value = item
  try {
    const [cRes, pRes] = await Promise.all([axios.get('/customers'), axios.get('/products')])
    customers.value = cRes.data.data || cRes.data
    products.value = pRes.data.data || pRes.data
  } catch (e) {}
  if (item) {
    form.customer_id = item.customer_id; form.order_date = item.order_date || ''; form.status = item.status; form.notes = item.notes || ''
    form.items = (item.items || []).map(i => ({ product_id: i.product_id, quantity: i.quantity, unit_price: i.unit_price }))
  } else {
    form.customer_id = ''; form.order_date = new Date().toISOString().substring(0, 10); form.status = 'pending'; form.notes = ''; form.items = []
  }
  formVisible.value = true; formError.value = ''
}

function addItem() { form.items.push({ product_id: '', quantity: 1, unit_price: 0 }) }
function removeItem(idx) { form.items.splice(idx, 1) }

async function handleSave() {
  saving.value = true; formError.value = ''
  try {
    if (editingItem.value) {
      const { data } = await axios.put(`/sales-orders/${editingItem.value.id}`, form)
      Object.assign(editingItem.value, data.data || data)
    } else {
      const { data } = await axios.post('/sales-orders', form)
      items.value.push(data.data || data)
    }
    formVisible.value = false
  } catch (e) { formError.value = e.response?.data?.message || 'Failed to save.' }
  finally { saving.value = false }
}

function confirmDelete(item) { deleteItem.value = item }
async function handleDelete() {
  deleting.value = true
  try { await axios.delete(`/sales-orders/${deleteItem.value.id}`); items.value = items.value.filter(i => i.id !== deleteItem.value.id); deleteItem.value = null }
  catch (e) { error.value = 'Failed to delete.' }
  finally { deleting.value = false }
}

onMounted(fetchData)
</script>
