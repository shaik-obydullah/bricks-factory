<template>
  <div>
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-lg font-semibold text-gray-800">Settings</h2>
      <button @click="openForm()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700">+ Add Setting</button>
    </div>

    <div v-if="loading" class="flex justify-center py-20">
      <div class="w-10 h-10 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
    </div>
    <div v-else-if="error" class="bg-red-50 text-red-700 p-4 rounded-lg">{{ error }}</div>
    <div v-else-if="!groups.length" class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center text-gray-500">No settings found.</div>
    <div v-else class="space-y-6">
      <div v-for="group in groups" :key="group.name" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
          <h3 class="font-semibold text-gray-800 text-sm capitalize">{{ group.name || 'General' }}</h3>
        </div>
        <table class="w-full text-sm">
          <thead class="text-gray-500">
            <tr>
              <th class="text-left px-4 py-2 font-medium">Key</th>
              <th class="text-left px-4 py-2 font-medium">Value</th>
              <th class="text-right px-4 py-2 font-medium">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="setting in group.items" :key="setting.id" class="hover:bg-gray-50">
              <td class="px-4 py-2.5 font-mono text-xs">{{ setting.key }}</td>
              <td class="px-4 py-2.5">{{ setting.value || '-' }}</td>
              <td class="px-4 py-2.5 text-right">
                <button @click="openForm(setting)" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium mr-3">Edit</button>
                <button @click="confirmDelete(setting)" class="text-red-600 hover:text-red-800 text-sm font-medium">Delete</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="formVisible" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="formVisible = false">
      <div class="bg-white rounded-xl shadow-2xl p-6 max-w-lg w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ editingItem ? 'Edit' : 'Add' }} Setting</h3>
        <div v-if="formError" class="mb-4 bg-red-50 text-red-700 px-4 py-3 rounded-lg text-sm">{{ formError }}</div>
        <form @submit.prevent="handleSave" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Key</label>
            <input v-model="form.key" :disabled="!!editingItem" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm disabled:bg-gray-50" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Value</label>
            <input v-model="form.value" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Group</label>
            <input v-model="form.group" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm" placeholder="general" />
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
        <p class="text-gray-600 text-sm mb-4">Delete setting "{{ deleteItem.key }}"?</p>
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

const allSettings = ref({})
const loading = ref(true)
const error = ref('')
const formVisible = ref(false)
const editingItem = ref(null)
const saving = ref(false)
const formError = ref('')
const deleteItem = ref(null)
const deleting = ref(false)
const form = reactive({ key: '', value: '', group: 'general' })

const groups = computed(() => {
  return Object.entries(allSettings.value).map(([name, items]) => ({ name, items: Array.isArray(items) ? items : [items] }))
})

async function fetchData() {
  loading.value = true
  try {
    const { data } = await axios.get('/settings')
    allSettings.value = data
  } catch (e) { error.value = 'Failed to load settings.' }
  finally { loading.value = false }
}

function openForm(setting) {
  editingItem.value = setting || null
  form.key = setting?.key || ''
  form.value = setting?.value || ''
  form.group = setting?.group || 'general'
  formVisible.value = true
  formError.value = ''
}

async function handleSave() {
  saving.value = true; formError.value = ''
  try {
    if (editingItem.value) {
      await axios.put(`/settings/${editingItem.value.id}`, { value: form.value, group: form.group })
    } else {
      await axios.post('/settings', form)
    }
    formVisible.value = false
    fetchData()
  } catch (e) { formError.value = e.response?.data?.message || 'Failed to save.' }
  finally { saving.value = false }
}

function confirmDelete(setting) { deleteItem.value = setting }
async function handleDelete() {
  deleting.value = true
  try {
    await axios.delete(`/settings/${deleteItem.value.id}`)
    deleteItem.value = null
    fetchData()
  } catch (e) { error.value = 'Failed to delete.' }
  finally { deleting.value = false }
}

onMounted(fetchData)
</script>
