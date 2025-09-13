import api from '../services/api.js'

export const employeeService = {
  // Get all employees
  getEmployees: async (companyId = null) => {
    const url = companyId ? `/employees?company_id=${companyId}` : '/employees'
    const response = await api.get(url)
    return response.data
  },

  // Get single employee by ID
  getEmployee: async (id) => {
    const response = await api.get(`/employees/${id}`)
    return response.data
  },

  // Create new employee
  createEmployee: async (employeeData) => {
    const response = await api.post('/employees', employeeData)
    return response.data
  },

  // Update employee
  updateEmployee: async (id, employeeData) => {
    const response = await api.put(`/employees/${id}`, employeeData)
    return response.data
  },

  // Delete employee
  deleteEmployee: async (id) => {
    const response = await api.delete(`/employees/${id}`)
    return response.data
  }
}