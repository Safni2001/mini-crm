import { useState, useEffect } from 'react'
import { Link, useParams, useNavigate } from 'react-router-dom'
import { employeeService } from '../../services/employeeService.js'
import { companyService } from '../../services/companyService.js'

const EmployeeList = () => {
  const { companyId } = useParams()
  const navigate = useNavigate()
  const [employees, setEmployees] = useState([])
  const [company, setCompany] = useState(null)
  const [companies, setCompanies] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)
  const [currentPage, setCurrentPage] = useState(1)
  const [pagination, setPagination] = useState(null)
  const [searchTerm, setSearchTerm] = useState('')
  const [selectedCompany, setSelectedCompany] = useState('')

  const isAllEmployeesView = !companyId

  const fetchData = async (page = 1) => {
    try {
      setLoading(true)
      setError(null)
      
      if (isAllEmployeesView) {
        // Fetch all employees and companies for the main employee list
        const [employeesResponse, companiesResponse] = await Promise.all([
          employeeService.getEmployees(),
          companyService.getCompanies(page)
        ])
        
        const employeesData = employeesResponse.data || employeesResponse
        const companiesData = companiesResponse.data || companiesResponse
        
        setEmployees(employeesData)
        setCompanies(companiesData)
        setPagination(companiesResponse.pagination)
      } else {
        // Fetch employees for specific company and company details
        const [employeesResponse, companyResponse] = await Promise.all([
          employeeService.getEmployees(companyId),
          companyService.getCompany(companyId)
        ])
        
        setEmployees(employeesResponse.data || employeesResponse)
        setCompany(companyResponse)
      }
    } catch (err) {
      setError('Failed to fetch data')
      console.error('Error fetching data:', err)
    } finally {
      setLoading(false)
    }
  }

  const handleDelete = async (id) => {
    if (window.confirm('Are you sure you want to delete this employee?')) {
      try {
        await employeeService.deleteEmployee(id)
        fetchData(currentPage)
      } catch (err) {
        setError('Failed to delete employee')
        console.error('Error deleting employee:', err)
      }
    }
  }

  const handlePageChange = (page) => {
    setCurrentPage(page)
    fetchData(page)
  }

  const filteredEmployees = employees.filter(employee => {
    const matchesSearch = searchTerm === '' || 
      `${employee.first_name} ${employee.last_name}`.toLowerCase().includes(searchTerm.toLowerCase()) ||
      employee.email?.toLowerCase().includes(searchTerm.toLowerCase())
    
    const matchesCompany = selectedCompany === '' || employee.company_id === parseInt(selectedCompany)
    
    return matchesSearch && matchesCompany
  })

  useEffect(() => {
    fetchData()
  }, [companyId])

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

  return (
    <div>
      <div className="flex justify-between items-center mb-6">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">
            {isAllEmployeesView ? 'All Employees' : 'Employees'}
          </h1>
          {company && (
            <p className="text-gray-600 mt-1">
              Company: <Link to={`/companies/${company.id}`} className="text-blue-600 hover:text-blue-800">{company.name}</Link>
            </p>
          )}
        </div>
        <Link
          to={isAllEmployeesView ? '/employees/new' : `/companies/${companyId}/employees/new`}
          className="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-200"
        >
          Add Employee
        </Link>
      </div>

      {/* Search and Filter Section for All Employees View */}
      {isAllEmployeesView && (
        <div className="bg-white rounded-lg shadow p-6 mb-6">
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                Search Employees
              </label>
              <input
                type="text"
                placeholder="Search by name or email..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                Filter by Company
              </label>
              <select
                value={selectedCompany}
                onChange={(e) => setSelectedCompany(e.target.value)}
                className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">All Companies</option>
                {companies.map((company) => (
                  <option key={company.id} value={company.id}>
                    {company.name}
                  </option>
                ))}
              </select>
            </div>
            <div className="flex items-end">
              <div className="text-sm text-gray-600">
                Showing {filteredEmployees.length} of {employees.length} employees
              </div>
            </div>
          </div>
        </div>
      )}

      <div className="bg-white rounded-lg shadow overflow-hidden">
        <div className="overflow-x-auto">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Name
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Email
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Phone
                </th>
                {isAllEmployeesView && (
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Company
                  </th>
                )}
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {filteredEmployees.length === 0 ? (
                <tr>
                  <td colSpan={isAllEmployeesView ? "5" : "4"} className="px-6 py-4 text-center text-gray-500">
                    No employees found
                  </td>
                </tr>
              ) : (
                filteredEmployees.map((employee) => (
                  <tr key={employee.id} className="hover:bg-gray-50">
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className="flex items-center">
                        <div className="flex-shrink-0 h-10 w-10 bg-blue-500 rounded-full flex items-center justify-center">
                          <span className="text-white text-sm font-medium">
                            {employee.first_name?.[0]}{employee.last_name?.[0]}
                          </span>
                        </div>
                        <div className="ml-4">
                          <div className="text-sm font-medium text-gray-900">
                            {employee.first_name} {employee.last_name}
                          </div>
                        </div>
                      </div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {employee.email}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {employee.phone || 'N/A'}
                    </td>
                    {isAllEmployeesView && (
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {employee.company ? (
                          <Link 
                            to={`/companies/${employee.company.id}`} 
                            className="text-blue-600 hover:text-blue-800"
                          >
                            {employee.company.name}
                          </Link>
                        ) : (
                          <span className="text-gray-400">No Company</span>
                        )}
                      </td>
                    )}
                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <Link
                        to={isAllEmployeesView ? `/employees/${employee.id}` : `/companies/${companyId}/employees/${employee.id}`}
                        className="text-blue-600 hover:text-blue-900 mr-3"
                      >
                        View
                      </Link>
                      <Link
                        to={isAllEmployeesView ? `/employees/${employee.id}/edit` : `/companies/${companyId}/employees/${employee.id}/edit`}
                        className="text-indigo-600 hover:text-indigo-900 mr-3"
                      >
                        Edit
                      </Link>
                      <button
                        onClick={() => handleDelete(employee.id)}
                        className="text-red-600 hover:text-red-900"
                      >
                        Delete
                      </button>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  )
}

export default EmployeeList