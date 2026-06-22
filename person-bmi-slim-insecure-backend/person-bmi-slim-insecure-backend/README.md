# SECJ3483 Person BMI Insecure Slim Backend Starter

This is an **intentionally insecure** PHP Slim backend for the Person BMI security investigation lab.

It is designed to be used together with the Vue CLI insecure frontend starter.

## Important Warning

This backend contains deliberate security weaknesses for teaching purposes.
Do not use this code in a real application.

Students are expected to investigate, explain, fix, and prove the fixes.

## Technology

- PHP 8+
- Slim Framework 4
- MySQL
- PDO
- Vue CLI frontend expected at `http://localhost:8081`

## Setup

### 1. Install dependencies

```bash
composer install
```

### 2. Create database and seed data

Using phpMyAdmin or MySQL CLI, run:

```sql
sql/schema.sql
sql/seed.sql
```

The database name is:

```text
security_bmi_lab
```

### 3. Check database configuration

Open:

```text
src/config.php
```

Default settings:

```php
'db_host' => '127.0.0.1',
'db_name' => 'security_bmi_lab',
'db_user' => 'root',
'db_pass' => ''
```

Change these if your MySQL username/password is different.

### 4. Run backend

```bash
composer start
```

or:

```bash
php -S localhost:8080 -t public
```

Open:

```text
http://localhost:8080/api/health
```

Expected response:

```json
{
  "status": "ok",
  "api": "person-bmi-insecure-backend"
}
```

## Default Users

| Role | Email | Password |
|---|---|---|
| user | `ali@example.com` | `password123` |
| user | `sara@example.com` | `password123` |
| staff | `staff@example.com` | `password123` |
| admin | `admin@example.com` | `password123` |

## API Routes

### Public

| Method | Route | Purpose |
|---|---|---|
| GET | `/api/health` | Check API status |
| POST | `/api/register` | Register user |
| POST | `/api/login` | Login |

### Person BMI

| Method | Route | Purpose |
|---|---|---|
| GET | `/api/profile` | Return profile, insecurely trusts fake token/default user |
| GET | `/api/persons` | Return BMI records |
| POST | `/api/persons` | Create BMI record |
| GET | `/api/persons/{id}` | Get BMI record by ID |
| PUT | `/api/persons/{id}` | Update BMI record |
| DELETE | `/api/persons/{id}` | Delete BMI record |

### Staff/Admin

| Method | Route | Purpose |
|---|---|---|
| GET | `/api/staff/persons` | View all BMI records, no role check |
| GET | `/api/staff/persons/{id}` | View any BMI record, no role check |
| GET | `/api/admin/users` | View all users, no role check, exposes passwords |
| PUT | `/api/admin/users/{id}/role` | Change user role, no admin check |
| DELETE | `/api/admin/persons/{id}` | Delete any BMI record, no admin check |

## Intentional Weaknesses for Students to Investigate

1. No backend validation for BMI data.
2. Plain password storage.
3. Login uses string concatenation and is SQL Injection-prone.
4. Fake token is not a real signed JWT.
5. Token has no expiry.
6. Backend trusts unsigned token payload.
7. Backend trusts `user_id`, `bmi`, `category`, and `role` sent by frontend.
8. No protected route enforcement.
9. No owner-based access control.
10. No role-based access control.
11. Staff/admin routes do not check role.
12. API responses expose `password` and `password_hash`.
13. Detailed SQL errors are returned to users.
14. CORS allows all origins.
15. Update route allows unsafe field updates.

## Suggested Investigation Tests

### Test 1: Invalid BMI data

```http
POST /api/persons
Content-Type: application/json

{
  "name": "",
  "age": -5,
  "height": 0,
  "weight": -70,
  "bmi": 999,
  "category": "Normal",
  "user_id": 1,
  "notes": "invalid test"
}
```

### Test 2: SQL Injection idea

Try login with email:

```text
ali@example.com' -- 
```

and any password.

### Test 3: Access another user BMI record

```http
GET /api/persons/3
Authorization: Bearer <ali-token>
```

Ali should not be able to access Sara's record, but this insecure backend allows it.

### Test 4: Access admin route as normal user

```http
GET /api/admin/users
Authorization: Bearer <ali-token>
```

This insecure backend returns all users and exposes passwords.

### Test 5: Update protected fields

```http
PUT /api/persons/1
Content-Type: application/json

{
  "user_id": 2,
  "bmi": 10,
  "category": "Normal"
}
```

The insecure backend accepts unsafe field updates.

## Expected Student Fixes Later

Students should later improve this backend by adding:

- backend validation,
- password hashing with `password_hash()` and `password_verify()`,
- prepared statements,
- real JWT using `firebase/php-jwt`,
- token verification middleware/helper,
- role-based access control,
- owner-based access control,
- whitelist fields for updates,
- backend BMI calculation,
- safe API responses,
- safer error handling,
- stricter CORS.
