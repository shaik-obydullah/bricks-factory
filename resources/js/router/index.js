import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AppLayout from '@/components/layout/AppLayout.vue'

const routes = [
  {
    path: '/',
    redirect: '/dashboard',
  },
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/pages/auth/Login.vue'),
    meta: { requiresAuth: false },
  },
  {
    path: '/',
    component: AppLayout,
    meta: { requiresAuth: true },
    children: [
      { path: 'dashboard', name: 'Dashboard', component: () => import('@/pages/dashboard/Dashboard.vue') },
      { path: 'production/orders', name: 'ProductionOrders', component: () => import('@/pages/production/ProductionOrders.vue') },
      { path: 'production/orders/create', name: 'ProductionOrderCreate', component: () => import('@/pages/production/ProductionOrderForm.vue') },
      { path: 'production/orders/:id', name: 'ProductionOrderDetail', component: () => import('@/pages/production/ProductionOrderDetail.vue') },
      { path: 'production/batches', name: 'ProductionBatches', component: () => import('@/pages/production/ProductionBatches.vue') },
      { path: 'production/machines', name: 'Machines', component: () => import('@/pages/production/Machines.vue') },
      { path: 'production/targets', name: 'ProductionTargets', component: () => import('@/pages/production/ProductionTargets.vue') },
      { path: 'inventory/products', name: 'Products', component: () => import('@/pages/inventory/Products.vue') },
      { path: 'inventory/products/create', name: 'ProductCreate', component: () => import('@/pages/inventory/ProductForm.vue') },
      { path: 'inventory/products/:id', name: 'ProductDetail', component: () => import('@/pages/inventory/ProductDetail.vue') },
      { path: 'inventory/raw-materials', name: 'RawMaterials', component: () => import('@/pages/inventory/RawMaterials.vue') },
      { path: 'inventory/stock-movements', name: 'StockMovements', component: () => import('@/pages/inventory/StockMovements.vue') },
      { path: 'inventory/stock-alerts', name: 'StockAlerts', component: () => import('@/pages/inventory/StockAlerts.vue') },
      { path: 'inventory/warehouses', name: 'Warehouses', component: () => import('@/pages/inventory/Warehouses.vue') },
      { path: 'inventory/categories', name: 'Categories', component: () => import('@/pages/inventory/Categories.vue') },
      { path: 'quality/checks', name: 'QualityChecks', component: () => import('@/pages/quality/QualityChecks.vue') },
      { path: 'quality/defects', name: 'Defects', component: () => import('@/pages/quality/Defects.vue') },
      { path: 'sales/customers', name: 'Customers', component: () => import('@/pages/sales/Customers.vue') },
      { path: 'sales/orders', name: 'SalesOrders', component: () => import('@/pages/sales/SalesOrders.vue') },
      { path: 'sales/invoices', name: 'Invoices', component: () => import('@/pages/sales/Invoices.vue') },
      { path: 'sales/payments', name: 'Payments', component: () => import('@/pages/sales/Payments.vue') },
      { path: 'purchase/suppliers', name: 'Suppliers', component: () => import('@/pages/purchase/Suppliers.vue') },
      { path: 'purchase/orders', name: 'PurchaseOrders', component: () => import('@/pages/purchase/PurchaseOrders.vue') },
      { path: 'purchase/goods-receipts', name: 'GoodsReceipts', component: () => import('@/pages/purchase/GoodsReceipts.vue') },
      { path: 'employees', name: 'Employees', component: () => import('@/pages/employees/Employees.vue') },
      { path: 'employees/attendance', name: 'Attendance', component: () => import('@/pages/employees/Attendance.vue') },
      { path: 'reports/production', name: 'ReportProduction', component: () => import('@/pages/reports/ReportProduction.vue') },
      { path: 'reports/inventory', name: 'ReportInventory', component: () => import('@/pages/reports/ReportInventory.vue') },
      { path: 'reports/sales', name: 'ReportSales', component: () => import('@/pages/reports/ReportSales.vue') },
      { path: 'reports/quality', name: 'ReportQuality', component: () => import('@/pages/reports/ReportQuality.vue') },
      { path: 'settings', name: 'Settings', component: () => import('@/pages/settings/Settings.vue') },
    ],
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login')
  } else if (to.path === '/login' && authStore.isAuthenticated) {
    next('/dashboard')
  } else {
    next()
  }
})

export default router
