<template>
  <form class="card" @submit.prevent="submitForm">
    <h2>{{ title }}</h2>

    <p class="notice warn">
      Insecure starter: BMI and category are calculated in frontend and sent to backend.
      Students should question this design.
    </p>

    <div class="grid">
      <div class="form-row">
        <label>Name</label>
        <input
          v-model="form.name"
          type="text"
          placeholder="Ali"
        />
      </div>

      <div class="form-row">
        <label>Age</label>
        <input
          v-model.number="form.age"
          type="number"
          placeholder="21"
        />
      </div>

      <div class="form-row">
        <label>Height (meters)</label>
        <input
          v-model="form.height"
          type="text"
          placeholder="1.70"
        />
      </div>

      <div class="form-row">
        <label>Weight (kg)</label>
        <input
          v-model="form.weight"
          type="text"
          placeholder="65"
        />
      </div>
    </div>

    <div class="form-row">
      <label>Notes</label>
      <textarea
        v-model="form.notes"
        placeholder="Try XSS payload here during investigation"
      ></textarea>
    </div>

    <div class="grid">
      <div class="form-row">
        <label>Frontend-calculated BMI</label>
        <input :value="computedBmi" readonly />
      </div>

      <div class="form-row">
        <label>Frontend-calculated Category</label>
        <input :value="computedCategory" readonly />
      </div>
    </div>

    <details class="notice danger">
      <summary>Insecure payload preview</summary>
      <pre class="code">{{ payloadPreview }}</pre>
      <p class="small">
        Notice that user_id, bmi and category are sent from frontend.
        Students should investigate whether backend should trust these fields.
      </p>
    </details>

    <button class="btn" type="submit">
      {{ buttonLabel }}
    </button>
  </form>
</template>

<script>
import { getStoredUser } from '@/utils/auth'

export default {
  name: 'BmiForm',

  emits: ['save-bmi'],

  props: {
    title: {
      type: String,
      default: 'BMI Form'
    },
    buttonLabel: {
      type: String,
      default: 'Submit'
    },
    initialValue: {
      type: Object,
      default: () => ({
        name: '',
        age: 21,
        height: '1.70',
        weight: '65',
        notes: ''
      })
    }
  },

  data() {
    return {
      form: {
        name: this.initialValue.name || '',
        age:
          this.initialValue.age !== undefined && this.initialValue.age !== ''
            ? Number(this.initialValue.age)
            : 21,
        height:
          this.initialValue.height !== undefined && this.initialValue.height !== ''
            ? String(this.initialValue.height)
            : '1.70',
        weight:
          this.initialValue.weight !== undefined && this.initialValue.weight !== ''
            ? String(this.initialValue.weight)
            : '65',
        notes: this.initialValue.notes || ''
      }
    }
  },

  computed: {
    // Investigation question:
// Should BMI and category be calculated by the frontend or backend?
    computedBmi() {
      const h = parseFloat(this.form.height)
      const w = parseFloat(this.form.weight)

      if (!h || !w) {
        return ''
      }

      return (w / (h * h)).toFixed(2)
    },

    computedCategory() {
      const bmi = parseFloat(this.computedBmi)

      if (!bmi) {
        return ''
      }

      if (bmi < 18.5) {
        return 'Underweight'
      }

      if (bmi < 25) {
        return 'Normal'
      }

      if (bmi < 30) {
        return 'Overweight'
      }

      return 'Obese'
    },

    payloadPreview() {
      return JSON.stringify(this.buildPayload(), null, 2)
    }
  },

  methods: {
    buildPayload() {
      const user = getStoredUser()
// Investigation question:
// This payload sends user_id, bmi, category, and role from frontend.
      return {
        name: this.form.name,
        age: this.form.age,
        height: this.form.height,
        weight: this.form.weight,
        notes: this.form.notes,

        user_id: user?.id,
        bmi: this.computedBmi || 0,
        category: this.computedCategory || 'Unknown',
        role: user?.role
      }
    },

    submitForm() {
      const payload = this.buildPayload()

      console.log('BmiForm payload sent to parent:', payload)

      this.$emit('save-bmi', payload)
    }
  }
}
</script>