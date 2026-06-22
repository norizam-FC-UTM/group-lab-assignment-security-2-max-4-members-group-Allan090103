<template>
  <header class="navbar">
    <div class="brand">Person BMI Security Lab</div>
    <nav class="nav-links">
      <router-link to="/investigation-guide">Investigation Guide</router-link>
      <router-link v-if="loggedIn" to="/dashboard">Dashboard</router-link>
      <router-link v-if="loggedIn" to="/my-bmi">My BMI</router-link>
      <router-link v-if="loggedIn" to="/add-bmi">Add BMI</router-link>
      <router-link v-if="loggedIn && (role === 'staff' || role === 'admin')" to="/staff/bmi-records">Staff Monitor</router-link>
      <router-link v-if="loggedIn && role === 'admin'" to="/admin/users">Admin Users</router-link>
      <router-link v-if="!loggedIn" to="/login">Login</router-link>
      <router-link v-if="!loggedIn" to="/register">Register</router-link>
      <button v-if="loggedIn" @click="doLogout">Logout</button>
    </nav>
  </header>
</template>

<script>
import { getStoredUser, getRole, isLoggedIn, logout } from '@/utils/auth'

export default {
  name: 'AppNavbar',
  computed: {
    loggedIn() {
      return isLoggedIn()
    },
    role() {
      return getRole()
    },
    user() {
      return getStoredUser()
    }
  },
  methods: {
    doLogout() {
      logout()
      this.$router.push('/login')
    }
  }
}
</script>
