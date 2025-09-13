import { useState, useEffect } from 'react'
import { useParams, Link, useNavigate } from 'react-router-dom'
import { employeeService } from '../../services/employeeService.js'
import { companyService } from '../../services/companyService.js'

const EmployeeDetail = () => {
  const { companyId, id } = useParams()
  const navigate = useNavigate()
  const isAllEmployeesView = !companyId
  const [employee, setEmployee] = useState(null)
  const [company, setCompany] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)

  const fetchData = async () => {
    try {
      setLoading(true)
      setError(null)
      
      const employeeResponse = await employeeService.getEmployee(id)
      setEmployee(employeeResponse)
      
      // Only fetch company if we have a companyId or if employee has a company
      if (companyId) {
        const companyResponse = await companyService.getCompany(companyId)
        setCompany(companyResponse)
      } else if (employeeResponse.company_id) {
        const companyResponse = await companyService.getCompany(employeeResponse.company_id)
        setCompany(companyResponse)
      }
    } catch (err) {
      setError('Failed to fetch data')
      console.error('Error fetching data:', err)
    } finally {
      setLoading(false)
    }
  }

  const handleDelete = async () => {
    if (window.confirm('Are you sure you want to delete this employee?')) {
      try {
        await employeeService.deleteEmployee(id)
        // Navigate back to the appropriate list
        if (isAllEmployeesView) {
          navigate('/employees')
        } else {
          navigate(`/companies/${companyId}/employees`)
        }
      } catch (err) {
        setError('Failed to delete employee')
        console.error('Error deleting employee:', err)
      }
    }
  }

  useEffect(() => {
    fetchData()
  }, [companyId, id])

  if (loading) {
    return (
      <div className="flex justify-center items-center h-64">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
      </div>
    )
  }

  if (error) {
    return (
      <div className="bg-red-50 border border-red-200 rounded-lg p-4">
        <p className="text-red-700">{error}</p>
      </div>
    )
  }

  if (!employee) {
    return (
      <div className="bg-white rounded-lg shadow p-6">
        <p className="text-gray-600">Employee not found</p>
      </div>
    )
  }

  return (
    <div>
      <div className="flex justify-between items-center mb-6">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">Employee Details</h1>
          {company && (
            <p className="text-gray-600 mt-1">
              Company: <Link to={`/companies/${company.id}`} className="text-blue-600 hover:text-blue-800">{company.name}</Link>
            </p>
          )}
        </div>
        <div className="space-x-4">
          <Link
            to={isAllEmployeesView ? `/employees/${id}/edit` : `/companies/${companyId}/employees/${id}/edit`}
            className="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-200"
          >
            Edit
          </Link>
          <button
            onClick={handleDelete}
            className="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition duration-200"
          >
            Delete
          </button>
          <Link
            to={isAllEmployeesView ? '/employees' : `/companies/${companyId}/employees`}
            className="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition duration-200"
          >
            Back to Employees
          </Link>
        </div>
      </div>

      <div className="bg-white rounded-lg shadow overflow-hidden">
        <div className="p-6">
          <div className="flex items-start space-x-6">
            <div className="flex-shrink-0">
              <div className="h-32 w-32 bg-blue-500 rounded-full flex items-center justify-center">
                <span className="text-white text-2xl font-bold">
                  {employee.first_name?.[0]}{employee.last_name?.[0]}
                </span>
              </div>
            </div>
            <div className="flex-1">
              <h2 className="text-2xl font-bold text-gray-900 mb-2">
                {employee.first_name} {employee.last_name}
              </h2>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <h3 className="text-sm font-medium text-gray-500">Email</h3>
                  <p className="text-gray-900">{employee.email}</p>
                </div>
                <div>
                  <h3 className="text-sm font-medium text-gray-500">Phone</h3>
                  <p className="text-gray-900">{employee.phone || 'N/A'}</p>
                </div>
                <div>
                  <h3 className="text-sm font-medium text-gray-500">Position</h3>
                  <p className="text-gray-900">{employee.position || 'N/A'}</p>
                </div>
                <div>
                  <h3 className="text-sm font-medium text-gray-500">Department</h3>
                  <p className="text-gray-900">{employee.department || 'N/A'}</p>
                </div>
                <div>
                  <h3 className="text-sm font-medium text-gray-500">Status</h3>
                  <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${
                    employee.status === 'active' 
                      ? 'bg-green-100 text-green-800' 
                      : employee.status === 'inactive'
                      ? 'bg-red-100 text-red-800'
                      : 'bg-yellow-100 text-yellow-800'
                  }`}>
                    {employee.status || 'Active'}
                  </span>
                </div>
                <div>
                  <h3 className="text-sm font-medium text-gray-500">Created At</h3>
                  <p className="text-gray-900">
                    {new Date(employee.created_at).toLocaleDateString()}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}

export default EmployeeDetail