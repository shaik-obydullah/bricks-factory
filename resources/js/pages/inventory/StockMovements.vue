<template>
  <div>
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center gap-3">
        <select v-model="filters.type" class="text-sm border border-gray-300 rounded-lg px-3 py-2 bg-white" @change="fetchData">
          <option value="">All Types</option>
          <option value="in">Stock In</option>
          <option value="out">Stock Out</option>
          <option value="transfer">Transfer</option>
        </select>
        <select v-model="filters.movable_type" class="text-sm border border-gray-300 rounded-lg px-3 py-2 bg-white" @change="fetchData">
          <option value="">All Types</option>
          <option value="App\Models\Product">Products</option>
          <option value="App\Models\RawMaterial">Raw Materials</option>
        </select>
      </div>
      <button @click="openForm()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700">+ New Movement</button>
    </div>

    <div v-if="loading" class="flex justify-center py-20">
      <div class="w-10 h-10 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
    </div>
    <div v-else-if="error" class="bg-red-50 text-red-700 p-4 rounded-lg">{{ error }}</div>
    <div v-else-if="!items.length" class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center text-gray-500">No stock movements found.</div>
    <div v-else class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-600">
          <tr>
            <th class="text-left px-4 py-3 font-medium">Date</th>
            <th class="text-left px-4 py-3 font-medium">Type</th>
            <th class="text-left px-4 py-3 font-medium">Item</th>
            <th class="text-left px-4 py-3 font-medium">Quantity</th>
            <th class="text-left px-4 py-3 font-medium">Reference</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="item in items" :key="item.id" class="hover:bg-gray-50">
            <td class="px-4 py-3">{{ formatDate(item.created_at) }}</td>
            <td class="px-4 py-3">
              <span class="px-2 py-0.5 rounded-full text-xs font-medium" :class="item.movement_type === 'in' ? 'bg-green-100 text-green-800' : item.movement_type === 'out' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'">{{ humanize(item.movement_type) }}</span>
            </td>
            <td class="px-4 py-3">{{ item.typeable?.name || '-' }}</td>
            <td class="px-4 py-3">{{ item.quantity }}</td>
            <td class="px-4 py-3 text-xs">{{ item.reference || '-' }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Form Modal -->
    <div v-if="formVisible" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="formVisible = false">
      <div class="bg-white rounded-xl shadow-2xl p-6 max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Record Stock Movement</h3>
        <div v-if="formError" class="mb-4 bg-red-50 text-red-700 px-4 py-3 rounded-lg text-sm">{{ formError }}</div>
        <form @submit.prevent="createMovement" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
            <select v-model="form.type" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm">
              <option value="in">Stock In</option>
              <option value="out">Stock Out</option>
              <option value="transfer">Transfer</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Item Type</label>
            <select v-model="form.movable_type" @change="loadItems" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm">
              <option value="App\Models\Product">Product</option>
              <option value="App\Models\RawMaterial">Raw Material</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Item</label>
            <select v-model="form.movable_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm">
              <option value="">Select Item</option>
              <option v-for="i in movableItems" :key="i.id" :value="i.id">{{ i.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
            <input v-model.number="form.quantity" type="number" min="1" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">From Warehouse</label>
            <select v-model="form.from_warehouse_id" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm">
              <option value="">Select</option>
              <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">To Warehouse</label>
            <select v-model="form.to_warehouse_id" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm">
              <option value="">Select</option>
              <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
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
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import axios from '@/utils/axios'

const items = ref([])
const loading = ref(true)
const error = ref('')
const filters = reactive({ type: '', movable_type: '' })
const formVisible = ref(false)
const saving = ref(false)
const formError = ref('')
const warehouses = ref([])
const movableItems = ref([])
const form = reactive({ type: 'in', movable_type: 'App\\Models\\Product', movable_id: '', quantity: 1, from_warehouse_id: '', to_warehouse_id: '', reference: '' })

async function fetchData() {
  loading.value = true; error.value = ''
  try {
    const params = {}
    if (filters.type) params.type = filters.type
    if (filters.movable_type) params.movable_type = filters.movable_type
    const { data } = await axios.get('/stock-movements', { params })
    items.value = data.data || data
  } catch (e) { error.value = 'Failed to load movements.' }
  finally { loading.value = false }
}

async function loadItems() {
  try {
    const endpoint = form.movable_type === 'App\\Models\\Product' ? '/products' : '/raw-materials'
    const { data } = await axios.get(endpoint)
    movableItems.value = data.data || data
  } catch (e) {}
}

async function openForm() {
  formVisible.value = true; formError.value = ''
  form.type = 'in'; form.movable_type = 'App\\Models\\Product'
  form.movable_id = ''; form.quantity = 1; form.from_warehouse_id = ''; form.to_warehouse_id = ''; form.reference = ''
  try {
    const { data } = await axios.get('/warehouses')
    warehouses.value = data.data || data
  } catch (e) {}
  await loadItems()
}

async function createMovement() {
  saving.value = true; formError.value = ''
  try {
    const { data } = await axios.post('/stock-movements', form)
    items.value.unshift(data.data || data)
    formVisible.value = false
  } catch (e) { formError.value = e.response?.data?.message || 'Failed to record movement.' }
  finally { saving.value = false }
}

onMounted(fetchData)
</script>
