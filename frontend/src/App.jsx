import { BrowserRouter as Router, Routes, Route } from 'react-router-dom'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { AuthProvider } from './context/AuthContext.jsx'
import Layout from './components/UI/Layout'
import Login from './components/Auth/Login'
import Dashboard from './components/Dashboard'
import CompanyList from './components/Companies/CompanyList'
import CompanyForm from './components/Companies/CompanyForm'
import CompanyDetail from './components/Companies/CompanyDetail'
import EmployeeList from './components/Employees/EmployeeList'
import EmployeeForm from './components/Employees/EmployeeForm'
import EmployeeDetail from './components/Employees/EmployeeDetail'
import PrivateRoute from './components/Auth/PrivateRoute'

const queryClient = new QueryClient()

function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <AuthProvider>
        <Router>
          <div className="min-h-screen bg-gray-50">
            <Routes>
              <Route path="/login" element={<Login />} />
              <Route
                path="/"
                element={
                  <PrivateRoute>
                    <Layout />
                  </PrivateRoute>
                }
              >
                <Route index element={<Dashboard />} />
                <Route path="companies" element={<CompanyList />} />
                <Route path="companies/new" element={<CompanyForm />} />
                <Route path="companies/:id" element={<CompanyDetail />} />
                <Route path="companies/:id/edit" element={<CompanyForm />} />
                <Route path="companies/:companyId/employees" element={<EmployeeList />} />
                <Route path="companies/:companyId/employees/new" element={<EmployeeForm />} />
                <Route path="companies/:companyId/employees/:id" element={<EmployeeDetail />} />
                <Route path="companies/:companyId/employees/:id/edit" element={<EmployeeForm />} />
                <Route path="employees" element={<EmployeeList />} />
                <Route path="employees/new" element={<EmployeeForm />} />
                <Route path="employees/:id" element={<EmployeeDetail />} />
                <Route path="employees/:id/edit" element={<EmployeeForm />} />
              </Route>
            </Routes>
          </div>
        </Router>
      </AuthProvider>
    </QueryClientProvider>
  )
}

export default App
