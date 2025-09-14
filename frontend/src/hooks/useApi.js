import React from 'react'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import api, { checkOnlineStatus } from '../services/api.js'

// Generic API hook for GET requests with enhanced error handling
export const useApi = (key, fn, options = {}) => {
  return useQuery({
    queryKey: key,
    queryFn: fn,
    retry: (failureCount, error) => {
      // Don't retry if offline
      if (error.isOffline) return false
      // Don't retry auth errors
      if (error.response?.status === 401) return false
      // Don't retry client errors (4xx except 401)
      if (error.response?.status >= 400 && error.response?.status < 500) return false
      // Retry network errors and server errors up to 3 times
      return failureCount < 3
    },
    retryDelay: (attemptIndex) => Math.min(1000 * 2 ** attemptIndex, 30000),
    staleTime: 5 * 60 * 1000, // 5 minutes
    cacheTime: 10 * 60 * 1000, // 10 minutes
    refetchOnWindowFocus: false,
    ...options
  })
}

// Generic API hook for POST/PUT/DELETE requests with enhanced error handling
export const useApiMutation = (fn, options = {}) => {
  const queryClient = useQueryClient()
  
  return useMutation({
    mutationFn: fn,
    retry: (failureCount, error) => {
      // Don't retry if offline
      if (error.isOffline) return false
      // Don't retry auth errors
      if (error.response?.status === 401) return false
      // Don't retry client errors (4xx except 401)
      if (error.response?.status >= 400 && error.response?.status < 500) return false
      // Retry network errors and server errors up to 2 times
      return failureCount < 2
    },
    retryDelay: (attemptIndex) => Math.min(1000 * 2 ** attemptIndex, 10000),
    onSuccess: (data, variables, context) => {
      queryClient.invalidateQueries()
      options.onSuccess?.(data, variables, context)
    },
    onError: (error, variables, context) => {
      if (error.isOffline) {
        console.warn('Mutation failed due to offline status')
      }
      options.onError?.(error, variables, context)
    },
    ...options
  })
}

// Hook to check online status
export const useOnlineStatus = () => {
  const [isOnline, setIsOnline] = React.useState(checkOnlineStatus())

  React.useEffect(() => {
    const handleOnline = () => setIsOnline(true)
    const handleOffline = () => setIsOnline(false)

    window.addEventListener('online', handleOnline)
    window.addEventListener('offline', handleOffline)

    return () => {
      window.removeEventListener('online', handleOnline)
      window.removeEventListener('offline', handleOffline)
    }
  }, [])

  return isOnline
}

// Company API hooks
export const useCompanies = (params = {}) => {
  return useApi(
    ['companies', params],
    () => api.get('/companies', { params }).then(res => res.data),
    {
      keepPreviousData: true
    }
  )
}

export const useCompany = (id) => {
  return useApi(
    ['company', id],
    () => api.get(`/companies/${id}`).then(res => res.data),
    {
      enabled: !!id
    }
  )
}

export const useCreateCompany = () => {
  return useApiMutation(
    (data) => api.post('/companies', data)
  )
}

export const useUpdateCompany = () => {
  return useApiMutation(
    ({ id, data }) => api.put(`/companies/${id}`, data)
  )
}

export const useDeleteCompany = () => {
  return useApiMutation(
    (id) => api.delete(`/companies/${id}`)
  )
}

// Employee API hooks
export const useEmployees = (companyId, params = {}) => {
  return useApi(
    ['employees', companyId, params],
    () => api.get(`/companies/${companyId}/employees`, { params }).then(res => res.data),
    {
      enabled: !!companyId,
      keepPreviousData: true
    }
  )
}

export const useEmployee = (companyId, id) => {
  return useApi(
    ['employee', companyId, id],
    () => api.get(`/companies/${companyId}/employees/${id}`).then(res => res.data),
    {
      enabled: !!companyId && !!id
    }
  )
}

export const useCreateEmployee = () => {
  return useApiMutation(
    ({ companyId, data }) => api.post(`/companies/${companyId}/employees`, data)
  )
}

export const useUpdateEmployee = () => {
  return useApiMutation(
    ({ companyId, id, data }) => api.put(`/companies/${companyId}/employees/${id}`, data)
  )
}

export const useDeleteEmployee = () => {
  return useApiMutation(
    ({ companyId, id }) => api.delete(`/companies/${companyId}/employees/${id}`)
  )
}