<template>
  <div class="card">
    <h2>Unauthorized</h2>
    <p>You are blocked by the frontend route guard.</p>
    <p class="notice warn">
      Investigation question: Is this enough to secure the backend API? Can you still call the endpoint using Postman or Thunder Client?
    </p>
    <router-link class="btn" to="/dashboard">Back to Dashboard</router-link>
  </div>
</template>

<script>
export default { name: 'UnauthorizedView' }
</script>
