<template>
  <div class="card">
    <h2>Register</h2>
    <p class="notice danger">
      Insecure starter: this form allows users to choose role. Students should investigate why this is dangerous.
    </p>

    <form @submit.prevent="register">
      <div class="grid">
        <div class="form-row">
          <label>Name</label>
          <input v-model="form.name" placeholder="New User" />
        </div>
        <div class="form-row">
          <label>Email</label>
          <input v-model="form.email" type="email" placeholder="new@example.com" />
        </div>
      </div>
      <div class="grid">
        <div class="form-row">
          <label>Password</label>
          <input v-model="form.password" type="password" placeholder="password123" />
        </div>
        <div class="form-row">
          <label>Role</label>
          <select v-model="form.role">
            <option value="user">user</option>
            <option value="staff">staff</option>
            <option value="admin">admin</option>
          </select>
        </div>
      </div>
      <button class="btn" type="submit">Register</button>
    </form>

    <div v-if="message" class="notice" :class="ok ? 'good' : 'danger'">{{ message }}</div>
    <div v-if="rawResponse" class="card">
      <h3>Raw API response</h3>
      <div class="code">{{ rawResponse }}</div>
    </div>
  </div>
</template>

<script>
import { apiPost } from '@/services/api'

export default {
  name: 'RegisterView',
  data() {
    return {
      form: {
        name: '',
        email: '',
        password: '',
        role: 'admin'
      },
      message: '',
      ok: false,
      rawResponse: ''
    }
  },
  methods: {
    async register() {
      // INSECURE: frontend sends role to backend. Backend should not trust this for normal registration.
      const result = await apiPost('/register', this.form)
      this.rawResponse = JSON.stringify(result, null, 2)
      this.ok = result.ok
      this.message = result.data.message || result.data.error || 'Request completed'
    }
  }
}
</script>
