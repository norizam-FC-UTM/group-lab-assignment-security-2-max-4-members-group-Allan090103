<template>
  <div>
    <div class="card">
      <h2>Admin Users</h2>
      <p class="notice danger">
        Insecure starter: If you modified localStorage role to admin, this page may become visible. Backend must still block non-admin tokens.
      </p>
      <button class="btn" @click="loadUsers">Load Users</button>
    </div>

    <div v-if="message" class="notice" :class="ok ? 'good' : 'danger'">{{ message }}</div>
    <div v-if="rawResponse" class="card">
      <h3>Raw API response</h3>
      <div class="code">{{ rawResponse }}</div>
    </div>

    <div class="card table-wrap" v-if="users.length">
      <table>
        <thead>
          <tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Dangerous Role Update</th></tr>
        </thead>
        <tbody>
          <tr v-for="u in users" :key="u.id">
            <td>{{ u.id }}</td>
            <td>{{ u.name }}</td>
            <td>{{ u.email }}</td>
            <td><UserRoleBadge :role="u.role" /></td>
            <td>
              <select v-model="u.newRole">
                <option value="user">user</option>
                <option value="staff">staff</option>
                <option value="admin">admin</option>
              </select>
              <button class="btn warning" @click="changeRole(u)">Change Role</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import { apiGet, apiPut } from '@/services/api'
import UserRoleBadge from '@/components/UserRoleBadge.vue'

export default {
  name: 'AdminUsersView',
  components: { UserRoleBadge },
  data() {
    return {
      users: [],
      message: '',
      ok: false,
      rawResponse: ''
    }
  },
  mounted() {
    this.loadUsers()
  },
  methods: {
    async loadUsers() {
      const result = await apiGet('/admin/users')
      this.rawResponse = JSON.stringify(result, null, 2)
      this.ok = result.ok
      this.message = result.data.message || result.data.error || ''
      const list = Array.isArray(result.data) ? result.data : (result.data.users || [])
      this.users = list.map(u => ({ ...u, newRole: u.role }))
    },
    async changeRole(user) {
      // Backend must verify admin role; frontend control is not enough.
      const result = await apiPut('/admin/users/' + user.id + '/role', { role: user.newRole })
      this.rawResponse = JSON.stringify(result, null, 2)
      this.ok = result.ok
      this.message = result.data.message || result.data.error || 'Request completed'
      this.loadUsers()
    }
  }
}
</script>
