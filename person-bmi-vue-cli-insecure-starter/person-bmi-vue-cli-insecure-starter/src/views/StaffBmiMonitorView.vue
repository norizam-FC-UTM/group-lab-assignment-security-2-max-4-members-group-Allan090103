<template>
  <div>
    <div class="card">
      <h2>Staff BMI Monitor</h2>
      <p class="notice danger">
        Insecure starter: frontend route guard trusts localStorage role. Backend must still enforce role.
      </p>
      <button class="btn" @click="loadAll">Load All BMI Records</button>
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
  name: 'StaffBmiMonitorView',
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
    this.loadAll()
  },
  methods: {
    async loadAll() {
      const result = await apiGet('/staff/persons')
      this.rawResponse = JSON.stringify(result, null, 2)
      this.ok = result.ok
      this.message = result.data.message || result.data.error || ''
      this.persons = Array.isArray(result.data) ? result.data : (result.data.persons || [])
    },
    async loadRecordById(id) {
      const result = await apiGet('/staff/persons/' + id)
      this.rawResponse = JSON.stringify(result, null, 2)
      this.ok = result.ok
      this.message = result.ok ? 'Loaded staff record ID ' + id : (result.data.error || 'Failed')
    },
    async deleteRecord(person) {
      // INSECURE UX: staff UI offers delete; backend should decide if staff is allowed.
      if (!confirm('Try delete record #' + person.id + '?')) return
      const result = await apiDelete('/persons/' + person.id)
      this.rawResponse = JSON.stringify(result, null, 2)
      this.ok = result.ok
      this.message = result.data.message || result.data.error || 'Request completed'
      this.loadAll()
    }
  }
}
</script>
