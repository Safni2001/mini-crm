import axios from 'axios'

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api'

// Offline detection
let isOnline = navigator.onLine
const offlineQueue = []

window.addEventListener('online', () => {
  isOnline = true
  console.log('🟢 Back online - processing queued requests')
  // Process queued requests when back online
  while (offlineQueue.length > 0) {
    const { resolve, config } = offlineQueue.shift()
    api(config).then(resolve).catch(console.error)
  }
})

window.addEventListener('offline', () => {
  isOnline = false
  console.log('🔴 Gone offline')
})

const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  timeout: 10000
})

// Request interceptor for auth token and logging
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }

    // Request logging
    if (import.meta.env.VITE_NODE_ENV !== 'production') {
      console.log(`📤 ${config.method?.toUpperCase()} ${config.url}`, {
        data: config.data,
        params: config.params
      })
    }

    // Offline handling
    if (!isOnline && config.method?.toLowerCase() !== 'get') {
      return new Promise((resolve) => {
        offlineQueue.push({ resolve, config })
      })
    }

    return config
  },
  (error) => {
    console.error('📤❌ Request error:', error)
    return Promise.reject(error)
  }
)

// Response interceptor for logging and error handling
api.interceptors.response.use(
  (response) => {
    // Response logging
    if (import.meta.env.VITE_NODE_ENV !== 'production') {
      console.log(`📥 ${response.status} ${response.config.url}`, response.data)
    }
    return response
  },
  (error) => {
    // Response error logging
    if (import.meta.env.VITE_NODE_ENV !== 'production') {
      console.error(`📥❌ ${error.response?.status || 'Network'} ${error.config?.url}`, {
        message: error.message,
        response: error.response?.data
      })
    }

    // Handle auth errors
    if (error.response?.status === 401) {
      localStorage.removeItem('token')
      window.location.href = '/login'
    }

    // Handle network errors
    if (!error.response) {
      error.isNetworkError = true
      if (!isOnline) {
        error.isOffline = true
      }
    }

    return Promise.reject(error)
  }
)

// Export offline status checker
export const checkOnlineStatus = () => isOnline

export default api