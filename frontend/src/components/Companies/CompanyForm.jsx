import { useState, useEffect } from 'react'
import { useNavigate, useParams } from 'react-router-dom'
import { companyService } from '../../services/companyService.js'

const CompanyForm = () => {
  const navigate = useNavigate()
  const { id } = useParams()
  const isEditing = !!id

  const [formData, setFormData] = useState({
    name: '',
    email: '',
    website: '',
    logo: null
  })
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState(null)
  const [fetching, setFetching] = useState(false)

  const fetchCompany = async () => {
    if (!isEditing) return
    
    try {
      setFetching(true)
      const company = await companyService.getCompany(id)
      setFormData({
        name: company.name,
        email: company.email,
        website: company.website,
        logo: null
      })
    } catch (err) {
      setError('Failed to fetch company data')
      console.error('Error fetching company:', err)
    } finally {
      setFetching(false)
    }
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    setLoading(true)
    setError(null)

    // Create a clean data object for submission
    const submitData = {
      name: formData.name,
      email: formData.email,
      website: formData.website,
    }

    // Only add logo if it's a File object (not null or undefined)
    if (formData.logo instanceof File) {
      submitData.logo = formData.logo
    }

    try {
      if (isEditing) {
        await companyService.updateCompany(id, submitData)
      } else {
        await companyService.createCompany(submitData)
      }
      navigate('/companies')
    } catch (err) {
      setError('Failed to save company')
      console.error('Error saving company:', err)
    } finally {
      setLoading(false)
    }
  }

  const handleChange = (e) => {
    const { name, value, files } = e.target
    setFormData({
      ...formData,
      [name]: files ? files[0] : value
    })
  }

  useEffect(() => {
    fetchCompany()
  }, [id])

  if (fetching) {
    return (
      <div className="flex justify-center items-center h-64">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
      </div>
    )
  }

  return (
    <div>
      <h1 className="text-3xl font-bold text-gray-900 mb-6">
        {isEditing ? 'Edit Company' : 'Add New Company'}
      </h1>
      
      {error && (
        <div className="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
          <p className="text-red-700">{error}</p>
        </div>
      )}

      <div className="bg-white rounded-lg shadow p-6">
        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Company Name
            </label>
            <input
              type="text"
              name="name"
              value={formData.name}
              onChange={handleChange}
              className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              required
            />
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
              Website
            </label>
            <input
              type="url"
              name="website"
              value={formData.website}
              onChange={handleChange}
              className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Company Logo
            </label>
            <input
              type="file"
              name="logo"
              onChange={handleChange}
              accept="image/*"
              className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            {formData.logo && (
              <p className="text-sm text-gray-600 mt-1">
                Selected file: {formData.logo.name}
              </p>
            )}
          </div>
          <div className="flex space-x-4">
            <button
              type="submit"
              disabled={loading}
              className="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {loading ? 'Saving...' : (isEditing ? 'Update Company' : 'Create Company')}
            </button>
            <button
              type="button"
              onClick={() => navigate('/companies')}
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

export default CompanyForm