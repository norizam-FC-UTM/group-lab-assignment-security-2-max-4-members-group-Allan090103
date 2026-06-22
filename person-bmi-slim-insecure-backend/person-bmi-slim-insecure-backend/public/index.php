<?php
// ==========================================================
// SECJ3483 Web Technology
// Person BMI Insecure Slim Backend Starter
// ==========================================================
// NOTA:
// This backend is intentionally insecure.
// provided for investigation and fixing during lab activiy this week.
// Do NOT use this code in real applications.
// ==========================================================

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/db.php';

$app = AppFactory::create();

// Required for JSON/form body parsing in Slim 4.
$app->addBodyParsingMiddleware();

// Helpful for development error display.
// INSECURE: In production, detailed errors should not be shown to users.
$app->addErrorMiddleware(true, true, true);

// ----------------------------------------------------------
// CORS for Vue CLI frontend
// ----------------------------------------------------------
$app->add(function (Request $request, $handler) {
    if ($request->getMethod() === 'OPTIONS') {
        $response = new \Slim\Psr7\Response();
    } else {
        $response = $handler->handle($request);
    }

    return $response
        ->withHeader('Access-Control-Allow-Origin', '*') // INSECURE: convenient untuk aktiviti lab, tidak untuk production.
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->withHeader('Access-Control-Allow-Credentials', 'false');
});

// ----------------------------------------------------------
// Helper functions
// ----------------------------------------------------------
function jsonResponse(Response $response, $data, int $status = 200): Response
{
    $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));

    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus($status);
}

//
function getRequestData(Request $request): array
{
    $data = $request->getParsedBody();

    if (is_array($data) && !empty($data)) {
        return $data;
    }

    $rawBody = (string) $request->getBody();

    if ($rawBody !== '') {
        $jsonData = json_decode($rawBody, true);

        if (is_array($jsonData)) {
            return $jsonData;
        }
    }

    return is_array($data) ? $data : [];
}

// INSECURE: This is NOT a real JWT.
// This is just base64 JSON. You should replace this with real signed JWT sebagai pembaikan.
function createFakeToken(array $user): string
{
    $payload = [
        'user_id' => $user['id'],
        'role' => $user['role'],
        'email' => $user['email'],
        'note' => 'INSECURE_FAKE_TOKEN_NO_SIGNATURE_NO_EXPIRY'
    ];

    return base64_encode(json_encode($payload));
}

// INSECURE: This trusts an unsigned, editable token.
// You should replace this with proper JWT verification.
function getFakeUserFromToken(Request $request): ?array
{
    $auth = $request->getHeaderLine('Authorization');

    if (!$auth || !preg_match('/Bearer\s+(\S+)/', $auth, $matches)) {
        return null;
    }
    $json = base64_decode($matches[1], true);

    if (!$json) {
        return null;
    }
    $payload = json_decode($json, true);
    return is_array($payload) ? $payload : null;
}

function exposeException(Response $response, Throwable $e): Response
{
    // INSECURE: exposes detailed internal error to API client.
    return jsonResponse($response, [
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], 500);
}

// ----------------------------------------------------------
// Root routes 
// ----------------------------------------------------------
$app->get('/', function (Request $request, Response $response) {
    return jsonResponse($response, [
        'message' => 'Person BMI Insecure Slim Backend Starter',
        'warning' => 'This backend is intentionally insecure for classroom investigation.'
    ]);
});

$app->get('/api/health', function (Request $request, Response $response) {
    return jsonResponse($response, [
        'status' => 'ok',
        'api' => 'person-bmi-insecure-backend'
    ]);
});

// ----------------------------------------------------------
// Public route: Register
// ----------------------------------------------------------
$app->post('/api/register', function (Request $request, Response $response) {
    try {
        $pdo = getPDO();
        $data = getRequestData($request);

        // INSECURE:
        // - No backend validation.
        // - Role is accepted from frontend, so user can register as admin/staff.
        // - Password is stored as plain text.
        // - password_hash is intentionally filled with the same plain password for investigation.
        $name = $data['name'] ?? '';
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        $role = $data['role'] ?? 'user';

        $sql = "INSERT INTO users (name, email, password, password_hash, role)
                VALUES ('$name', '$email', '$password', '$password', '$role')";

        // INSECURE: direct SQL execution with user input.
        $pdo->exec($sql);
        $id = $pdo->lastInsertId();

        // INSECURE: returns password/password_hash.
        $user = $pdo->query("SELECT * FROM users WHERE id = $id")->fetch();

        return jsonResponse($response, [
            'message' => 'User registered. This route is intentionally insecure.',
            'user' => $user,
            'debug_received_body' => $data,
            'debug_sql' => $sql
        ], 201);
    } catch (Throwable $e) {
        return exposeException($response, $e);
    }
});

// ----------------------------------------------------------
// Public route: Login
// ----------------------------------------------------------
$app->post('/api/login', function (Request $request, Response $response) {
    try {
        $pdo = getPDO();
        $data = getRequestData($request);

        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        // INSECURE:
        // - SQL Injection risk.
        // - Plain password check.
        // - No password_hash/password_verify.
        // Example test: email = ali@example.com' --
        $sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password' LIMIT 1";
        $user = $pdo->query($sql)->fetch();

        if (!$user) {
            return jsonResponse($response, [
                'error' => 'Invalid login',
                'debug_received_body' => $data,
                'debug_sql' => $sql
            ], 401);
        }

        // INSECURE: fake unsigned token with no expiry.
        $token = createFakeToken($user);

        return jsonResponse($response, [
            'message' => 'Login successful. This token is intentionally insecure.',
            'token' => $token,
            'user' => $user, // INSECURE: exposes all user fields.
            'debug_received_body' => $data,
            'debug_sql' => $sql
        ]);
    } catch (Throwable $e) {
        return exposeException($response, $e);
    }
});

// ----------------------------------------------------------
// Protected-ish route: Profile
// ----------------------------------------------------------
$app->get('/api/profile', function (Request $request, Response $response) {
    try {
        $pdo = getPDO();

        // INSECURE:
        // If token missing, defaults to user 1.
        // If token exists, trusts unsigned editable token.
        $fakeUser = getFakeUserFromToken($request);
        $userId = $fakeUser['user_id'] ?? 1;

        // INSECURE: SELECT * returns password/password_hash.
        $user = $pdo->query("SELECT * FROM users WHERE id = $userId")->fetch();

        return jsonResponse($response, [
            'message' => 'Profile returned. This route trusts insecure token/default user.',
            'user' => $user,
            'token_payload_trusted_by_backend' => $fakeUser
        ]);
    } catch (Throwable $e) {
        return exposeException($response, $e);
    }
});

// ----------------------------------------------------------
// BMI routes
// ----------------------------------------------------------
$app->get('/api/persons', function (Request $request, Response $response) {
    try {
        $pdo = getPDO();

        // INSECURE:
        // Trusts unsigned token. If no token, returns all records.
        // Also accepts ?user_id= to override owner.
        $fakeUser = getFakeUserFromToken($request);
        $params = $request->getQueryParams();
        $userId = $params['user_id'] ?? ($fakeUser['user_id'] ?? null);

        if ($userId) {
            $sql = "SELECT * FROM persons WHERE user_id = $userId ORDER BY id DESC";
        } else {
            $sql = "SELECT * FROM persons ORDER BY id DESC";
        }

        $persons = $pdo->query($sql)->fetchAll();

        return jsonResponse($response, [
            'message' => 'BMI records returned. This route is intentionally weak.',
            'persons' => $persons,
            'debug_sql' => $sql
        ]);
    } catch (Throwable $e) {
        return exposeException($response, $e);
    }
});

$app->post('/api/persons', function (Request $request, Response $response) {
    try {
        $pdo = getPDO();
        $data = getRequestData($request);

        // INSECURE:
        // - No backend validation.
        // - Trusts user_id from frontend.
        // - Trusts bmi and category from frontend.
        // - Does not calculate BMI at backend.
        $user_id = $data['user_id'] ?? 1;
        $name = $data['name'] ?? '';
        $age = $data['age'] ?? 0;
        $height = $data['height'] ?? 0;
        $weight = $data['weight'] ?? 0;
        $bmi = $data['bmi'] ?? 0;
        $category = $data['category'] ?? '';
        $notes = $data['notes'] ?? '';

        $sql = "INSERT INTO persons (user_id, name, age, height, weight, bmi, category, notes)
                VALUES ($user_id, '$name', $age, $height, $weight, $bmi, '$category', '$notes')";

        $pdo->exec($sql);
        $id = $pdo->lastInsertId();

        $person = $pdo->query("SELECT * FROM persons WHERE id = $id")->fetch();

        return jsonResponse($response, [
            'message' => 'BMI record created. This route trusts frontend data.',
            'person' => $person,
            'debug_received_body' => $data,
            'debug_sql' => $sql
        ], 201);
    } catch (Throwable $e) {
        return exposeException($response, $e);
    }
});

$app->get('/api/persons/{id}', function (Request $request, Response $response, array $args) {
    try {
        $pdo = getPDO();
        $id = $args['id'];

        // TODO: Review whether this route should allow all users to access any record.
        $sql = "SELECT * FROM persons WHERE id = $id";
        $person = $pdo->query($sql)->fetch();

        if (!$person) {
            return jsonResponse($response, ['error' => 'Record not found'], 404);
        }

        return jsonResponse($response, [
            'message' => 'Record returned without ownership check.',
            'person' => $person,
            'debug_sql' => $sql
        ]);
    } catch (Throwable $e) {
        return exposeException($response, $e);
    }
});

$app->put('/api/persons/{id}', function (Request $request, Response $response, array $args) {
    try {
        $pdo = getPDO();
        $id = $args['id'];
        $data = getRequestData($request);

        // INSECURE MASS ASSIGNMENT:
        // Updates almost any field sent by the frontend.
        // You should whitelist allowed fields and calculate bmi/category at backend.
        $allowedInInsecureStarter = [
            'user_id',
            'name',
            'age',
            'height',
            'weight',
            'bmi',
            'category',
            'notes'
        ];

        $sets = [];

        foreach ($allowedInInsecureStarter as $field) {
            if (array_key_exists($field, $data)) {
                $value = $data[$field];

                if (is_numeric($value)) {
                    $sets[] = "$field = $value";
                } else {
                    $escaped = str_replace("'", "''", (string) $value);
                    $sets[] = "$field = '$escaped'";
                }
            }
        }

        if (!$sets) {
            return jsonResponse($response, [
                'error' => 'No fields to update',
                'debug_received_body' => $data
            ], 400);
        }

        $sql = "UPDATE persons SET " . implode(', ', $sets) . " WHERE id = $id";
        $pdo->exec($sql);

        $person = $pdo->query("SELECT * FROM persons WHERE id = $id")->fetch();

        return jsonResponse($response, [
            'message' => 'BMI record updated. This route allows unsafe field updates.',
            'person' => $person,
            'debug_received_body' => $data,
            'debug_sql' => $sql
        ]);
    } catch (Throwable $e) {
        return exposeException($response, $e);
    }
});

$app->delete('/api/persons/{id}', function (Request $request, Response $response, array $args) {
    try {
        $pdo = getPDO();
        $id = $args['id'];

        // INSECURE: No auth, no ownership check, no role check.
        $sql = "DELETE FROM persons WHERE id = $id";
        $pdo->exec($sql);

        return jsonResponse($response, [
            'message' => 'BMI record deleted without role or ownership check.',
            'debug_sql' => $sql
        ]);
    } catch (Throwable $e) {
        return exposeException($response, $e);
    }
});

// ----------------------------------------------------------
// Staff routes
// ----------------------------------------------------------
$app->get('/api/staff/persons', function (Request $request, Response $response) {
    try {
        $pdo = getPDO();

        // INSECURE: No staff/admin role check.
        $sql = "SELECT persons.*, users.email AS owner_email, users.role AS owner_role
                FROM persons
                JOIN users ON persons.user_id = users.id
                ORDER BY persons.id DESC";

        $persons = $pdo->query($sql)->fetchAll();

        return jsonResponse($response, [
            'message' => 'All BMI records returned without staff role check.',
            'persons' => $persons,
            'debug_sql' => $sql
        ]);
    } catch (Throwable $e) {
        return exposeException($response, $e);
    }
});

$app->get('/api/staff/persons/{id}', function (Request $request, Response $response, array $args) {
    try {
        $pdo = getPDO();
        $id = $args['id'];

        // INSECURE - Review whether this route should allow all users
        $sql = "SELECT persons.*, users.email AS owner_email, users.role AS owner_role
                FROM persons
                JOIN users ON persons.user_id = users.id
                WHERE persons.id = $id";

        $person = $pdo->query($sql)->fetch();

        if (!$person) {
            return jsonResponse($response, ['error' => 'Record not found'], 404);
        }

        return jsonResponse($response, [
            'message' => 'Staff record returned without role check.',
            'person' => $person,
            'debug_sql' => $sql
        ]);
    } catch (Throwable $e) {
        return exposeException($response, $e);
    }
});

// ----------------------------------------------------------
// Admin routes
// ----------------------------------------------------------
$app->get('/api/admin/users', function (Request $request, Response $response) {
    try {
        $pdo = getPDO();

        // INSECURE:
        // - No admin role check.
        // - SELECT * exposes password/password_hash.
        $sql = "SELECT * FROM users ORDER BY id ASC";
        $users = $pdo->query($sql)->fetchAll();

        return jsonResponse($response, [
            'message' => 'All users returned without admin role check. Sensitive fields exposed.',
            'users' => $users,
            'debug_sql' => $sql
        ]);
    } catch (Throwable $e) {
        return exposeException($response, $e);
    }
});

$app->put('/api/admin/users/{id}/role', function (Request $request, Response $response, array $args) {
    try {
        $pdo = getPDO();
        $id = $args['id'];
        $data = getRequestData($request);
        $role = $data['role'] ?? 'user';

        // INSECURE: No admin role check. Anyone can change any user role.
        $sql = "UPDATE users SET role = '$role' WHERE id = $id";
        $pdo->exec($sql);

        $user = $pdo->query("SELECT * FROM users WHERE id = $id")->fetch();

        return jsonResponse($response, [
            'message' => 'User role changed without admin verification.',
            'user' => $user,
            'debug_received_body' => $data,
            'debug_sql' => $sql
        ]);
    } catch (Throwable $e) {
        return exposeException($response, $e);
    }
});

$app->delete('/api/admin/persons/{id}', function (Request $request, Response $response, array $args) {
    try {
        $pdo = getPDO();
        $id = $args['id'];

        // INSECURE: No admin role check.
        $sql = "DELETE FROM persons WHERE id = $id";
        $pdo->exec($sql);

        return jsonResponse($response, [
            'message' => 'Admin delete executed without admin role verification.',
            'debug_sql' => $sql
        ]);
    } catch (Throwable $e) {
        return exposeException($response, $e);
    }
});

// Preflight catch-all
$app->options('/{routes:.+}', function (Request $request, Response $response) {
    return $response;
});

$app->run();