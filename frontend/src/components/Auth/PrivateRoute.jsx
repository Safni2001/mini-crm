import { useContext } from 'react'
import { Navigate } from 'react-router-dom'
import { AuthContext } from '../../context/AuthContext.jsx'

const PrivateRoute = ({ children }) => {
  const { user, loading } = useContext(AuthContext)

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-500"></div>
      </div>
    )
  }

  return user ? children : <Navigate to="/login" />
}

export default PrivateRoute