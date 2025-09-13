export const validateEmail = (email) => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return emailRegex.test(email)
}

export const validatePhone = (phone) => {
  const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/
  return phoneRegex.test(phone.replace(/[\s\-\(\)]/g, ''))
}

export const validateUrl = (url) => {
  try {
    new URL(url)
    return true
  } catch {
    return false
  }
}

export const validateRequired = (value) => {
  return value !== null && value !== undefined && value.toString().trim() !== ''
}

export const getValidationErrors = (values, rules) => {
  const errors = {}
  
  Object.keys(rules).forEach(field => {
    const fieldRules = rules[field]
    const value = values[field]
    
    if (fieldRules.required && !validateRequired(value)) {
      errors[field] = `${field} is required`
    } else if (value && fieldRules.email && !validateEmail(value)) {
      errors[field] = `${field} must be a valid email`
    } else if (value && fieldRules.phone && !validatePhone(value)) {
      errors[field] = `${field} must be a valid phone number`
    } else if (value && fieldRules.url && !validateUrl(value)) {
      errors[field] = `${field} must be a valid URL`
    } else if (value && fieldRules.minLength && value.length < fieldRules.minLength) {
      errors[field] = `${field} must be at least ${fieldRules.minLength} characters`
    } else if (value && fieldRules.maxLength && value.length > fieldRules.maxLength) {
      errors[field] = `${field} must be no more than ${fieldRules.maxLength} characters`
    }
  })
  
  return errors
}