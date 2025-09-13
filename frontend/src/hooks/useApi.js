import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import api from '../services/api.js'

// Generic API hook for GET requests
export const useApi = (key, fn, options = {}) => {
  return useQuery({
    queryKey: key,
    queryFn: fn,
    ...options
  })
}

// Generic API hook for POST/PUT/DELETE requests
export const useApiMutation = (fn, options = {}) => {
  const queryClient = useQueryClient()
  
  return useMutation({
    mutationFn: fn,
    onSuccess: () => {
      queryClient.invalidateQueries()
    },
    ...options
  })
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