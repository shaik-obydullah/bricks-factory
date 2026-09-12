<template>
  <div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
      <h2 class="text-xl font-semibold text-gray-800 mb-6">{{ isEdit ? 'Edit' : 'Create' }} Product</h2>

      <div v-if="error" class="mb-4 bg-red-50 text-red-700 px-4 py-3 rounded-lg text-sm">{{ error }}</div>

      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
          <input v-model="form.name" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
          <input v-model="form.sku" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
          <select v-model="form.type" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm">
            <option value="">Select Type</option>
            <option value="brick">Brick</option>
            <option value="block">Block</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
          <select v-model="form.category_id" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm">
            <option value="">Select Category</option>
            <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Unit</label>
          <input v-model="form.unit" placeholder="e.g. pcs" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
          <textarea v-model="form.description" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm"></textarea>
        </div>
        <div class="flex justify-end gap-3 pt-2">
          <router-link to="/inventory/products" class="px-4 py-2.5 text-sm text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Cancel</router-link>
          <button type="submit" :disabled="saving" class="px-6 py-2.5 text-sm text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 disabled:opacity-50 flex items-center gap-2">
            <div v-if="saving" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
            {{ saving ? 'Saving...' : 'Save' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from '@/utils/axios'

const router = useRouter()
const isEdit = ref(false)
const saving = ref(false)
const error = ref('')
const categories = ref([])
const form = reactive({ name: '', sku: '', type: '', category_id: '', unit: '', description: '' })

onMounted(async () => {
  try {
    const { data } = await axios.get('/categories')
    categories.value = data.data || data
  } catch (e) { error.value = 'Failed to load categories.' }
})

async function handleSubmit() {
  saving.value = true; error.value = ''
  try {
    await axios.post('/products', form)
    router.push('/inventory/products')
  } catch (e) { error.value = e.response?.data?.message || 'Failed to save product.' }
  finally { saving.value = false }
}
</script>
