// INSECURE STARTER CODE
// API helper for Vue CLI. Uses VUE_APP_API_BASE_URL instead of VITE_API_BASE_URL.

import { getToken } from '@/utils/auth'

const API_BASE = process.env.VUE_APP_API_BASE_URL || 'http://localhost:8080/api'

async function parseResponse(response) {
  let data = null
  try {
    data = await response.json()
  } catch (e) {
    data = { error: 'Invalid JSON response' }
  }

// Investigation question:
// This token is read from localStorage and sent to the backend.
// What could happen if an attacker can modify localStorage?
  return {
    ok: response.ok,
    status: response.status,
    data
  }
}

function authHeaders(includeJson = true) {
  const headers = {}
  if (includeJson) headers['Content-Type'] = 'application/json'

// Investigation question:
// This token is read from localStorage and sent to the backend.
// What could happen if an attacker can modify localStorage?
  const token = getToken()
  if (token) headers['Authorization'] = 'Bearer ' + token
  return headers
}

export async function apiGet(path) {
  const response = await fetch(API_BASE + path, {
    headers: authHeaders(false)
  })
  return parseResponse(response)
}

export async function apiPost(path, body) {
  const response = await fetch(API_BASE + path, {
    method: 'POST',
    headers: authHeaders(true),
    body: JSON.stringify(body)
  })
  return parseResponse(response)
}

export async function apiPut(path, body) {
  const response = await fetch(API_BASE + path, {
    method: 'PUT',
    headers: authHeaders(true),
    body: JSON.stringify(body)
  })
  return parseResponse(response)
}

export async function apiDelete(path) {
  const response = await fetch(API_BASE + path, {
    method: 'DELETE',
    headers: authHeaders(false)
  })
  return parseResponse(response)
}

export { API_BASE }
