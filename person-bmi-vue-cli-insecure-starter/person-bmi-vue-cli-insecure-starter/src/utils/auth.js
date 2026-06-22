// INSECURE STARTER CODE
// This file intentionally trusts localStorage. Students should investigate why this is dangerous.

export function getToken() {
  return localStorage.getItem('jwt')
}

export function getStoredUser() {
  try {
    return JSON.parse(localStorage.getItem('user') || 'null')
  } catch (e) {
    return null
  }
}

export function isLoggedIn() {
  return !!getToken()
}

export function getRole() {
  const user = getStoredUser()
  return user?.role || null
}

export function setSession(token, user) {
  // INSECURE: token and user role are stored in localStorage and can be modified using DevTools.
  localStorage.setItem('jwt', token)
  localStorage.setItem('user', JSON.stringify(user))
}

export function logout() {
  localStorage.removeItem('jwt')
  localStorage.removeItem('user')
}
