<template>
  <router-link
    :to="to"
    class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors"
    :class="isActive ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'"
  >
    <span v-if="icon" v-html="icon" class="w-5 h-5 shrink-0"></span>
    <span class="truncate">{{ label }}</span>
    <span
      v-if="count !== undefined && count !== null"
      class="ml-auto bg-indigo-100 text-indigo-700 text-xs font-semibold px-2 py-0.5 rounded-full"
    >
      {{ count }}
    </span>
  </router-link>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const props = defineProps({
  to: { type: String, required: true },
  label: { type: String, required: true },
  icon: { type: String, default: '' },
  count: { type: Number, default: null },
})

const route = useRoute()
const isActive = computed(() => route.path.startsWith(props.to))
</script>
