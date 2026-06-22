<template>
  <div>
    <div v-if="persons.length === 0" class="card">
      <p>No BMI records found.</p>
    </div>
    <div class="grid">
      <BmiCard
        v-for="person in persons"
        :key="person.id"
        :person="person"
        @delete="$emit('delete', $event)"
        @try-other-id="$emit('try-other-id', $event)"
      />
    </div>
  </div>
</template>

<script>
import BmiCard from './BmiCard.vue'

export default {
  name: 'BmiList',
  components: { BmiCard },
  props: {
    persons: { type: Array, default: () => [] }
  }
}
</script>
