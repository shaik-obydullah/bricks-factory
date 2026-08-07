<template>
  <div class="flex h-screen bg-gray-100">
    <!-- Mobile overlay -->
    <div
      v-if="mobileSidebarOpen"
      class="fixed inset-0 z-30 bg-black/50 lg:hidden"
      @click="mobileSidebarOpen = false"
    ></div>

    <!-- Sidebar -->
    <aside
      class="fixed inset-y-0 left-0 z-40 w-64 bg-white border-r border-gray-200 flex flex-col transition-transform duration-200 lg:static lg:translate-x-0"
      :class="mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    >
      <!-- Logo -->
      <div class="flex items-center gap-3 px-6 h-16 border-b border-gray-200">
        <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center">
          <span class="text-white font-bold text-lg">B</span>
        </div>
        <span class="font-semibold text-lg text-gray-800">Bricks Factory</span>
      </div>

      <!-- Nav -->
      <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
        <NavItem to="/dashboard" label="Dashboard" :icon="icons.dashboard" />

        <div class="pt-4 pb-1">
          <p class="px-4 text-xs font-semibold uppercase tracking-wider text-gray-400">Production</p>
        </div>
        <NavItem to="/production/orders" label="Orders" :icon="icons.orders" />
        <NavItem to="/production/batches" label="Batches" :icon="icons.batches" />
        <NavItem to="/production/machines" label="Machines" :icon="icons.machines" />
        <NavItem to="/production/targets" label="Targets" :icon="icons.targets" />

        <div class="pt-4 pb-1">
          <p class="px-4 text-xs font-semibold uppercase tracking-wider text-gray-400">Inventory</p>
        </div>
        <NavItem to="/inventory/products" label="Products" :icon="icons.products" />
        <NavItem to="/inventory/raw-materials" label="Raw Materials" :icon="icons.materials" />
        <NavItem to="/inventory/stock-movements" label="Stock Movements" :icon="icons.movements" />
        <NavItem to="/inventory/stock-alerts" label="Stock Alerts" :icon="icons.alerts" />
        <NavItem to="/inventory/warehouses" label="Warehouses" :icon="icons.warehouses" />
        <NavItem to="/inventory/categories" label="Categories" :icon="icons.categories" />

        <div class="pt-4 pb-1">
          <p class="px-4 text-xs font-semibold uppercase tracking-wider text-gray-400">Quality</p>
        </div>
        <NavItem to="/quality/checks" label="Checks" :icon="icons.checks" />
        <NavItem to="/quality/defects" label="Defects" :icon="icons.defects" />

        <div class="pt-4 pb-1">
          <p class="px-4 text-xs font-semibold uppercase tracking-wider text-gray-400">Sales</p>
        </div>
        <NavItem to="/sales/customers" label="Customers" :icon="icons.customers" />
        <NavItem to="/sales/orders" label="Orders" :icon="icons.salesOrders" />
        <NavItem to="/sales/invoices" label="Invoices" :icon="icons.invoices" />
        <NavItem to="/sales/payments" label="Payments" :icon="icons.payments" />

        <div class="pt-4 pb-1">
          <p class="px-4 text-xs font-semibold uppercase tracking-wider text-gray-400">Purchase</p>
        </div>
        <NavItem to="/purchase/suppliers" label="Suppliers" :icon="icons.suppliers" />
        <NavItem to="/purchase/orders" label="Orders" :icon="icons.purchaseOrders" />
        <NavItem to="/purchase/goods-receipts" label="Goods Receipts" :icon="icons.receipts" />

        <div class="pt-4 pb-1">
          <p class="px-4 text-xs font-semibold uppercase tracking-wider text-gray-400">Employees</p>
        </div>
        <NavItem to="/employees" label="Employees" :icon="icons.employees" />
        <NavItem to="/employees/attendance" label="Attendance" :icon="icons.attendance" />

        <div class="pt-4 pb-1">
          <p class="px-4 text-xs font-semibold uppercase tracking-wider text-gray-400">Reports</p>
        </div>
        <NavItem to="/reports/production" label="Production" :icon="icons.reports" />
        <NavItem to="/reports/inventory" label="Inventory" :icon="icons.reports" />
        <NavItem to="/reports/sales" label="Sales" :icon="icons.reports" />
        <NavItem to="/reports/quality" label="Quality" :icon="icons.reports" />

        <div class="pt-4 pb-1">
          <p class="px-4 text-xs font-semibold uppercase tracking-wider text-gray-400">System</p>
        </div>
        <NavItem to="/settings" label="Settings" :icon="icons.settings" />
      </nav>

      <!-- User info at bottom -->
      <div class="border-t border-gray-200 p-4" v-if="authStore.user">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-700 font-semibold text-sm">
            {{ authStore.user.name?.charAt(0)?.toUpperCase() || 'U' }}
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-800 truncate">{{ authStore.user.name }}</p>
            <p class="text-xs text-gray-500 truncate">{{ authStore.user.email }}</p>
          </div>
          <button @click="handleLogout" class="text-gray-400 hover:text-red-600 transition-colors" title="Logout">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
          </button>
        </div>
      </div>
    </aside>

    <!-- Main content area -->
    <div class="flex-1 flex flex-col min-w-0">
      <!-- Top navbar -->
      <header class="bg-white border-b border-gray-200 h-16 flex items-center px-4 lg:px-6 sticky top-0 z-20">
        <button @click="mobileSidebarOpen = true" class="lg:hidden text-gray-500 hover:text-gray-700 mr-3">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
        <h1 class="text-lg font-semibold text-gray-800 flex-1">{{ pageTitle }}</h1>
        <div class="flex items-center gap-4">
          <div class="relative">
            <select
              v-model="selectedWarehouse"
              class="text-sm border border-gray-300 rounded-lg px-3 py-1.5 bg-white text-gray-700 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            >
              <option value="">All Warehouses</option>
              <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
            </select>
          </div>
          <div class="relative">
            <button @click="showUserMenu = !showUserMenu" class="flex items-center gap-2 text-sm text-gray-700 hover:text-gray-900">
              <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-700 font-semibold text-sm">
                {{ authStore.user?.name?.charAt(0)?.toUpperCase() || 'U' }}
              </div>
              <span class="hidden md:inline">{{ authStore.user?.name }}</span>
            </button>
            <div v-if="showUserMenu" @click.self="showUserMenu = false" class="fixed inset-0 z-10"></div>
            <div v-if="showUserMenu" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-20">
              <router-link to="/settings" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" @click="showUserMenu = false">Settings</router-link>
              <button @click="handleLogout" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">Logout</button>
            </div>
          </div>
        </div>
      </header>

      <!-- Page content -->
      <main class="flex-1 overflow-auto p-4 lg:p-6">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import NavItem from './NavItem.vue'
import axios from '@/utils/axios'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const mobileSidebarOpen = ref(false)
const showUserMenu = ref(false)
const selectedWarehouse = ref('')
const warehouses = ref([])

const pageTitle = computed(() => {
  const name = route.name
  if (!name) return 'Dashboard'
  const titles = {
    Dashboard: 'Dashboard',
    ProductionOrders: 'Production Orders',
    ProductionOrderCreate: 'Create Production Order',
    ProductionOrderDetail: 'Production Order Detail',
    ProductionBatches: 'Production Batches',
    Machines: 'Machines',
    ProductionTargets: 'Production Targets',
    Products: 'Products',
    ProductCreate: 'Create Product',
    ProductDetail: 'Product Detail',
    RawMaterials: 'Raw Materials',
    StockMovements: 'Stock Movements',
    StockAlerts: 'Stock Alerts',
    Warehouses: 'Warehouses',
    Categories: 'Categories',
    QualityChecks: 'Quality Checks',
    Defects: 'Defects',
    Customers: 'Customers',
    SalesOrders: 'Sales Orders',
    Invoices: 'Invoices',
    Payments: 'Payments',
    Suppliers: 'Suppliers',
    PurchaseOrders: 'Purchase Orders',
    GoodsReceipts: 'Goods Receipts',
    Employees: 'Employees',
    Attendance: 'Attendance',
    ReportProduction: 'Production Report',
    ReportInventory: 'Inventory Report',
    ReportSales: 'Sales Report',
    ReportQuality: 'Quality Report',
    Settings: 'Settings',
  }
  return titles[name] || name
})

watch(selectedWarehouse, (val) => {
  localStorage.setItem('selected_warehouse', val)
})

onMounted(() => {
  selectedWarehouse.value = localStorage.getItem('selected_warehouse') || ''
  axios.get('/warehouses').then(({ data }) => {
    warehouses.value = data.data || data
  }).catch(() => {})
})

async function handleLogout() {
  showUserMenu.value = false
  await authStore.logout()
  router.push('/login')
}

const icons = {
  dashboard: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>',
  orders: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>',
  batches: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" /></svg>',
  machines: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>',
  targets: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M3 10v11m18-11v11" /></svg>',
  products: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>',
  materials: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>',
  movements: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" /></svg>',
  alerts: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" /></svg>',
  warehouses: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>',
  categories: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>',
  checks: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
  defects: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
  customers: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>',
  salesOrders: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>',
  invoices: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>',
  payments: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>',
  suppliers: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>',
  purchaseOrders: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z" /></svg>',
  receipts: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" /></svg>',
  employees: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>',
  attendance: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>',
  reports: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>',
  settings: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /></svg>',
}
</script>
