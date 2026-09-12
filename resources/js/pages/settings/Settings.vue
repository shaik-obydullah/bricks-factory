<template>
  <div>
    <div v-if="loading" class="flex justify-center py-20">
      <div class="w-10 h-10 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
    </div>
    <div v-else-if="error" class="bg-red-50 text-red-700 p-4 rounded-lg">{{ error }}</div>
    <div v-else class="space-y-6">
      <div v-for="group in GROUPS" :key="group.name" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200">
          <h3 class="font-semibold text-gray-800">{{ group.label }}</h3>
          <p v-if="group.description" class="text-sm text-gray-500 mt-0.5">{{ group.description }}</p>
        </div>
        <div class="p-5">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div v-for="key in group.keys" :key="key" :class="field(key).type === 'textarea' ? 'md:col-span-2' : ''">
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ field(key).label }}</label>
              <input
                v-if="field(key).type === 'text'"
                v-model="form[key]"
                type="text"
                :placeholder="field(key).placeholder"
                class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
              />
              <input
                v-else-if="field(key).type === 'email'"
                v-model="form[key]"
                type="email"
                :placeholder="field(key).placeholder"
                class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
              />
              <input
                v-else-if="field(key).type === 'tel'"
                v-model="form[key]"
                type="tel"
                :placeholder="field(key).placeholder"
                class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
              />
              <input
                v-else-if="field(key).type === 'number'"
                v-model.number="form[key]"
                type="number"
                :step="field(key).step"
                :min="field(key).min"
                class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
              />
              <textarea
                v-else-if="field(key).type === 'textarea'"
                v-model="form[key]"
                rows="2"
                :placeholder="field(key).placeholder"
                class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
              ></textarea>
              <select
                v-else-if="field(key).type === 'select'"
                v-model="form[key]"
                class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
              >
                <option v-for="opt in fieldOptions(key)" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
              </select>
              <div v-if="key === 'date_format'" class="mt-2 flex items-center gap-2">
                <span class="text-xs text-gray-500">Preview:</span>
                <span class="text-sm font-medium text-gray-800 bg-gray-100 rounded-lg px-3 py-1">{{ datePreview }}</span>
              </div>
              <p v-if="field(key).description" class="mt-1.5 text-xs text-gray-500">{{ field(key).description }}</p>
            </div>
          </div>
          <div class="flex items-center justify-end gap-3 mt-5 pt-4 border-t border-gray-100">
            <span v-if="saveErrorGroup === group.name && saveError" class="text-sm text-red-600">{{ saveError }}</span>
            <span v-else-if="savedGroup === group.name" class="text-sm text-green-600">Saved successfully.</span>
            <button
              v-if="authStore.can('settings.update')"
              @click="saveGroup(group)"
              :disabled="saving === group.name"
              class="px-6 py-2.5 text-sm text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 disabled:opacity-50"
            >
              {{ saving === group.name ? 'Saving...' : 'Save' }}
            </button>
          </div>
        </div>
      </div>

      <div v-if="customSettings.length" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <button @click="advancedOpen = !advancedOpen" class="w-full flex items-center justify-between px-5 py-4">
          <div class="text-left">
            <h3 class="font-semibold text-gray-800">Advanced Settings</h3>
            <p class="text-sm text-gray-500 mt-0.5">{{ customSettings.length }} custom setting{{ customSettings.length > 1 ? 's' : '' }}</p>
          </div>
          <span class="text-gray-400 text-sm font-medium">{{ advancedOpen ? 'Hide' : 'Show' }}</span>
        </button>
        <div v-if="advancedOpen" class="border-t border-gray-200">
          <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
            <p class="text-sm text-gray-500">Settings that don't belong to a built-in section.</p>
            <button v-if="authStore.can('settings.create')" @click="openForm()" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">+ Add Setting</button>
          </div>
          <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
              <tr>
                <th class="text-left px-5 py-2.5 font-medium">Key</th>
                <th class="text-left px-5 py-2.5 font-medium">Value</th>
                <th class="text-right px-5 py-2.5 font-medium">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="setting in customSettings" :key="setting.id" class="hover:bg-gray-50">
                <td class="px-5 py-2.5 font-mono text-xs">{{ setting.key }}</td>
                <td class="px-5 py-2.5">{{ setting.value || '-' }}</td>
                <td class="px-5 py-2.5 text-right">
                  <button v-if="authStore.can('settings.update')" @click="openForm(setting)" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium mr-3">Edit</button>
                  <button v-if="authStore.can('settings.delete')" @click="confirmDelete(setting)" class="text-red-600 hover:text-red-800 text-sm font-medium">Delete</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div v-if="formVisible" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="formVisible = false">
      <div class="bg-white rounded-xl shadow-2xl p-6 max-w-lg w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ editingItem ? 'Edit' : 'Add' }} Setting</h3>
        <div v-if="formError" class="mb-4 bg-red-50 text-red-700 px-4 py-3 rounded-lg text-sm">{{ formError }}</div>
        <form @submit.prevent="handleSave" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Key</label>
            <input v-model="advancedForm.key" :disabled="!!editingItem" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm disabled:bg-gray-50" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Value</label>
            <input v-model="advancedForm.value" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Group</label>
            <input v-model="advancedForm.group" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm" placeholder="general" />
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
        <p class="text-gray-600 text-sm mb-4">Delete setting "{{ deleteItem.key }}"?</p>
        <div class="flex justify-end gap-3">
          <button @click="deleteItem = null" class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded-lg">Cancel</button>
          <button @click="handleDelete" :disabled="deleting" class="px-4 py-2 text-sm text-white bg-red-600 rounded-lg disabled:opacity-50">{{ deleting ? 'Deleting...' : 'Delete' }}</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import axios from '@/utils/axios'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()

const MONTHS = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']
const DAYS = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']

const DATE_FORMATS = [
  { value: 'Y-m-d', label: 'Year-Month-Day (e.g. 2026-08-07)' },
  { value: 'd-m-Y', label: 'Day-Month-Year (e.g. 07-08-2026)' },
  { value: 'd/m/Y', label: 'Day/Month/Year (e.g. 07/08/2026)' },
  { value: 'm/d/Y', label: 'Month/Day/Year (e.g. 08/07/2026)' },
  { value: 'd M Y', label: 'Day Mon Year (e.g. 07 Aug 2026)' },
  { value: 'M d, Y', label: 'Mon Day, Year (e.g. Aug 07, 2026)' },
  { value: 'jS F Y', label: 'Day Month Year (e.g. 7th August 2026)' },
]

const FIELD_CONFIG = {
  company_name: { label: 'Company Name', type: 'text', group: 'general', placeholder: 'Bricks Factory Ltd' },
  company_address: { label: 'Company Address', type: 'textarea', group: 'general', placeholder: 'Gazaria, Munshiganj' },
  company_phone: { label: 'Company Phone', type: 'tel', group: 'general', placeholder: '01700-000000' },
  company_email: { label: 'Company Email', type: 'email', group: 'general', placeholder: 'info@company.com' },
  currency: { label: 'Currency', type: 'select', group: 'general', options: ['BDT', 'USD', 'EUR', 'GBP', 'INR'] },
  invoice_prefix: { label: 'Invoice Prefix', type: 'text', group: 'invoice', description: 'Prepended to invoice numbers.', placeholder: 'INV-' },
  vat_rate: { label: 'VAT Rate (%)', type: 'number', group: 'invoice', step: '0.01', min: '0' },
  production_target_notice: { label: 'Target Notice (days)', type: 'number', group: 'production', description: 'Days in advance to warn about upcoming production targets.', step: '1', min: '1' },
  date_format: { label: 'Date Format', type: 'select', group: 'preferences', description: 'How dates are displayed across the system.', options: DATE_FORMATS },
}

const DEFAULTS = {
  currency: 'BDT',
  date_format: 'Y-m-d',
}

const GROUPS = [
  { name: 'general', label: 'General', description: 'Basic company information shown across the system.', keys: ['company_name', 'company_address', 'company_phone', 'company_email', 'currency'] },
  { name: 'invoice', label: 'Invoicing', description: 'Defaults applied when creating invoices.', keys: ['invoice_prefix', 'vat_rate'] },
  { name: 'production', label: 'Production', description: 'Production planning preferences.', keys: ['production_target_notice'] },
  { name: 'preferences', label: 'Preferences', description: 'How dates are displayed across the system.', keys: ['date_format'] },
]

const allSettings = ref([])
const loading = ref(true)
const error = ref('')
const form = reactive({})
const saving = ref('')
const savedGroup = ref('')
const saveError = ref('')
const saveErrorGroup = ref('')
const advancedOpen = ref(false)

const formVisible = ref(false)
const editingItem = ref(null)
const formError = ref('')
const deleteItem = ref(null)
const deleting = ref(false)
const advancedForm = reactive({ key: '', value: '', group: 'general' })

const byKey = computed(() => {
  const map = {}
  for (const s of allSettings.value) map[s.key] = s
  return map
})

const customSettings = computed(() => allSettings.value.filter((s) => !FIELD_CONFIG[s.key]))

const datePreview = computed(() => formatDate(new Date(), form.date_format || DEFAULTS.date_format))

function field(key) { return FIELD_CONFIG[key] || {} }

function fieldOptions(key) {
  const opts = (field(key).options || []).map((o) => (typeof o === 'object' ? o : { value: o, label: o }))
  if (form[key] && !opts.some((o) => o.value === form[key])) opts.unshift({ value: form[key], label: form[key] })
  return opts
}

function dateSuffix(day) {
  if (day % 100 >= 11 && day % 100 <= 13) return 'th'
  switch (day % 10) {
    case 1: return 'st'
    case 2: return 'nd'
    case 3: return 'rd'
    default: return 'th'
  }
}

function formatDate(date, format) {
  const pad = (n) => String(n).padStart(2, '0')
  const tokens = {
    Y: String(date.getFullYear()),
    y: String(date.getFullYear()).slice(-2),
    m: pad(date.getMonth() + 1),
    n: String(date.getMonth() + 1),
    d: pad(date.getDate()),
    j: String(date.getDate()),
    S: dateSuffix(date.getDate()),
    F: MONTHS[date.getMonth()],
    M: MONTHS[date.getMonth()].slice(0, 3),
    l: DAYS[date.getDay()],
    D: DAYS[date.getDay()].slice(0, 3),
  }
  return format.replace(/[YymndjSFMlD]/g, (t) => tokens[t] || t)
}

async function fetchData() {
  loading.value = true
  try {
    const { data } = await axios.get('/settings')
    allSettings.value = Object.values(data).flat()
    for (const key of Object.keys(FIELD_CONFIG)) {
      form[key] = byKey.value[key]?.value ?? DEFAULTS[key] ?? ''
    }
  } catch (e) { error.value = 'Failed to load settings.' }
  finally { loading.value = false }
}

async function saveGroup(group) {
  saving.value = group.name
  saveError.value = ''
  saveErrorGroup.value = ''
  try {
    await Promise.all(group.keys.map(async (key) => {
      const setting = byKey.value[key]
      const payload = { value: form[key] ?? '', group: FIELD_CONFIG[key].group }
      if (setting) {
        await axios.put(`/settings/${setting.id}`, payload)
      } else {
        await axios.post('/settings', { key, ...payload })
      }
    }))
    savedGroup.value = group.name
    if (group.name === 'preferences' && form.date_format) {
      localStorage.setItem('date_format', form.date_format)
    }
    setTimeout(() => { if (savedGroup.value === group.name) savedGroup.value = '' }, 2500)
    await fetchData()
  } catch (e) {
    saveError.value = e.response?.data?.message || `Failed to save ${group.label} settings.`
    saveErrorGroup.value = group.name
  } finally { saving.value = '' }
}

function openForm(setting) {
  editingItem.value = setting || null
  advancedForm.key = setting?.key || ''
  advancedForm.value = setting?.value || ''
  advancedForm.group = setting?.group || 'general'
  formVisible.value = true
  formError.value = ''
}

async function handleSave() {
  saving.value = true; formError.value = ''
  try {
    if (editingItem.value) {
      await axios.put(`/settings/${editingItem.value.id}`, { value: advancedForm.value, group: advancedForm.group })
    } else {
      await axios.post('/settings', advancedForm)
    }
    formVisible.value = false
    fetchData()
  } catch (e) { formError.value = e.response?.data?.message || 'Failed to save.' }
  finally { saving.value = false }
}

function confirmDelete(setting) { deleteItem.value = setting }
async function handleDelete() {
  deleting.value = true
  try {
    await axios.delete(`/settings/${deleteItem.value.id}`)
    deleteItem.value = null
    fetchData()
  } catch (e) { error.value = 'Failed to delete.' }
  finally { deleting.value = false }
}

onMounted(fetchData)
</script>
