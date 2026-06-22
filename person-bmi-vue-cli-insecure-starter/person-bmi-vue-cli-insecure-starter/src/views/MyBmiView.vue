<template>
  <div>
    <div class="card">
      <h2>My BMI Records</h2>
      <p class="notice warn">
        Investigation hint: Does the backend return only your records? What happens if you request another ID?
      </p>
      <div class="actions">
        <button class="btn" @click="loadMyRecords">Load My Records</button>
        <button class="btn secondary" @click="manualIdTest">Try Manual ID</button>
      </div>
    </div>

    <div v-if="message" class="notice" :class="ok ? 'good' : 'danger'">{{ message }}</div>
    <div v-if="rawResponse" class="card">
      <h3>Raw API response</h3>
      <div class="code">{{ rawResponse }}</div>
    </div>

    <BmiList :persons="persons" @delete="deleteRecord" @try-other-id="loadRecordById" />
  </div>
</template>

<script>
import BmiList from '@/components/BmiList.vue'
import { apiGet, apiDelete } from '@/services/api'

export default {
  name: 'MyBmiView',
  components: { BmiList },
  data() {
    return {
      persons: [],
      message: '',
      ok: false,
      rawResponse: ''
    }
  },
  mounted() {
    this.loadMyRecords()
  },
  methods: {
    async loadMyRecords() {
      const result = await apiGet('/persons')
      this.rawResponse = JSON.stringify(result, null, 2)
      this.ok = result.ok
      this.message = result.data.message || result.data.error || ''
      this.persons = Array.isArray(result.data) ? result.data : (result.data.persons || [])
    },
    async loadRecordById(id) {
      const result = await apiGet('/persons/' + id)
      this.rawResponse = JSON.stringify(result, null, 2)
      this.ok = result.ok
      this.message = result.ok ? 'Loaded record ID ' + id : (result.data.error || 'Failed')
      const record = result.data.person || result.data
      if (record && record.id) this.persons = [record]
    },
    manualIdTest() {
      const id = prompt('Enter BMI record ID to request directly:', '2')
      if (id) this.loadRecordById(id)
    },
    async deleteRecord(person) {
      if (!confirm('Delete record #' + person.id + '?')) return
      const result = await apiDelete('/persons/' + person.id)
      this.rawResponse = JSON.stringify(result, null, 2)
      this.ok = result.ok
      this.message = result.data.message || result.data.error || 'Request completed'
      this.loadMyRecords()
    }
  }
}
</script>
