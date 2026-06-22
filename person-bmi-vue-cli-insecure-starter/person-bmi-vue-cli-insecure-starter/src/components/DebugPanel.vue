<template>
  <div class="card debug-panel">
    <h3>Insecure Debug Panel</h3>
    <p class="notice danger">
      This panel is intentionally insecure. It exposes localStorage values to stimulate investigation.
    </p>
    <p><strong>API Base:</strong> {{ apiBase }}</p>
    <p><strong>JWT token from localStorage:</strong></p>
    <div class="code">{{ token || '(none)' }}</div>
    <p><strong>User object from localStorage:</strong></p>
    <div class="code">{{ userText }}</div>
    <div class="actions">
      <button class="btn warning" @click="makeMeAdmin">Modify localStorage role to admin</button>
      <button class="btn secondary" @click="refresh">Refresh panel</button>
    </div>
    <p class="small">
      Investigation hint: Is frontend role checking trustworthy if users can modify localStorage?
    </p>
  </div>
</template>

<script>
import { API_BASE } from '@/services/api'

export default {
  name: 'DebugPanel',
  data() {
    return {
      token: localStorage.getItem('jwt'),
      userText: localStorage.getItem('user') || '(none)',
      apiBase: API_BASE
    }
  },
  methods: {
    refresh() {
      this.token = localStorage.getItem('jwt')
      this.userText = localStorage.getItem('user') || '(none)'
    },
    makeMeAdmin() {
      const raw = localStorage.getItem('user')
      if (!raw) return alert('No user in localStorage.')
      const user = JSON.parse(raw)
      user.role = 'admin'
      localStorage.setItem('user', JSON.stringify(user))
      this.refresh()
      alert('Frontend role changed to admin in localStorage. Try opening Admin Users page.')
    }
  }
}
</script>
