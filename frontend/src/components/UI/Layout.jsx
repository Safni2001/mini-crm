import { Outlet, Link, useNavigate } from 'react-router-dom'
import { useAuth } from '../../hooks/useAuth.js'

const Layout = () => {
  const { user, logout } = useAuth()
  const navigate = useNavigate()

  const handleLogout = async () => {
    await logout()
    navigate('/login')
  }

  return (
    <div className="flex h-screen bg-gray-100">
      <div className="w-64 bg-white shadow-lg">
        <div className="p-6">
          <h1 className="text-2xl font-bold text-gray-800">Mini CRM</h1>
          <p className="text-gray-600 text-sm mt-1">Welcome, {user?.name}</p>
        </div>
        <nav className="mt-6">
          <Link
            to="/"
            className="block px-6 py-2 text-gray-700 hover:bg-gray-100 hover:text-gray-900"
          >
            Dashboard
          </Link>
          <Link
            to="/companies"
            className="block px-6 py-2 text-gray-700 hover:bg-gray-100 hover:text-gray-900"
          >
            Companies
          </Link>
        </nav>
        <div className="absolute bottom-0 w-64 p-6">
          <button
            onClick={handleLogout}
            className="w-full bg-red-500 text-white py-2 px-4 rounded hover:bg-red-600 transition duration-200"
          >
            Logout
          </button>
        </div>
      </div>
      <div className="flex-1 overflow-auto">
        <main className="p-6">
          <Outlet />
        </main>
      </div>
    </div>
  )
}

export default Layout