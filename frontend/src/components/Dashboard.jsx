import { Link } from 'react-router-dom'

const Dashboard = () => {
  return (
    <div>
      <h1 className="text-3xl font-bold text-gray-900 mb-6">Dashboard</h1>
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div className="bg-white p-6 rounded-lg shadow">
          <h2 className="text-xl font-semibold text-gray-800 mb-4">Companies</h2>
          <p className="text-gray-600 mb-4">Manage your company profiles and information</p>
          <Link
            to="/companies"
            className="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-200"
          >
            View Companies
          </Link>
        </div>
        <div className="bg-white p-6 rounded-lg shadow">
          <h2 className="text-xl font-semibold text-gray-800 mb-4">Employees</h2>
          <p className="text-gray-600 mb-4">Manage employee records and assignments</p>
          <Link
            to="/employees"
            className="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition duration-200"
          >
            View Employees
          </Link>
        </div>
        <div className="bg-white p-6 rounded-lg shadow">
          <h2 className="text-xl font-semibold text-gray-800 mb-4">Settings</h2>
          <p className="text-gray-600 mb-4">Configure your account and preferences</p>
          <button className="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition duration-200">
            Settings
          </button>
        </div>
      </div>
    </div>
  )
}

export default Dashboard