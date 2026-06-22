<template>
  <div class="card">
    <h2>Login</h2>
    <p class="notice warn">
      Insecure starter: login stores JWT and user role in localStorage. Students should investigate the risk.
    </p>

    <form @submit.prevent="login">
      <div class="form-row">
        <label>Email</label>
        <input v-model="email" type="email" placeholder="use email as id" />
      </div>
      <div class="form-row">
        <label>Password</label>
        <input v-model="password" type="password" placeholder="password" />
      </div>
      <button class="btn" type="submit">Login</button>
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
import { setSession } from '@/utils/auth'

export default {
  name: 'LoginView',
  data() {
    return {
      email: '',
      password: '',
      message: '',
      ok: false,
      rawResponse: ''
    }
  },
  methods: {
    async login() {
      const result = await apiPost('/login', {
        email: this.email,
        password: this.password
      })

      this.rawResponse = JSON.stringify(result, null, 2)
      this.ok = result.ok

      if (result.ok && result.data.token) {
        // INSECURE: frontend trusts user object and role returned/stored locally.
        const user = result.data.user || {
          id: result.data.user_id || 1,
          name: result.data.name || this.email,
          email: this.email,
          role: result.data.role || 'user'
        }

        setSession(result.data.token, user)
        this.message = 'Login successful. Session stored in localStorage.'
        this.$router.push('/dashboard')
      } else {
        // INSECURE: display backend error directly.
        this.message = result.data.error || 'Login failed'
      }
    }
  }
}
</script>
