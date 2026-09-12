<template>
  <div>
    <div class="flex justify-end mb-4">
      <button @click="openForm(null)" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">+ Add Machine</button>
    </div>

    <div v-if="loading" class="flex justify-center py-20">
      <div class="w-10 h-10 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
    </div>
    <div v-else-if="error" class="bg-red-50 text-red-700 p-4 rounded-lg">{{ error }}</div>
    <div v-else-if="!items.length" class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center text-gray-500">No machines found.</div>
    <div v-else class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-600">
          <tr>
            <th class="text-left px-4 py-3 font-medium">Name</th>
            <th class="text-left px-4 py-3 font-medium">Type</th>
            <th class="text-left px-4 py-3 font-medium">Status</th>
            <th class="text-left px-4 py-3 font-medium">Last Maintenance</th>
            <th class="text-left px-4 py-3 font-medium">Next Maintenance</th>
            <th class="text-right px-4 py-3 font-medium">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="item in items" :key="item.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 font-medium">{{ item.name }}</td>
            <td class="px-4 py-3">{{ item.type || '-' }}</td>
            <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium" :class="statusClass(item.status)">{{ humanize(item.status) }}</span></td>
            <td class="px-4 py-3">{{ formatDate(item.last_maintenance) || '-' }}</td>
            <td class="px-4 py-3">{{ formatDate(item.next_maintenance) || '-' }}</td>
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
      <div class="bg-white rounded-xl shadow-2xl p-6 max-w-lg w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ editingItem ? 'Edit' : 'Add' }} Machine</h3>
        <div v-if="formError" class="mb-4 bg-red-50 text-red-700 px-4 py-3 rounded-lg text-sm">{{ formError }}</div>
        <form @submit.prevent="handleSave" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
            <input v-model="form.name" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
            <input v-model="form.type" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select v-model="form.status" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
              <option value="active">Active</option>
              <option value="idle">Idle</option>
              <option value="maintenance">Maintenance</option>
              <option value="broken">Broken</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Last Maintenance</label>
            <input v-model="form.last_maintenance" type="date" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Next Maintenance</label>
            <input v-model="form.next_maintenance" type="date" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
          </div>
          <div class="flex justify-end gap-3 pt-2">
            <button type="button" @click="formVisible = false" class="px-4 py-2.5 text-sm text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Cancel</button>
            <button type="submit" :disabled="saving" class="px-6 py-2.5 text-sm text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 disabled:opacity-50">
              {{ saving ? 'Saving...' : 'Save' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Modal -->
    <div v-if="deleteItem" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="deleteItem = null">
      <div class="bg-white rounded-xl shadow-2xl p-6 max-w-sm w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-800 mb-2">Confirm Delete</h3>
        <p class="text-gray-600 text-sm mb-4">Delete "{{ deleteItem.name }}"? This cannot be undone.</p>
        <div class="flex justify-end gap-3">
          <button @click="deleteItem = null" class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Cancel</button>
          <button @click="handleDelete" :disabled="deleting" class="px-4 py-2 text-sm text-white bg-red-600 rounded-lg hover:bg-red-700 disabled:opacity-50">{{ deleting ? 'Deleting...' : 'Delete' }}</button>
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
const formVisible = ref(false)
const editingItem = ref(null)
const saving = ref(false)
const formError = ref('')
const deleteItem = ref(null)
const deleting = ref(false)
const form = reactive({ name: '', type: '', status: 'active', last_maintenance: '', next_maintenance: '' })

function statusClass(s) {
  const map = { active: 'bg-green-100 text-green-800', idle: 'bg-gray-100 text-gray-800', maintenance: 'bg-yellow-100 text-yellow-800', broken: 'bg-red-100 text-red-800' }
  return map[s] || 'bg-gray-100 text-gray-800'
}

async function fetchData() {
  loading.value = true
  try {
    const { data } = await axios.get('/machines')
    items.value = data.data || data
  } catch (e) { error.value = 'Failed to load machines.' }
  finally { loading.value = false }
}

function openForm(item) {
  editingItem.value = item
  if (item) {
    form.name = item.name; form.type = item.type || ''; form.status = item.status
    form.last_maintenance = item.last_maintenance?.slice(0, 10) || ''; form.next_maintenance = item.next_maintenance?.slice(0, 10) || ''
  } else {
    form.name = ''; form.type = ''; form.status = 'active'; form.last_maintenance = ''; form.next_maintenance = ''
  }
  formVisible.value = true
  formError.value = ''
}

async function handleSave() {
  saving.value = true
  formError.value = ''
  try {
    if (editingItem.value) {
      const { data } = await axios.put(`/machines/${editingItem.value.id}`, form)
      Object.assign(editingItem.value, data.data || data)
    } else {
      const { data } = await axios.post('/machines', form)
      items.value.unshift(data.data || data)
    }
    formVisible.value = false
  } catch (e) { formError.value = e.response?.data?.message || 'Failed to save.' }
  finally { saving.value = false }
}

function confirmDelete(item) { deleteItem.value = item }
async function handleDelete() {
  deleting.value = true
  try {
    await axios.delete(`/machines/${deleteItem.value.id}`)
    items.value = items.value.filter(i => i.id !== deleteItem.value.id)
    deleteItem.value = null
  } catch (e) { error.value = 'Failed to delete.' }
  finally { deleting.value = false }
}

onMounted(fetchData)
</script>
