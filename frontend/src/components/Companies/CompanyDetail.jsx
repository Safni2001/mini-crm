import { useState, useEffect } from 'react'
import { useParams, Link, useNavigate } from 'react-router-dom'
import { companyService } from '../../services/companyService.js'

const CompanyDetail = () => {
  const { id } = useParams()
  const navigate = useNavigate()
  const [company, setCompany] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)

  const fetchCompany = async () => {
    try {
      setLoading(true)
      const response = await companyService.getCompany(id)
      setCompany(response)
    } catch (err) {
      setError('Failed to fetch company details')
      console.error('Error fetching company:', err)
    } finally {
      setLoading(false)
    }
  }

  const handleDelete = async () => {
    if (window.confirm('Are you sure you want to delete this company?')) {
      try {
        await companyService.deleteCompany(id)
        navigate('/companies')
      } catch (err) {
        setError('Failed to delete company')
        console.error('Error deleting company:', err)
      }
    }
  }

  useEffect(() => {
    fetchCompany()
  }, [id])

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

  if (!company) {
    return (
      <div className="bg-white rounded-lg shadow p-6">
        <p className="text-gray-600">Company not found</p>
      </div>
    )
  }

  return (
    <div>
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-3xl font-bold text-gray-900">Company Details</h1>
        <div className="space-x-4">
          <Link
            to={`/companies/${id}/edit`}
            className="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-200"
          >
            Edit
          </Link>
          <Link
            to={`/companies/${id}/employees`}
            className="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition duration-200"
          >
            View Employees
          </Link>
          <button
            onClick={handleDelete}
            className="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition duration-200"
          >
            Delete
          </button>
        </div>
      </div>

      <div className="bg-white rounded-lg shadow overflow-hidden">
        <div className="p-6">
          <div className="flex items-start space-x-6">
            {company.logo && (
              <div className="flex-shrink-0">
                <img 
                  className="h-32 w-32 rounded-lg object-cover"
                  src={`http://mini-crm.test/storage/${company.logo}`}
                  alt={company.name}
                />
              </div>
            )}
            <div className="flex-1">
              <h2 className="text-2xl font-bold text-gray-900 mb-2">{company.name}</h2>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <h3 className="text-sm font-medium text-gray-500">Email</h3>
                  <p className="text-gray-900">{company.email || 'N/A'}</p>
                </div>
                <div>
                  <h3 className="text-sm font-medium text-gray-500">Website</h3>
                  {company.website ? (
                    <a 
                      href={company.website} 
                      target="_blank" 
                      rel="noopener noreferrer"
                      className="text-blue-600 hover:text-blue-800"
                    >
                      {company.website}
                    </a>
                  ) : (
                    <p className="text-gray-900">N/A</p>
                  )}
                </div>
                <div>
                  <h3 className="text-sm font-medium text-gray-500">Created At</h3>
                  <p className="text-gray-900">
                    {new Date(company.created_at).toLocaleDateString()}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Employees Section */}
        <div className="border-t border-gray-200 px-6 py-4 bg-gray-50">
          <div className="flex justify-between items-center mb-4">
            <h3 className="text-lg font-medium text-gray-900">Employees</h3>
            <Link
              to={`/companies/${id}/employees/new`}
              className="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition duration-200 text-sm"
            >
              Add Employee
            </Link>
          </div>
          
          {company.employees && company.employees.length > 0 ? (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              {company.employees.map((employee) => (
                <div key={employee.id} className="bg-white rounded-lg border border-gray-200 p-4">
                  <div className="flex items-center">
                    <div className="flex-shrink-0 h-10 w-10 bg-blue-500 rounded-full flex items-center justify-center">
                      <span className="text-white text-sm font-medium">
                        {employee.first_name?.[0]}{employee.last_name?.[0]}
                      </span>
                    </div>
                    <div className="ml-3">
                      <p className="text-sm font-medium text-gray-900">
                        {employee.first_name} {employee.last_name}
                      </p>
                      <p className="text-sm text-gray-500">{employee.email}</p>
                    </div>
                  </div>
                  <div className="mt-3 flex space-x-2">
                    <Link
                      to={`/companies/${id}/employees/${employee.id}`}
                      className="text-blue-600 hover:text-blue-800 text-xs"
                    >
                      View
                    </Link>
                    <Link
                      to={`/companies/${id}/employees/${employee.id}/edit`}
                      className="text-indigo-600 hover:text-indigo-800 text-xs"
                    >
                      Edit
                    </Link>
                  </div>
                </div>
              ))}
            </div>
          ) : (
            <p className="text-gray-500">No employees found</p>
          )}
        </div>
      </div>
    </div>
  )
}

export default CompanyDetail