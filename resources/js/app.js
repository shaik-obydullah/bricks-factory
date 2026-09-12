import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from './router'
import App from './App.vue'
import { humanize, formatDate } from './utils/format'
import axios from '@/utils/axios'

const app = createApp(App)
app.config.globalProperties.humanize = humanize
app.config.globalProperties.formatDate = formatDate
app.use(createPinia())
app.use(router)

async function loadDateFormat() {
  if (!localStorage.getItem('token')) return
  try {
    const { data } = await axios.get('/settings')
    const settings = Object.values(data).flat()
    const fmt = settings.find((s) => s.key === 'date_format')
    if (fmt?.value) localStorage.setItem('date_format', fmt.value)
  } catch (e) {}
}

router.isReady().then(async () => {
  await loadDateFormat()
  app.mount('#app')
})
