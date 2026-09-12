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
  { path: '/production', redirect: '/production/orders' },
  { path: '/inventory', redirect: '/inventory/products' },
  { path: '/sales', redirect: '/sales/orders' },
  { path: '/purchase', redirect: '/purchase/orders' },
  { path: '/quality', redirect: '/quality/checks' },
  { path: '/reports', redirect: '/reports/production' },
  { path: '/:pathMatch(.*)*', redirect: '/dashboard' },
  {
    path: '/',
    component: AppLayout,
    meta: { requiresAuth: true },
    children: [
      { path: 'dashboard', name: 'Dashboard', component: () => import('@/pages/dashboard/Dashboard.vue'), meta: { permission: 'dashboard.view' } },
      { path: 'production/orders', name: 'ProductionOrders', component: () => import('@/pages/production/ProductionOrders.vue'), meta: { permission: 'production.view' } },
      { path: 'production/orders/create', name: 'ProductionOrderCreate', component: () => import('@/pages/production/ProductionOrderForm.vue'), meta: { permission: 'production.create' } },
      { path: 'production/orders/:id', name: 'ProductionOrderDetail', component: () => import('@/pages/production/ProductionOrderDetail.vue'), meta: { permission: 'production.view' } },
      { path: 'production/batches', name: 'ProductionBatches', component: () => import('@/pages/production/ProductionBatches.vue'), meta: { permission: 'production.view' } },
      { path: 'production/machines', name: 'Machines', component: () => import('@/pages/production/Machines.vue'), meta: { permission: 'production.view' } },
      { path: 'production/targets', name: 'ProductionTargets', component: () => import('@/pages/production/ProductionTargets.vue'), meta: { permission: 'production.view' } },
      { path: 'inventory/products', name: 'Products', component: () => import('@/pages/inventory/Products.vue'), meta: { permission: 'inventory.view' } },
      { path: 'inventory/products/create', name: 'ProductCreate', component: () => import('@/pages/inventory/ProductForm.vue'), meta: { permission: 'inventory.create' } },
      { path: 'inventory/products/:id', name: 'ProductDetail', component: () => import('@/pages/inventory/ProductDetail.vue'), meta: { permission: 'inventory.view' } },
      { path: 'inventory/raw-materials', name: 'RawMaterials', component: () => import('@/pages/inventory/RawMaterials.vue'), meta: { permission: 'inventory.view' } },
      { path: 'inventory/stock-movements', name: 'StockMovements', component: () => import('@/pages/inventory/StockMovements.vue'), meta: { permission: 'inventory.view' } },
      { path: 'inventory/stock-alerts', name: 'StockAlerts', component: () => import('@/pages/inventory/StockAlerts.vue'), meta: { permission: 'inventory.view' } },
      { path: 'inventory/warehouses', name: 'Warehouses', component: () => import('@/pages/inventory/Warehouses.vue'), meta: { permission: 'inventory.view' } },
      { path: 'inventory/categories', name: 'Categories', component: () => import('@/pages/inventory/Categories.vue'), meta: { permission: 'inventory.view' } },
      { path: 'quality/checks', name: 'QualityChecks', component: () => import('@/pages/quality/QualityChecks.vue'), meta: { permission: 'quality.view' } },
      { path: 'quality/defects', name: 'Defects', component: () => import('@/pages/quality/Defects.vue'), meta: { permission: 'quality.view' } },
      { path: 'sales/customers', name: 'Customers', component: () => import('@/pages/sales/Customers.vue'), meta: { permission: 'sales.view' } },
      { path: 'sales/orders', name: 'SalesOrders', component: () => import('@/pages/sales/SalesOrders.vue'), meta: { permission: 'sales.view' } },
      { path: 'sales/invoices', name: 'Invoices', component: () => import('@/pages/sales/Invoices.vue'), meta: { permission: 'sales.view' } },
      { path: 'sales/payments', name: 'Payments', component: () => import('@/pages/sales/Payments.vue'), meta: { permission: 'sales.view' } },
      { path: 'purchase/suppliers', name: 'Suppliers', component: () => import('@/pages/purchase/Suppliers.vue'), meta: { permission: 'purchase.view' } },
      { path: 'purchase/orders', name: 'PurchaseOrders', component: () => import('@/pages/purchase/PurchaseOrders.vue'), meta: { permission: 'purchase.view' } },
      { path: 'purchase/goods-receipts', name: 'GoodsReceipts', component: () => import('@/pages/purchase/GoodsReceipts.vue'), meta: { permission: 'purchase.view' } },
      { path: 'employees', name: 'Employees', component: () => import('@/pages/employees/Employees.vue'), meta: { permission: 'employees.view' } },
      { path: 'employees/attendance', name: 'Attendance', component: () => import('@/pages/employees/Attendance.vue'), meta: { permission: 'employees.view' } },
      { path: 'reports/production', name: 'ReportProduction', component: () => import('@/pages/reports/ReportProduction.vue'), meta: { permission: 'reports.view' } },
      { path: 'reports/inventory', name: 'ReportInventory', component: () => import('@/pages/reports/ReportInventory.vue'), meta: { permission: 'reports.view' } },
      { path: 'reports/sales', name: 'ReportSales', component: () => import('@/pages/reports/ReportSales.vue'), meta: { permission: 'reports.view' } },
      { path: 'reports/quality', name: 'ReportQuality', component: () => import('@/pages/reports/ReportQuality.vue'), meta: { permission: 'reports.view' } },
      { path: 'settings', name: 'Settings', component: () => import('@/pages/settings/Settings.vue'), meta: { permission: 'settings.view' } },
      { path: 'settings/users', name: 'Users', component: () => import('@/pages/settings/Users.vue'), meta: { permission: 'users.view' } },
      { path: 'settings/roles', name: 'Roles', component: () => import('@/pages/settings/Roles.vue'), meta: { permission: 'users.view' } },
    ],
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach(async (to) => {
  const authStore = useAuthStore()
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return '/login'
  }
  if (to.path === '/login' && authStore.isAuthenticated) {
    return '/dashboard'
  }
  if (authStore.isAuthenticated && !authStore.user) {
    await authStore.initAuth()
  }
  if (to.meta.permission && !authStore.can(to.meta.permission)) {
    return '/dashboard'
  }
  return true
})

export default router
