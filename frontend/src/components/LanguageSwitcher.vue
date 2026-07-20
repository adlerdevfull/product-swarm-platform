<template>
  <div class="inline-flex rounded-lg border border-slate-600 bg-slate-800 p-0.5">
    <button
      v-for="opt in available"
      :key="opt.code"
      type="button"
      @click="setLocale(opt.code)"
      class="px-2.5 py-1 text-xs font-semibold rounded-md transition"
      :class="locale === opt.code ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-white'"
    >
      {{ opt.label }}
    </button>
  </div>
</template>

<script setup lang="ts">
import { useI18n } from '../composables/useI18n'
const { locale, setLocale, available } = useI18n()
</script>
