<template>
  <div>
    <BmiForm
      title="Add BMI Record"
      button-label="Save BMI"
      @save-bmi="save"
    />

    <div v-if="message" class="notice" :class="ok ? 'good' : 'danger'">
      {{ message }}
    </div>

    <div v-if="rawResponse" class="card">
      <h3>Raw API response</h3>
      <pre class="code">{{ rawResponse }}</pre>
    </div>
  </div>
</template>

<script>
import BmiForm from '@/components/BmiForm.vue'
import { apiPost } from '@/services/api'

export default {
  name: 'AddBmiView',

  components: {
    BmiForm
  },

  data() {
    return {
      message: '',
      ok: false,
      rawResponse: ''
    }
  },

  methods: {
    async save(payload) {
      console.log('AddBmiView received payload:', payload)

      const result = await apiPost('/persons', payload)

      console.log('Backend result:', result)

      this.rawResponse = JSON.stringify(result, null, 2)
      this.ok = result.ok
      this.message = result.data.message || result.data.error || 'Request completed'
    }
  }
}
</script>