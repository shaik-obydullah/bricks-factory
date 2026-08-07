<template>
  <div>
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-lg font-semibold text-gray-800">Goods Receipts</h2>
      <button @click="openForm()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700">+ Receive Goods</button>
    </div>

    <div v-if="loading" class="flex justify-center py-20"><div class="w-10 h-10 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div></div>
    <div v-else-if="error" class="bg-red-50 text-red-700 p-4 rounded-lg">{{ error }}</div>
    <div v-else-if="!items.length" class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center text-gray-500">No goods receipts found.</div>
    <div v-else class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-600">
          <tr><th class="text-left px-4 py-3 font-medium">Date</th><th class="text-left px-4 py-3 font-medium">PO #</th><th class="text-left px-4 py-3 font-medium">Supplier</th><th class="text-left px-4 py-3 font-medium">Item</th><th class="text-left px-4 py-3 font-medium">Quantity</th><th class="text-left px-4 py-3 font-medium">Status</th></tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="item in items" :key="item.id" class="hover:bg-gray-50">
            <td class="px-4 py-3">{{ item.received_date || item.created_at?.substring(0, 10) }}</td>
            <td class="px-4 py-3">#{{ item.purchase_order_id }}</td>
            <td class="px-4 py-3">{{ item.purchase_order?.supplier?.name || '-' }}</td>
            <td class="px-4 py-3">{{ item.raw_material?.name || '-' }}</td>
            <td class="px-4 py-3">{{ item.quantity_received }}</td>
            <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium" :class="item.status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'">{{ item.status }}</span></td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="formVisible" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="formVisible = false">
      <div class="bg-white rounded-xl shadow-2xl p-6 max-w-lg w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Receive Goods</h3>
        <div v-if="formError" class="mb-4 bg-red-50 text-red-700 px-4 py-3 rounded-lg text-sm">{{ formError }}</div>
        <form @submit.prevent="createReceipt" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Purchase Order</label>
            <select v-model="form.purchase_order_id" required @change="loadPOItems" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm">
              <option value="">Select PO</option>
              <option v-for="po in purchaseOrders" :key="po.id" :value="po.id">#{{ po.id }} - {{ po.supplier?.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Raw Material</label>
            <select v-model="form.raw_material_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm">
              <option value="">Select Material</option>
              <option v-for="m in materials" :key="m.id" :value="m.id">{{ m.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Quantity Received</label>
            <input v-model.number="form.quantity_received" type="number" min="1" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Warehouse</label>
            <select v-model="form.warehouse_id" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm">
              <option value="">Select Warehouse</option>
              <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Received Date</label>
            <input v-model="form.received_date" type="date" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm" />
          </div>
          <div class="flex justify-end gap-3 pt-2">
            <button type="button" @click="formVisible = false" class="px-4 py-2.5 text-sm text-gray-700 bg-gray-100 rounded-lg">Cancel</button>
            <button type="submit" :disabled="saving" class="px-6 py-2.5 text-sm text-white bg-indigo-600 rounded-lg disabled:opacity-50">{{ saving ? 'Saving...' : 'Save' }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import axios from '@/utils/axios'

const items = ref([]); const purchaseOrders = ref([]); const materials = ref([]); const warehouses = ref([])
const loading = ref(true); const error = ref('')
const formVisible = ref(false); const saving = ref(false); const formError = ref('')
const form = reactive({ purchase_order_id: '', raw_material_id: '', quantity_received: 1, warehouse_id: '', received_date: '' })

async function fetchData() {
  loading.value = true
  try { const { data } = await axios.get('/goods-receipts'); items.value = data.data || data }
  catch (e) { error.value = 'Failed to load receipts.' }
  finally { loading.value = false }
}

async function openForm() {
  formVisible.value = true; formError.value = ''
  form.purchase_order_id = ''; form.raw_material_id = ''; form.quantity_received = 1; form.warehouse_id = ''; form.received_date = new Date().toISOString().substring(0, 10)
  try {
    const [poRes, mRes, wRes] = await Promise.all([axios.get('/purchase-orders'), axios.get('/raw-materials'), axios.get('/warehouses')])
    purchaseOrders.value = poRes.data.data || poRes.data
    materials.value = mRes.data.data || mRes.data
    warehouses.value = wRes.data.data || wRes.data
  } catch (e) {}
}

async function loadPOItems() {}

async function createReceipt() {
  saving.value = true; formError.value = ''
  try {
    const { data } = await axios.post('/goods-receipts', form)
    items.value.unshift(data.data || data)
    formVisible.value = false
  } catch (e) { formError.value = e.response?.data?.message || 'Failed to record receipt.' }
  finally { saving.value = false }
}

onMounted(fetchData)
</script>
