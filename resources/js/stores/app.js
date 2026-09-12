import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useAppStore = defineStore('app', () => {
  const sidebarCollapsed = ref(false)
  const notifications = ref([])
  const selectedWarehouse = ref(localStorage.getItem('selected_warehouse') || '')

  function toggleSidebar() {
    sidebarCollapsed.value = !sidebarCollapsed.value
  }

  function setSelectedWarehouse(id) {
    selectedWarehouse.value = id || ''
    localStorage.setItem('selected_warehouse', selectedWarehouse.value)
  }

  function addNotification(notification) {
    notifications.value.push({ id: Date.now(), ...notification })
  }

  function removeNotification(id) {
    notifications.value = notifications.value.filter((n) => n.id !== id)
  }

  return {
    sidebarCollapsed,
    notifications,
    selectedWarehouse,
    toggleSidebar,
    setSelectedWarehouse,
    addNotification,
    removeNotification,
  }
})
