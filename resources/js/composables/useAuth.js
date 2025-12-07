import { ref, computed } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const user = ref(null)
const token = ref(localStorage.getItem('token') || null)

if (token.value) {
  axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
}

export function useAuth() {
  const router = useRouter()

  const isAuthenticated = computed(() => !!token.value)

  const login = async (credentials) => {
    try {
      const response = await axios.post('/login', credentials)
      token.value = response.data.token
      user.value = response.data.user
      localStorage.setItem('token', token.value)
      axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
      return { success: true, data: response.data }
    } catch (error) {
      return { success: false, error: error.response?.data?.message || 'Login failed' }
    }
  }

  const register = async (userData) => {
    try {
      const response = await axios.post('/register', userData)
      token.value = response.data.token
      user.value = response.data.user
      localStorage.setItem('token', token.value)
      axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
      return { success: true, data: response.data }
    } catch (error) {
      return { success: false, error: error.response?.data?.message || 'Registration failed' }
    }
  }

  const logout = async () => {
    try {
      await axios.post('/logout')
    } catch (error) {
      console.error('Logout error:', error)
    } finally {
      token.value = null
      user.value = null
      localStorage.removeItem('token')
      delete axios.defaults.headers.common['Authorization']
      router.push('/login')
    }
  }

  const fetchUser = async () => {
    try {
      const response = await axios.get('/me')
      user.value = response.data
      return { success: true, data: response.data }
    } catch (error) {
      if (error.response?.status === 401) {
        logout()
      }
      return { success: false, error: error.response?.data?.message || 'Failed to fetch user' }
    }
  }

  return {
    user,
    token,
    isAuthenticated,
    login,
    register,
    logout,
    fetchUser
  }
}

