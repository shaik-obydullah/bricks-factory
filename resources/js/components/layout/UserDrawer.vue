<template>
  <teleport to="body">
    <transition name="fade">
      <div v-if="open" class="fixed inset-0 z-40 bg-black/40" @click="close"></div>
    </transition>

    <transition name="slide">
      <div v-if="open" class="fixed inset-y-0 right-0 z-50 w-96 max-w-full bg-white shadow-2xl flex flex-col">
        <div class="px-6 py-5 border-b border-gray-200 bg-gray-50 flex items-start justify-between">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-700 font-bold text-lg">
              {{ authStore.user?.name?.charAt(0)?.toUpperCase() || 'U' }}
            </div>
            <div>
              <p class="font-semibold text-gray-800">{{ authStore.user?.name || 'User' }}</p>
              <p class="text-sm text-gray-500">{{ authStore.user?.email }}</p>
            </div>
          </div>
          <button @click="close" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
          </button>
        </div>

        <div class="flex-1 overflow-y-auto px-6 py-5">
          <div v-if="settingsLoading" class="flex justify-center py-10">
            <div class="w-8 h-8 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
          </div>
          <div v-else-if="settingsError" class="bg-red-50 text-red-700 p-4 rounded-lg text-sm">{{ settingsError }}</div>
          <div v-else-if="!groups.length" class="text-gray-500 text-sm text-center py-10">No settings found.</div>
          <div v-else class="space-y-5">
            <div v-for="group in groups" :key="group.name">
              <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">{{ group.name || 'General' }}</h4>
              <div class="border border-gray-200 rounded-lg divide-y divide-gray-100 overflow-hidden">
                <div v-for="setting in group.items" :key="setting.id" class="flex items-center justify-between gap-3 px-4 py-2.5">
                  <span class="font-mono text-xs text-gray-600 truncate">{{ setting.key }}</span>
                  <span class="text-sm text-gray-800 font-medium truncate">{{ setting.value || '-' }}</span>
                </div>
              </div>
            </div>
            <router-link to="/settings" class="block text-center text-sm text-indigo-600 hover:text-indigo-800 font-medium mt-2" @click="close">
              Manage all settings
            </router-link>
          </div>
        </div>

        <div class="px-6 py-4 border-t border-gray-200 flex gap-3">
          <router-link to="/settings" @click="close" class="flex-1 text-center px-4 py-2.5 text-sm text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 font-medium">
            Settings
          </router-link>
          <button @click="handleLogout" class="flex-1 px-4 py-2.5 text-sm text-white bg-red-600 rounded-lg hover:bg-red-700 font-medium">
            Logout
          </button>
        </div>
      </div>
    </transition>
  </teleport>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import axios from '@/utils/axios'

const props = defineProps({ open: { type: Boolean, default: false } })
const emit = defineEmits(['close'])

const router = useRouter()
const authStore = useAuthStore()

const allSettings = ref({})
const settingsLoading = ref(false)
const settingsError = ref('')

const groups = computed(() => {
  return Object.entries(allSettings.value).map(([name, items]) => ({ name, items: Array.isArray(items) ? items : [items] }))
})

async function fetchSettings() {
  settingsLoading.value = true
  settingsError.value = ''
  try {
    const { data } = await axios.get('/settings')
    allSettings.value = data
  } catch (e) {
    settingsError.value = 'Failed to load settings.'
  } finally {
    settingsLoading.value = false
  }
}

function close() {
  emit('close')
}

async function handleLogout() {
  emit('close')
  await authStore.logout()
  router.push('/login')
}

watch(() => props.open, (open) => {
  if (open) fetchSettings()
})
</script>

<style scoped>
.slide-enter-active,
.slide-leave-active {
  transition: transform 0.25s ease;
}
.slide-enter-from,
.slide-leave-to {
  transform: translateX(100%);
}
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.25s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
