<template>
  <div>
    <div class="flex justify-end mb-4">
      <button v-if="authStore.can('users.create')" @click="openForm(null)" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700">+ Add Role</button>
    </div>

    <div v-if="loading" class="flex justify-center py-20">
      <div class="w-10 h-10 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
    </div>
    <div v-else-if="error" class="bg-red-50 text-red-700 p-4 rounded-lg">{{ error }}</div>
    <div v-else-if="!items.length" class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center text-gray-500">No roles found.</div>
    <div v-else class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-600">
          <tr>
            <th class="text-left px-4 py-3 font-medium">Role</th>
            <th class="text-left px-4 py-3 font-medium">Permissions</th>
            <th class="text-left px-4 py-3 font-medium">Users</th>
            <th class="text-right px-4 py-3 font-medium">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="item in items" :key="item.id" class="hover:bg-gray-50">
            <td class="px-4 py-3">
              <span class="font-medium capitalize">{{ item.name }}</span>
            </td>
            <td class="px-4 py-3 text-gray-500">{{ item.permissions.length }} permissions</td>
            <td class="px-4 py-3 text-gray-500">{{ item.users_count ?? '-' }}</td>
            <td class="px-4 py-3 text-right">
              <button v-if="authStore.can('users.update')" @click="openForm(item)" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium mr-3">Edit</button>
              <button v-if="authStore.can('users.delete')" @click="confirmDelete(item)" class="text-red-600 hover:text-red-800 text-sm font-medium">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="formVisible" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="formVisible = false">
      <div class="bg-white rounded-xl shadow-2xl p-6 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ editingItem ? 'Edit' : 'Add' }} Role</h3>
        <div v-if="formError" class="mb-4 bg-red-50 text-red-700 px-4 py-3 rounded-lg text-sm">{{ formError }}</div>
        <form @submit.prevent="handleSave">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Role Name</label>
            <input v-model="form.name" required :disabled="!!editingItem" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm disabled:bg-gray-50" />
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Permissions</label>
            <div class="space-y-4">
              <div v-for="(perms, module) in permissionGroups" :key="module" class="border border-gray-200 rounded-lg p-3">
                <div class="flex items-center justify-between mb-2">
                  <p class="text-sm font-semibold text-gray-700 capitalize">{{ module }}</p>
                  <button type="button" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium" @click="toggleModule(module, perms)">
                    {{ moduleChecked(perms) ? 'Clear' : 'Select all' }}
                  </button>
                </div>
                <div class="flex flex-wrap gap-3">
                  <label v-for="p in perms" :key="p" class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                    <input type="checkbox" :value="p" v-model="form.permissions" class="w-4 h-4 rounded border-gray-300 text-indigo-600" />
                    {{ actionLabel(p) }}
                  </label>
                </div>
              </div>
            </div>
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
        <p class="text-gray-600 text-sm mb-4">Delete role "{{ deleteItem.name }}"? This cannot be undone.</p>
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
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const items = ref([])
const permissionGroups = ref({})
const loading = ref(true)
const error = ref('')
const formVisible = ref(false)
const editingItem = ref(null)
const saving = ref(false)
const formError = ref('')
const deleteItem = ref(null)
const deleting = ref(false)
const form = reactive({ name: '', permissions: [] })

function actionLabel(permission) {
  const action = permission.split('.').pop()
  return action.charAt(0).toUpperCase() + action.slice(1)
}

function moduleChecked(perms) {
  return perms.every(p => form.permissions.includes(p))
}

function toggleModule(module, perms) {
  if (moduleChecked(perms)) {
    form.permissions = form.permissions.filter(p => !perms.includes(p))
  } else {
    form.permissions = [...new Set([...form.permissions, ...perms])]
  }
}

async function fetchData() {
  loading.value = true
  try {
    const [{ data: roleData }, { data: groups }] = await Promise.all([
      axios.get('/roles'),
      axios.get('/permissions'),
    ])
    items.value = roleData
    permissionGroups.value = groups
  } catch (e) { error.value = 'Failed to load roles.' }
  finally { loading.value = false }
}

function openForm(item) {
  editingItem.value = item
  form.name = item?.name || ''
  form.permissions = item ? item.permissions.map(p => p.name) : []
  formVisible.value = true
  formError.value = ''
}

async function handleSave() {
  saving.value = true; formError.value = ''
  const payload = { name: form.name, permissions: form.permissions }
  try {
    if (editingItem.value) {
      const { data } = await axios.put(`/roles/${editingItem.value.id}`, payload)
      Object.assign(editingItem.value, data.data || data)
    } else {
      const { data } = await axios.post('/roles', payload)
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
    await axios.delete(`/roles/${deleteItem.value.id}`)
    items.value = items.value.filter(i => i.id !== deleteItem.value.id)
    deleteItem.value = null
  } catch (e) { error.value = e.response?.data?.message || 'Failed to delete.' }
  finally { deleting.value = false }
}

onMounted(fetchData)
</script>
