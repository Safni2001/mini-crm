import { BrowserRouter as Router, Routes, Route } from 'react-router-dom'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { Suspense, lazy } from 'react'
import { AuthProvider } from './context/AuthContext.jsx'
import Layout from './components/UI/Layout'
import Login from './components/Auth/Login'
import PrivateRoute from './components/Auth/PrivateRoute'

// Lazy load components for better performance
const Dashboard = lazy(() => import('./components/Dashboard'))
const CompanyList = lazy(() => import('./components/Companies/CompanyList'))
const CompanyForm = lazy(() => import('./components/Companies/CompanyForm'))
const CompanyDetail = lazy(() => import('./components/Companies/CompanyDetail'))
const EmployeeList = lazy(() => import('./components/Employees/EmployeeList'))
const EmployeeForm = lazy(() => import('./components/Employees/EmployeeForm'))
const EmployeeDetail = lazy(() => import('./components/Employees/EmployeeDetail'))

// Optimized QueryClient configuration
const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 5 * 60 * 1000, // 5 minutes
      cacheTime: 10 * 60 * 1000, // 10 minutes
      retry: (failureCount, error) => {
        if (error?.response?.status === 404) return false
        if (error?.response?.status >= 400 && error?.response?.status < 500) return false
        return failureCount < 3
      },
      retryDelay: (attemptIndex) => Math.min(1000 * 2 ** attemptIndex, 30000),
      refetchOnWindowFocus: false,
      refetchOnMount: true
    },
    mutations: {
      retry: (failureCount, error) => {
        if (error?.response?.status >= 400 && error?.response?.status < 500) return false
        return failureCount < 2
      }
    }
  }
})

// Loading component
const LoadingSpinner = () => (
  <div className="flex items-center justify-center h-64">
    <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
  </div>
)

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
                <Route index element={
                  <Suspense fallback={<LoadingSpinner />}>
                    <Dashboard />
                  </Suspense>
                } />
                <Route path="companies" element={
                  <Suspense fallback={<LoadingSpinner />}>
                    <CompanyList />
                  </Suspense>
                } />
                <Route path="companies/new" element={
                  <Suspense fallback={<LoadingSpinner />}>
                    <CompanyForm />
                  </Suspense>
                } />
                <Route path="companies/:id" element={
                  <Suspense fallback={<LoadingSpinner />}>
                    <CompanyDetail />
                  </Suspense>
                } />
                <Route path="companies/:id/edit" element={
                  <Suspense fallback={<LoadingSpinner />}>
                    <CompanyForm />
                  </Suspense>
                } />
                <Route path="companies/:companyId/employees" element={
                  <Suspense fallback={<LoadingSpinner />}>
                    <EmployeeList />
                  </Suspense>
                } />
                <Route path="companies/:companyId/employees/new" element={
                  <Suspense fallback={<LoadingSpinner />}>
                    <EmployeeForm />
                  </Suspense>
                } />
                <Route path="companies/:companyId/employees/:id" element={
                  <Suspense fallback={<LoadingSpinner />}>
                    <EmployeeDetail />
                  </Suspense>
                } />
                <Route path="companies/:companyId/employees/:id/edit" element={
                  <Suspense fallback={<LoadingSpinner />}>
                    <EmployeeForm />
                  </Suspense>
                } />
                <Route path="employees" element={
                  <Suspense fallback={<LoadingSpinner />}>
                    <EmployeeList />
                  </Suspense>
                } />
                <Route path="employees/new" element={
                  <Suspense fallback={<LoadingSpinner />}>
                    <EmployeeForm />
                  </Suspense>
                } />
                <Route path="employees/:id" element={
                  <Suspense fallback={<LoadingSpinner />}>
                    <EmployeeDetail />
                  </Suspense>
                } />
                <Route path="employees/:id/edit" element={
                  <Suspense fallback={<LoadingSpinner />}>
                    <EmployeeForm />
                  </Suspense>
                } />
              </Route>
            </Routes>
          </div>
        </Router>
      </AuthProvider>
    </QueryClientProvider>
  )
}

export default App
