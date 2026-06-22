<template>
  <span class="badge" :class="role">{{ role || 'guest' }}</span>
</template>

<script>
export default {
  name: 'UserRoleBadge',
  props: {
    role: {
      type: String,
      default: 'guest'
    }
  }
}
</script>
