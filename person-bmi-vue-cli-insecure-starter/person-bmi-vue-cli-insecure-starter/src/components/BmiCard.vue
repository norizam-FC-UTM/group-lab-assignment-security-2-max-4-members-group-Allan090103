<template>
  <div class="card">
    <h3>{{ person.name }} <span class="small">#{{ person.id }}</span></h3>
    <p><strong>Owner user_id:</strong> {{ person.user_id }}</p>
    <p><strong>Age:</strong> {{ person.age }}</p>
    <p><strong>Height:</strong> {{ person.height }} m</p>
    <p><strong>Weight:</strong> {{ person.weight }} kg</p>
    <p><strong>BMI:</strong> {{ person.bmi }}</p>
    <p><strong>Category:</strong> {{ person.category }}</p>

    <!-- FIX 11: Use {{ }} interpolation - Vue escapes HTML, prevents XSS -->
    <p><strong>Notes:</strong> {{ person.notes }}</p>

    <div class="actions">
      <button class="btn danger" @click="$emit('delete', person)">Delete</button>
      <button class="btn secondary" @click="tryOtherId">Try ID + 1</button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'BmiCard',
  props: {
    person: { type: Object, required: true }
  },
  methods: {
    tryOtherId() {
      this.$emit('try-other-id', Number(this.person.id) + 1)
    }
  }
}
</script>
