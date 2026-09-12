<template>
  <div v-if="loading" class="flex items-center justify-center min-h-screen bg-gray-100">
    <div class="text-center">
      <div class="w-12 h-12 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin mx-auto"></div>
      <p class="mt-4 text-gray-600 text-lg">Loading...</p>
    </div>
  </div>
  <router-view v-else />
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'

const loading = ref(true)
const authStore = useAuthStore()

onMounted(async () => {
  await authStore.initAuth()
  loading.value = false
})
</script>
