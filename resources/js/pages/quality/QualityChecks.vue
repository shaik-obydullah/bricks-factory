<template>
  <div>
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-lg font-semibold text-gray-800">Quality Checks</h2>
      <button @click="openForm()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700">+ New Check</button>
    </div>

    <div v-if="loading" class="flex justify-center py-20">
      <div class="w-10 h-10 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
    </div>
    <div v-else-if="error" class="bg-red-50 text-red-700 p-4 rounded-lg">{{ error }}</div>
    <div v-else-if="!items.length" class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center text-gray-500">No quality checks found.</div>
    <div v-else class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-600">
          <tr>
            <th class="text-left px-4 py-3 font-medium">Date</th>
            <th class="text-left px-4 py-3 font-medium">Batch</th>
            <th class="text-left px-4 py-3 font-medium">Inspector</th>
            <th class="text-left px-4 py-3 font-medium">Result</th>
            <th class="text-left px-4 py-3 font-medium">Notes</th>
            <th class="text-right px-4 py-3 font-medium">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="item in items" :key="item.id" class="hover:bg-gray-50">
            <td class="px-4 py-3">{{ item.check_date || item.created_at?.substring(0, 10) }}</td>
            <td class="px-4 py-3">#{{ item.batch_id }}</td>
            <td class="px-4 py-3">{{ item.inspector?.name || item.inspector || '-' }}</td>
            <td class="px-4 py-3">
              <span class="px-2 py-0.5 rounded-full text-xs font-medium" :class="resultClass(item.status)">{{ item.status }}</span>
            </td>
            <td class="px-4 py-3 max-w-xs truncate">{{ item.notes || '-' }}</td>
            <td class="px-4 py-3 text-right">
              <button @click="confirmDelete(item)" class="text-red-600 hover:text-red-800 text-sm font-medium">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Form Modal -->
    <div v-if="formVisible" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="formVisible = false">
      <div class="bg-white rounded-xl shadow-2xl p-6 max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">New Quality Check</h3>
        <div v-if="formError" class="mb-4 bg-red-50 text-red-700 px-4 py-3 rounded-lg text-sm">{{ formError }}</div>
        <form @submit.prevent="createCheck" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Batch</label>
            <select v-model="form.batch_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm">
              <option value="">Select Batch</option>
              <option v-for="b in batches" :key="b.id" :value="b.id">#{{ b.id }} - {{ b.order?.product?.name || '' }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Inspector</label>
            <input v-model="form.inspector" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Result</label>
            <select v-model="form.result" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm">
              <option value="pass">Pass</option>
              <option value="fail">Fail</option>
              <option value="conditional">Conditional</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea v-model="form.notes" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm"></textarea>
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
        <p class="text-gray-600 text-sm mb-4">Delete this quality check? This cannot be undone.</p>
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

const items = ref([])
const batches = ref([])
const loading = ref(true)
const error = ref('')
const formVisible = ref(false)
const saving = ref(false)
const formError = ref('')
const deleteItem = ref(null)
const deleting = ref(false)
const form = reactive({ batch_id: '', inspector: '', result: 'pass', notes: '' })

function resultClass(r) {
  const map = { pass: 'bg-green-100 text-green-800', passed: 'bg-green-100 text-green-800', fail: 'bg-red-100 text-red-800', failed: 'bg-red-100 text-red-800', conditional: 'bg-yellow-100 text-yellow-800', pending: 'bg-yellow-100 text-yellow-800' }
  return map[r] || 'bg-gray-100 text-gray-800'
}

async function fetchData() {
  loading.value = true
  try {
    const { data } = await axios.get('/quality-checks')
    items.value = data.data || data
  } catch (e) { error.value = 'Failed to load checks.' }
  finally { loading.value = false }
}

async function openForm() {
  formVisible.value = true; formError.value = ''
  form.batch_id = ''; form.inspector = ''; form.result = 'pass'; form.notes = ''
  try {
    const { data } = await axios.get('/production-batches')
    batches.value = data.data || data
  } catch (e) {}
}

async function createCheck() {
  saving.value = true; formError.value = ''
  try {
    const { data } = await axios.post('/quality-checks', form)
    items.value.unshift(data.data || data)
    formVisible.value = false
  } catch (e) { formError.value = e.response?.data?.message || 'Failed to create check.' }
  finally { saving.value = false }
}

function confirmDelete(item) { deleteItem.value = item }
async function handleDelete() {
  deleting.value = true
  try {
    await axios.delete(`/quality-checks/${deleteItem.value.id}`)
    items.value = items.value.filter(i => i.id !== deleteItem.value.id)
    deleteItem.value = null
  } catch (e) { error.value = 'Failed to delete.' }
  finally { deleting.value = false }
}

onMounted(fetchData)
</script>
