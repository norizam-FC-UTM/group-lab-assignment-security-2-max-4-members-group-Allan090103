<template>
  <div class="app-shell">
    <AppNavbar />
    <main class="container">
      <router-view />
    </main>
  </div>
</template>

<script>
import AppNavbar from '@/components/AppNavbar.vue'

export default {
  name: 'App',
  components: { AppNavbar }
}
</script>
