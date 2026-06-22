<template>
  <div>
    <div class="card">
      <h2>Security Investigation Guide</h2>
      <p class="notice warn">
        Your task is to investigate this intentionally insecure frontend and backend behavior.
        Do not fix immediately. First, observe, explain, then propose the fix.
      </p>
      <h3>Lab Philosophy</h3>
      <div class="code">Break it → Explain it → Fix it → Prove it</div>
    </div>

    <div class="card">
      <h3>Mission 1: Break BMI Validation</h3>
      <p>Try adding BMI record with invalid values.</p>
      <div class="code">{
  "name": "",
  "age": -5,
  "height": 0,
  "weight": -70,
  "notes": "testing invalid BMI"
}</div>
      <p><strong>Question:</strong> Should the frontend or backend enforce this rule?</p>
    </div>

    <div class="card">
      <h3>Mission 2: Access Another User's BMI Record</h3>
      <p>Login as one user, then try opening another BMI ID using the button or Postman.</p>
      <div class="code">GET /api/persons/2
Authorization: Bearer token-of-user-1</div>
      <p><strong>Expected secure behavior:</strong> 403 Access denied unless owner, staff, or admin.</p>
    </div>

    <div class="card">
      <h3>Mission 3: Bypass Frontend Role Guard</h3>
      <p>Open Dashboard → Debug Panel → click “Modify localStorage role to admin”.</p>
      <p>Then try opening Admin Users page.</p>
      <p><strong>Question:</strong> If frontend now shows admin page, is the backend secure?</p>
    </div>

    <div class="card">
      <h3>Mission 4: Manipulate Protected Fields</h3>
      <p>Observe the BMI form payload preview. It sends user_id, role, bmi and category from frontend.</p>
      <p><strong>Question:</strong> Which fields should be controlled by backend only?</p>
    </div>

    <div class="card">
      <h3>Mission 5: Trigger XSS</h3>
      <p>Enter this in notes:</p>
      <div class="code">&lt;img src=x onerror="alert('XSS')"&gt;</div>
      <p>This app intentionally renders notes using <code>v-html</code>.</p>
      <p><strong>Fix idea:</strong> use Vue interpolation: <code v-pre>{{ person.notes }}</code></p>
    </div>

    <div class="card">
      <h3>Mission 6: Check Sensitive Data Exposure</h3>
      <p>Look at raw API responses. Does the API return password_hash or private fields?</p>
      <p><strong>Fix idea:</strong> backend should select and return only necessary fields.</p>
    </div>

    <div class="card">
      <h3>Investigation Table</h3>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Test</th><th>Expected Secure Behavior</th><th>Actual Result</th><th>Weakness?</th><th>Possible Fix</th>
            </tr>
          </thead>
          <tbody>
            <tr><td>Invalid BMI</td><td>Backend rejects</td><td></td><td></td><td>Backend validation</td></tr>
            <tr><td>Other user BMI</td><td>403 Access denied</td><td></td><td></td><td>Owner check</td></tr>
            <tr><td>Admin page as user</td><td>Backend blocks</td><td></td><td></td><td>Backend RBAC</td></tr>
            <tr><td>XSS notes</td><td>Shown as text</td><td></td><td></td><td>Safe rendering</td></tr>
            <tr><td>password_hash response</td><td>Hidden</td><td></td><td></td><td>Safe API response</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
export default { name: 'InvestigationGuideView' }
</script>
