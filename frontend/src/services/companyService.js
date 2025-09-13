import api from '../services/api.js'

export const companyService = {
  // Get all companies with pagination
  getCompanies: async (page = 1) => {
    const response = await api.get(`/companies?page=${page}`)
    return response.data
  },

  // Get single company by ID
  getCompany: async (id) => {
    const response = await api.get(`/companies/${id}`)
    return response.data
  },

  // Create new company
  createCompany: async (companyData) => {
    const formData = new FormData()
    
    Object.keys(companyData).forEach(key => {
      if (key === 'logo' && companyData[key] instanceof File) {
        formData.append('logo', companyData[key])
      } else if (companyData[key] !== null && companyData[key] !== undefined) {
        formData.append(key, companyData[key])
      }
    })

    const response = await api.post('/companies', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
    return response.data
  },

  // Update company
  updateCompany: async (id, companyData) => {
    const formData = new FormData()
    
    Object.keys(companyData).forEach(key => {
      if (key === 'logo' && companyData[key] instanceof File) {
        formData.append('logo', companyData[key])
      } else if (companyData[key] !== null && companyData[key] !== undefined) {
        formData.append(key, companyData[key])
      }
    })

    // For PUT/PATCH requests with FormData, we need to use _method parameter
    formData.append('_method', 'PUT')

    const response = await api.post(`/companies/${id}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
    return response.data
  },

  // Delete company
  deleteCompany: async (id) => {
    const response = await api.delete(`/companies/${id}`)
    return response.data
  }
}