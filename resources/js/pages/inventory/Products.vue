<template>
  <div>
    <div class="flex items-center justify-between mb-4">
      <div>
        <input v-model="search" @input="fetchData" placeholder="Search products..." class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-64 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
      </div>
      <router-link to="/inventory/products/create" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">+ Add Product</router-link>
    </div>

    <div v-if="loading" class="flex justify-center py-20">
      <div class="w-10 h-10 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
    </div>
    <div v-else-if="error" class="bg-red-50 text-red-700 p-4 rounded-lg">{{ error }}</div>
    <div v-else-if="!items.length" class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center text-gray-500">No products found.</div>
    <div v-else class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-600">
          <tr>
            <th class="text-left px-4 py-3 font-medium">Name</th>
            <th class="text-left px-4 py-3 font-medium">SKU</th>
            <th class="text-left px-4 py-3 font-medium">Category</th>
            <th class="text-left px-4 py-3 font-medium">Type</th>
            <th class="text-left px-4 py-3 font-medium">Stock</th>
            <th class="text-right px-4 py-3 font-medium">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="item in items" :key="item.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 font-medium">{{ item.name }}</td>
            <td class="px-4 py-3 font-mono text-xs">{{ item.code || '-' }}</td>
            <td class="px-4 py-3">{{ item.category?.name || '-' }}</td>
            <td class="px-4 py-3 capitalize">{{ item.type || '-' }}</td>
            <td class="px-4 py-3">
              <span v-if="appStore.selectedWarehouse">
                {{ warehouseStock(item) }}
              </span>
              <span v-else>{{ totalStock(item) }}</span>
              <span v-if="warehousesFor(item).length" class="ml-1 text-xs text-gray-400">/ {{ warehousesFor(item).length }} wh</span>
            </td>
            <td class="px-4 py-3 text-right">
              <router-link :to="`/inventory/products/${item.id}`" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium mr-3">View</router-link>
              <button @click="confirmDelete(item)" class="text-red-600 hover:text-red-800 text-sm font-medium">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
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
import { ref, onMounted, watch } from 'vue'
import axios from '@/utils/axios'
import { useAppStore } from '@/stores/app'

const appStore = useAppStore()
const items = ref([])
const loading = ref(true)
const error = ref('')
const search = ref('')
const deleteItem = ref(null)
const deleting = ref(false)

async function fetchData() {
  loading.value = true
  error.value = ''
  try {
    const params = {}
    if (search.value) params.search = search.value
    if (appStore.selectedWarehouse) params.warehouse_id = appStore.selectedWarehouse
    const { data } = await axios.get('/products', { params })
    items.value = data.data || data
  } catch (e) { error.value = 'Failed to load products.' }
  finally { loading.value = false }
}

watch(() => appStore.selectedWarehouse, fetchData)

function confirmDelete(item) { deleteItem.value = item }

function totalStock(item) {
  return (item.product_stocks || []).reduce((sum, s) => sum + Number(s.quantity || 0), 0)
}

function warehouseStock(item) {
  return totalStock(item)
}

function warehousesFor(item) {
  return (item.product_stocks || []).filter(s => Number(s.quantity || 0) > 0)
}

async function handleDelete() {
  deleting.value = true
  try {
    await axios.delete(`/products/${deleteItem.value.id}`)
    items.value = items.value.filter(i => i.id !== deleteItem.value.id)
    deleteItem.value = null
  } catch (e) { error.value = 'Failed to delete.' }
  finally { deleting.value = false }
}

onMounted(fetchData)
</script>
