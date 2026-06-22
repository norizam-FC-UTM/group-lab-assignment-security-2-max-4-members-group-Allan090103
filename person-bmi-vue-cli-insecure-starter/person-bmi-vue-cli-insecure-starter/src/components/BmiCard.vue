<template>
  <div class="card">
    <h3>{{ person.name }} <span class="small">#{{ person.id }}</span></h3>
    <p><strong>Owner user_id:</strong> {{ person.user_id }}</p>
    <p><strong>Age:</strong> {{ person.age }}</p>
    <p><strong>Height:</strong> {{ person.height }} m</p>
    <p><strong>Weight:</strong> {{ person.weight }} kg</p>
    <p><strong>BMI:</strong> {{ person.bmi }}</p>
    <p><strong>Category:</strong> {{ person.category }}</p>

    <div class="notice danger">
      <strong>Notes rendered with v-html intentionally:</strong>
      <!-- INSECURE: v-html can execute user-controlled HTML/script-like payloads. -->
       <!-- Investigation question:
     This renders notes as HTML.
     What happens if notes contains an XSS payload? -->
      <div v-html="person.notes"></div> 
      // i.e safer to use <p>{{ person.notes }}</p>
    </div>

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
