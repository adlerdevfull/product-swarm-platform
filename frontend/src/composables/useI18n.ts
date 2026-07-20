import { computed, ref, type Ref } from 'vue'
import { messages, type Locale } from '../i18n/messages'

const STORAGE_KEY = 'locale'
const locale: Ref<Locale> = ref(
  (localStorage.getItem(STORAGE_KEY) as Locale) ||
    ((navigator.language || 'en').startsWith('es')
      ? 'es'
      : (navigator.language || 'en').startsWith('pt')
        ? 'pt'
        : 'en'),
)

function getByPath(obj: unknown, path: string): string {
  let cur: any = obj
  for (const p of path.split('.')) {
    if (cur == null) return path
    cur = cur[p]
  }
  return typeof cur === 'string' ? cur : path
}

export function useI18n() {
  const t = (key: string) => getByPath(messages[locale.value], key)
  const setLocale = (next: Locale) => {
    locale.value = next
    localStorage.setItem(STORAGE_KEY, next)
    document.documentElement.lang = next
  }
  const available = [
    { code: 'en' as Locale, label: 'EN' },
    { code: 'es' as Locale, label: 'ES' },
    { code: 'pt' as Locale, label: 'PT' },
  ]
  return { locale, t, setLocale, available, dict: computed(() => messages[locale.value]) }
}
