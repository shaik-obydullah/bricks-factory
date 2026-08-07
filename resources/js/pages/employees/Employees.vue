<template>
  <div>
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-lg font-semibold text-gray-800">Employees</h2>
      <button @click="openForm(null)" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700">+ Add Employee</button>
    </div>

    <div v-if="loading" class="flex justify-center py-20">
      <div class="w-10 h-10 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
    </div>
    <div v-else-if="error" class="bg-red-50 text-red-700 p-4 rounded-lg">{{ error }}</div>
    <div v-else-if="!items.length" class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center text-gray-500">No employees found.</div>
    <div v-else class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-600">
          <tr>
            <th class="text-left px-4 py-3 font-medium">Employee ID</th>
            <th class="text-left px-4 py-3 font-medium">Name</th>
            <th class="text-left px-4 py-3 font-medium">Designation</th>
            <th class="text-left px-4 py-3 font-medium">Department</th>
            <th class="text-left px-4 py-3 font-medium">Shift</th>
            <th class="text-left px-4 py-3 font-medium">Phone</th>
            <th class="text-left px-4 py-3 font-medium">Status</th>
            <th class="text-right px-4 py-3 font-medium">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="item in items" :key="item.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 font-mono text-xs">{{ item.employee_id }}</td>
            <td class="px-4 py-3 font-medium">{{ item.name }}</td>
            <td class="px-4 py-3">{{ item.designation || '-' }}</td>
            <td class="px-4 py-3">{{ item.department || '-' }}</td>
            <td class="px-4 py-3">{{ item.shift?.name || '-' }}</td>
            <td class="px-4 py-3">{{ item.phone || '-' }}</td>
            <td class="px-4 py-3">
              <span :class="item.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'" class="px-2 py-1 rounded-full text-xs font-medium">{{ item.status }}</span>
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
        <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ editingItem ? 'Edit' : 'Add' }} Employee</h3>
        <div v-if="formError" class="mb-4 bg-red-50 text-red-700 px-4 py-3 rounded-lg text-sm">{{ formError }}</div>
        <form @submit.prevent="handleSave" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Employee ID</label>
            <input v-model="form.employee_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
            <input v-model="form.name" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm" />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Designation</label>
              <input v-model="form.designation" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
              <input v-model="form.department" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm" />
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
              <input v-model="form.phone" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
              <input v-model="form.email" type="email" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm" />
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Shift</label>
              <select v-model="form.shift_id" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm">
                <option :value="null">No shift</option>
                <option v-for="shift in shifts" :key="shift.id" :value="shift.id">{{ shift.name }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Joining Date</label>
              <input v-model="form.joining_date" type="date" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm" />
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select v-model="form.status" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
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
        <p class="text-gray-600 text-sm mb-4">Delete "{{ deleteItem.name }}"? This cannot be undone.</p>
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
const shifts = ref([])
const loading = ref(true)
const error = ref('')
const formVisible = ref(false)
const editingItem = ref(null)
const saving = ref(false)
const formError = ref('')
const deleteItem = ref(null)
const deleting = ref(false)
const form = reactive({ employee_id: '', name: '', phone: '', email: '', designation: '', department: '', shift_id: null, joining_date: '', status: 'active' })

async function fetchData() {
  loading.value = true
  try {
    const { data } = await axios.get('/employees')
    items.value = data.data || data
  } catch (e) { error.value = 'Failed to load employees.' }
  finally { loading.value = false }
}

async function fetchShifts() {
  try {
    const { data } = await axios.get('/shifts')
    shifts.value = data.data || data
  } catch (e) { shifts.value = [] }
}

function openForm(item) {
  editingItem.value = item
  if (item) {
    form.employee_id = item.employee_id; form.name = item.name; form.phone = item.phone || ''; form.email = item.email || ''
    form.designation = item.designation || ''; form.department = item.department || ''; form.shift_id = item.shift_id || null
    form.joining_date = item.joining_date || ''; form.status = item.status
  } else {
    form.employee_id = ''; form.name = ''; form.phone = ''; form.email = ''; form.designation = ''; form.department = ''
    form.shift_id = null; form.joining_date = ''; form.status = 'active'
  }
  formVisible.value = true; formError.value = ''
}

async function handleSave() {
  saving.value = true; formError.value = ''
  try {
    if (editingItem.value) {
      const { data } = await axios.put(`/employees/${editingItem.value.id}`, form)
      Object.assign(editingItem.value, data.data || data)
    } else {
      const { data } = await axios.post('/employees', form)
      items.value.push(data.data || data)
    }
    formVisible.value = false
  } catch (e) { formError.value = e.response?.data?.message || Object.values(e.response?.data?.errors || {}).flat().join(', ') || 'Failed to save.' }
  finally { saving.value = false }
}

function confirmDelete(item) { deleteItem.value = item }
async function handleDelete() {
  deleting.value = true
  try {
    await axios.delete(`/employees/${deleteItem.value.id}`)
    items.value = items.value.filter(i => i.id !== deleteItem.value.id)
    deleteItem.value = null
  } catch (e) { error.value = 'Failed to delete.' }
  finally { deleting.value = false }
}

onMounted(() => { fetchData(); fetchShifts() })
</script>
