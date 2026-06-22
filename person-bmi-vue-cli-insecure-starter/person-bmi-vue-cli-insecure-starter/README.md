# Person BMI Vue CLI Insecure Starter

This is an intentionally insecure Vue CLI frontend starter for the SECJ3483 final security activity.

## Technology

- Vue 3
- Vue CLI
- Vue Router 4
- Fetch API
- Backend expected at `http://localhost:8080/api`

## Run

```bash
npm install
npm run serve
```

Default Vue CLI dev server:

```text
http://localhost:8081
```

## Environment

This project uses Vue CLI environment variable format:

```text
VUE_APP_API_BASE_URL=http://localhost:8080/api
```

Do not use Vite-style `VITE_API_BASE_URL` for this project.

## Intended Insecure Features

This starter intentionally contains weaknesses for students to investigate:

1. Route guard trusts `localStorage` role.
2. JWT token is stored in `localStorage`.
3. Debug panel exposes token and user object.
4. Debug panel can change role to admin in `localStorage`.
5. Register form allows users to choose `admin` or `staff` role.
6. BMI and category are calculated in frontend and sent to backend.
7. Frontend sends `user_id`, `role`, `bmi`, and `category` in BMI payload.
8. Notes are rendered using `v-html`, causing XSS risk.
9. API errors are displayed directly to users.
10. Staff/admin visibility depends on frontend checks.

## Student Investigation Philosophy

```text
Break it → Explain it → Fix it → Prove it
```

Students should first identify and explain weaknesses before fixing them.

## Expected Backend Routes

```text
POST   /api/register
POST   /api/login
GET    /api/profile
GET    /api/persons
POST   /api/persons
GET    /api/persons/{id}
PUT    /api/persons/{id}
DELETE /api/persons/{id}
GET    /api/staff/persons
GET    /api/staff/persons/{id}
GET    /api/admin/users
PUT    /api/admin/users/{id}/role
DELETE /api/admin/persons/{id}
```

## Important Teaching Reminder

Frontend weaknesses are included intentionally. The final security decision must happen at the backend.

Examples:

- Vue route guard hides pages, but backend route protection protects data.
- Frontend validation helps users, but backend validation protects the system.
- JWT identifies the user only after backend verification.
- Role in localStorage must not be trusted by backend.
