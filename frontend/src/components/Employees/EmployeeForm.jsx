import { useState, useEffect } from 'react'
import { useNavigate, useParams, Link } from 'react-router-dom'
import { employeeService } from '../../services/employeeService.js'
import { companyService } from '../../services/companyService.js'

const EmployeeForm = () => {
  const navigate = useNavigate()
  const { companyId, id } = useParams()
  const isEditing = !!id
  const isAllEmployeesView = !companyId

  const [formData, setFormData] = useState({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    position: '',
    department: '',
    status: 'active',
    company_id: companyId || ''
  })
  const [company, setCompany] = useState(null)
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState(null)
  const [fetching, setFetching] = useState(false)
  const [companies, setCompanies] = useState([])

  const fetchEmployee = async () => {
    if (!isEditing) return
    
    try {
      setFetching(true)
      const employee = await employeeService.getEmployee(id)
      setFormData({
        first_name: employee.first_name,
        last_name: employee.last_name,
        email: employee.email,
        phone: employee.phone,
        position: employee.position,
        department: employee.department,
        status: employee.status || 'active',
        company_id: employee.company_id || ''
      })
    } catch (err) {
      setError('Failed to fetch employee data')
      console.error('Error fetching employee:', err)
    } finally {
      setFetching(false)
    }
  }

  const fetchCompany = async () => {
    if (!companyId) return
    
    try {
      const companyData = await companyService.getCompany(companyId)
      setCompany(companyData)
    } catch (err) {
      setError('Failed to fetch company data')
      console.error('Error fetching company:', err)
    }
  }

  const fetchCompanies = async () => {
    if (!isAllEmployeesView) return
    
    try {
      const response = await companyService.getCompanies()
      setCompanies(response.data || response)
    } catch (err) {
      setError('Failed to fetch companies')
      console.error('Error fetching companies:', err)
    }
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    setLoading(true)
    setError(null)

    try {
      const submitData = {
        ...formData,
        company_id: formData.company_id || null
      }

      if (isEditing) {
        await employeeService.updateEmployee(id, submitData)
      } else {
        await employeeService.createEmployee(submitData)
      }
      
      // Navigate back to the appropriate list
      if (isAllEmployeesView) {
        navigate('/employees')
      } else {
        navigate(`/companies/${companyId}/employees`)
      }
    } catch (err) {
      setError('Failed to save employee')
      console.error('Error saving employee:', err)
    } finally {
      setLoading(false)
    }
  }

  const handleChange = (e) => {
    const { name, value } = e.target
    setFormData({
      ...formData,
      [name]: value
    })
  }

  useEffect(() => {
    const fetchData = async () => {
      if (isAllEmployeesView) {
        await Promise.all([fetchEmployee(), fetchCompanies()])
      } else {
        await Promise.all([fetchCompany(), fetchEmployee()])
      }
    }
    fetchData()
  }, [id, companyId])

  if (fetching) {
    return (
      <div className="flex justify-center items-center h-64">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
      </div>
    )
  }

  return (
    <div>
      <div className="mb-6">
        <h1 className="text-3xl font-bold text-gray-900 mb-2">
          {isEditing ? 'Edit Employee' : 'Add New Employee'}
        </h1>
        {company && (
          <p className="text-gray-600">
            Company: <Link to={`/companies/${company.id}`} className="text-blue-600 hover:text-blue-800">{company.name}</Link>
          </p>
        )}
      </div>

      {error && (
        <div className="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
          <p className="text-red-700">{error}</p>
        </div>
      )}

      <div className="bg-white rounded-lg shadow p-6">
        <form onSubmit={handleSubmit} className="space-y-4">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                First Name
              </label>
              <input
                type="text"
                name="first_name"
                value={formData.first_name}
                onChange={handleChange}
                className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                Last Name
              </label>
              <input
                type="text"
                name="last_name"
                value={formData.last_name}
                onChange={handleChange}
                className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              />
            </div>
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Email
            </label>
            <input
              type="email"
              name="email"
              value={formData.email}
              onChange={handleChange}
              className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              required
            />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Phone
            </label>
            <input
              type="tel"
              name="phone"
              value={formData.phone}
              onChange={handleChange}
              className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Position
            </label>
            <input
              type="text"
              name="position"
              value={formData.position}
              onChange={handleChange}
              className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              required
            />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Department
            </label>
            <input
              type="text"
              name="department"
              value={formData.department}
              onChange={handleChange}
              className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          {isAllEmployeesView && (
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                Company
              </label>
              <select
                name="company_id"
                value={formData.company_id}
                onChange={handleChange}
                className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              >
                <option value="">Select a Company</option>
                {companies.map((company) => (
                  <option key={company.id} value={company.id}>
                    {company.name}
                  </option>
                ))}
              </select>
            </div>
          )}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Status
            </label>
            <select
              name="status"
              value={formData.status}
              onChange={handleChange}
              className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="on_leave">On Leave</option>
            </select>
          </div>
          <div className="flex space-x-4">
            <button
              type="submit"
              disabled={loading}
              className="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {loading ? 'Saving...' : (isEditing ? 'Update Employee' : 'Create Employee')}
            </button>
            <button
              type="button"
              onClick={() => navigate(isAllEmployeesView ? '/employees' : `/companies/${companyId}/employees`)}
              className="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition duration-200"
            >
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  )
}

export default EmployeeForm