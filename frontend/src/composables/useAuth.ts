import { ref } from 'vue'
import api from '../services/api'

const user = ref<{ id: number; email: string; name: string; roles: string[] } | null>(null)

export function useAuth() {
  const login = async (email: string, password: string) => {
    const { data } = await api.post('/api/v1/auth/login', { email, password })
    localStorage.setItem('token', data.token)
    await me()
  }

  const me = async () => {
    const { data } = await api.get('/api/v1/auth/me')
    user.value = data.data
    return user.value
  }

  const logout = () => {
    localStorage.removeItem('token')
    user.value = null
  }

  return { user, login, me, logout }
}
