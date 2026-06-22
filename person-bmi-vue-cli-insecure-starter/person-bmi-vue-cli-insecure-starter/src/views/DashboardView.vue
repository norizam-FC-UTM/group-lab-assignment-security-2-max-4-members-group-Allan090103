<template>
  // Investigation question:
// If the frontend role can be changed here, what happens to staff/admin pages?
// Does changing frontend role also change real backend permission?
  <div>
    <div class="card">
      <h2>Dashboard</h2>
      <p>Welcome to the intentionally insecure Person BMI starter frontend.</p>
      <p><strong>Current user:</strong> {{ user?.name || 'unknown' }} <UserRoleBadge :role="user?.role" /></p>
      <p class="notice warn">
        Use this app for security investigation. Students should identify weaknesses before fixing them.
      </p>
    </div>

    <div class="grid">
      <div class="card">
        <h3>Suggested Investigation</h3>
        <ol>
          <li>Try invalid BMI data.</li>
          <li>Try XSS payload in notes.</li>
          <li>Check localStorage values.</li>
          <li>Modify localStorage role to admin.</li>
          <li>Try staff/admin pages as a normal user.</li>
          <li>Use Postman/Thunder Client to bypass frontend.</li>
        </ol>
        <router-link class="btn" to="/investigation-guide">Open Guide</router-link>
      </div>

      <DebugPanel />
    </div>
  </div>
</template>

<script>
import DebugPanel from '@/components/DebugPanel.vue'
import UserRoleBadge from '@/components/UserRoleBadge.vue'
import { getStoredUser } from '@/utils/auth'

export default {
  name: 'DashboardView',
  components: { DebugPanel, UserRoleBadge },
  computed: {
    user() {
      return getStoredUser()
    }
  }
}
</script>
