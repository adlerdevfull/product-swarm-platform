<template>
  <div class="w-full">
    <p class="text-[10px] uppercase tracking-wide text-slate-500 mb-1">{{ t('lang.label') }}</p>
    <div class="inline-flex w-full rounded-lg border border-slate-600 bg-slate-800 p-0.5">
      <button
        v-for="opt in available"
        :key="opt.code"
        type="button"
        @click="setLocale(opt.code)"
        class="flex-1 px-2 py-1.5 text-xs font-bold rounded-md transition"
        :class="locale === opt.code ? 'bg-indigo-600 text-white shadow' : 'text-slate-400 hover:text-white'"
      >
        {{ opt.label }}
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useI18n } from '../composables/useI18n'
const { locale, setLocale, available, t } = useI18n()
</script>
