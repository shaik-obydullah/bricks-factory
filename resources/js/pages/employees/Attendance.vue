<template>
  <div>
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center gap-2">
        <input v-model="filters.search" @input="fetchData" placeholder="Search employee name or ID..." class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-64 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
        <select v-model="filters.status" @change="fetchData" class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
          <option value="">All Statuses</option>
          <option value="present">Present</option>
          <option value="absent">Absent</option>
          <option value="late">Late</option>
          <option value="half_day">Half Day</option>
        </select>
        <input v-model="filters.date_from" type="date" class="border border-gray-300 rounded-lg px-3 py-2 text-sm" />
        <span class="text-gray-400 text-sm">to</span>
        <input v-model="filters.date_to" type="date" class="border border-gray-300 rounded-lg px-3 py-2 text-sm" />
        <button @click="fetchData" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700">Filter</button>
        <button v-if="hasFilters" @click="clearFilters" class="text-sm text-gray-500 hover:text-gray-700 border border-gray-300 rounded-lg px-3 py-2 bg-white">Clear</button>
      </div>
      <button @click="openForm()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700">+ Mark Attendance</button>
    </div>

    <div v-if="loading" class="flex justify-center py-20">
      <div class="w-10 h-10 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
    </div>
    <div v-else-if="error" class="bg-red-50 text-red-700 p-4 rounded-lg">{{ error }}</div>
    <div v-else-if="!items.length" class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center text-gray-500">No attendance records found.</div>
    <div v-else class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-600">
          <tr>
            <th class="text-left px-4 py-3 font-medium">Employee</th>
            <th class="text-left px-4 py-3 font-medium">Date</th>
            <th class="text-left px-4 py-3 font-medium">Check In</th>
            <th class="text-left px-4 py-3 font-medium">Check Out</th>
            <th class="text-left px-4 py-3 font-medium">Status</th>
            <th class="text-left px-4 py-3 font-medium">Notes</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="item in items" :key="item.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 font-medium">{{ item.employee?.name || '-' }}</td>
            <td class="px-4 py-3">{{ formatDate(item.date) }}</td>
            <td class="px-4 py-3">{{ item.check_in || '-' }}</td>
            <td class="px-4 py-3">{{ item.check_out || '-' }}</td>
            <td class="px-4 py-3">
              <span :class="statusClass(item.status)" class="px-2 py-1 rounded-full text-xs font-medium">{{ humanize(item.status) }}</span>
            </td>
            <td class="px-4 py-3 text-gray-500">{{ item.notes || '-' }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="formVisible" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="formVisible = false">
      <div class="bg-white rounded-xl shadow-2xl p-6 max-w-lg w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Mark Attendance</h3>
        <div v-if="formError" class="mb-4 bg-red-50 text-red-700 px-4 py-3 rounded-lg text-sm">{{ formError }}</div>
        <form @submit.prevent="handleSave" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Employee</label>
            <select v-model="form.employee_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm">
              <option :value="null" disabled>Select employee</option>
              <option v-for="employee in employees" :key="employee.id" :value="employee.id">{{ employee.name }} ({{ employee.employee_id }})</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
            <input v-model="form.date" type="date" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm" />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Check In</label>
              <input v-model="form.check_in" type="time" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Check Out</label>
              <input v-model="form.check_out" type="time" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm" />
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select v-model="form.status" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm">
              <option value="present">Present</option>
              <option value="absent">Absent</option>
              <option value="late">Late</option>
              <option value="half_day">Half Day</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <input v-model="form.notes" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm" />
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
import { ref, reactive, computed, onMounted } from 'vue'
import axios from '@/utils/axios'

const items = ref([])
const employees = ref([])
const loading = ref(true)
const error = ref('')
const formVisible = ref(false)
const saving = ref(false)
const formError = ref('')
const form = reactive({ employee_id: null, date: '', check_in: '', check_out: '', status: 'present', notes: '' })
const filters = reactive({ search: '', status: '', date_from: '', date_to: '' })
const hasFilters = computed(() => filters.search || filters.status || filters.date_from || filters.date_to)

function statusClass(status) {
  return {
    present: 'bg-green-100 text-green-700',
    absent: 'bg-red-100 text-red-700',
    late: 'bg-yellow-100 text-yellow-700',
    half_day: 'bg-orange-100 text-orange-700',
  }[status] || 'bg-gray-100 text-gray-600'
}

async function fetchData() {
  loading.value = true
  try {
    const { data } = await axios.get('/attendance', {
      params: {
        search: filters.search || undefined,
        status: filters.status || undefined,
        date_from: filters.date_from || undefined,
        date_to: filters.date_to || undefined,
      },
    })
    items.value = data.data || data
  } catch (e) { error.value = 'Failed to load attendance.' }
  finally { loading.value = false }
}

function clearFilters() { filters.search = ''; filters.status = ''; filters.date_from = ''; filters.date_to = ''; fetchData() }

async function fetchEmployees() {
  try {
    const { data } = await axios.get('/employees')
    employees.value = data.data || data
  } catch (e) { employees.value = [] }
}

function openForm() {
  form.employee_id = null; form.date = new Date().toISOString().slice(0, 10)
  form.check_in = ''; form.check_out = ''; form.status = 'present'; form.notes = ''
  formVisible.value = true; formError.value = ''
}

async function handleSave() {
  saving.value = true; formError.value = ''
  try {
    const { data } = await axios.post('/attendance', form)
    items.value.unshift(data.data || data)
    formVisible.value = false
  } catch (e) { formError.value = e.response?.data?.message || Object.values(e.response?.data?.errors || {}).flat().join(', ') || 'Failed to save.' }
  finally { saving.value = false }
}

onMounted(() => { fetchData(); fetchEmployees() })
</script>
