import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from '@/utils/axios'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('token'))
  const loading = ref(false)

  const isAuthenticated = computed(() => !!token.value)
  const isAdmin = computed(() => user.value?.role === 'admin')
  const userRole = computed(() => user.value?.role || null)
  const userRoles = computed(() => user.value?.roles || [])
  const userPermissions = computed(() => user.value?.permissions || [])

  function can(permission) {
    if (!permission) return true
    if (isAdmin.value) return true
    return userPermissions.value.includes(permission)
  }

  async function login(email, password) {
    const { data } = await axios.post('/login', { email, password })
    token.value = data.token
    localStorage.setItem('token', data.token)
    await fetchUser()
  }

  async function logout() {
    try {
      await axios.post('/logout')
    } catch (e) {
    }
    token.value = null
    user.value = null
    localStorage.removeItem('token')
  }

  async function fetchUser() {
    if (!token.value) return
    try {
      const { data } = await axios.get('/user')
      user.value = data
    } catch (e) {
      token.value = null
      user.value = null
      localStorage.removeItem('token')
    }
  }

  async function initAuth() {
    loading.value = true
    if (token.value) {
      await fetchUser()
    }
    loading.value = false
  }

  return { user, token, loading, isAuthenticated, isAdmin, userRole, userRoles, userPermissions, can, login, logout, fetchUser, initAuth }
})
