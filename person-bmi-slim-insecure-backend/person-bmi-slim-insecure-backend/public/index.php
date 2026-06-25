<?php
// ==========================================================
// SECJ3483 Web Technology
// Person BMI Secure Backend
// Commit 2: Backend validation, password hashing, prepared statements
// ==========================================================

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/db.php';

$app = AppFactory::create();
$app->addBodyParsingMiddleware();

// FIX 12: Disable detailed error display to users
$app->addErrorMiddleware(false, true, true);

// ----------------------------------------------------------
// CORS
// ----------------------------------------------------------
$app->add(function (Request $request, $handler) {
    if ($request->getMethod() === 'OPTIONS') {
        $response = new \Slim\Psr7\Response();
    } else {
        $response = $handler->handle($request);
    }

    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
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
    return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
}

function getRequestData(Request $request): array
{
    $data = $request->getParsedBody();
    if (is_array($data) && !empty($data)) return $data;
    $rawBody = (string) $request->getBody();
    if ($rawBody !== '') {
        $jsonData = json_decode($rawBody, true);
        if (is_array($jsonData)) return $jsonData;
    }
    return is_array($data) ? $data : [];
}

// FIX 2: Backend BMI calculation - frontend should not decide BMI value
function calculateBmi(float $height, float $weight): float
{
    return round($weight / ($height * $height), 2);
}

function getBmiCategory(float $bmi): string
{
    if ($bmi < 18.5) return 'Underweight';
    if ($bmi < 25.0) return 'Normal';
    if ($bmi < 30.0) return 'Overweight';
    return 'Obese';
}

// FIX 12: Safe error handler - never expose internal errors to users
function safeError(Response $response, Throwable $e, int $status = 500): Response
{
    error_log($e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    return jsonResponse($response, ['error' => 'Unable to process request'], $status);
}

// FIX 1: Backend validation for BMI input fields
function validateBmiData(array $data): ?string
{
    if (!isset($data['name']) || trim($data['name']) === '') {
        return 'Name is required';
    }
    if (!isset($data['age']) || (int)$data['age'] < 1 || (int)$data['age'] > 120) {
        return 'Age must be between 1 and 120';
    }
    if (!isset($data['height']) || (float)$data['height'] < 0.5 || (float)$data['height'] > 2.5) {
        return 'Height must be between 0.5 and 2.5 meters';
    }
    if (!isset($data['weight']) || (float)$data['weight'] < 2 || (float)$data['weight'] > 300) {
        return 'Weight must be between 2 and 300 kg';
    }
    return null;
}

// Note: Still using fake token - will be replaced with real JWT in Commit 3
function createFakeToken(array $user): string
{
    $payload = [
        'user_id' => $user['id'],
        'role'    => $user['role'],
        'email'   => $user['email']
    ];
    return base64_encode(json_encode($payload));
}

function getFakeUserFromToken(Request $request): ?array
{
    $auth = $request->getHeaderLine('Authorization');
    if (!$auth || !preg_match('/Bearer\s+(\S+)/', $auth, $matches)) return null;
    $json = base64_decode($matches[1], true);
    if (!$json) return null;
    $payload = json_decode($json, true);
    return is_array($payload) ? $payload : null;
}

// ----------------------------------------------------------
// Root routes
// ----------------------------------------------------------
$app->get('/', function (Request $request, Response $response) {
    return jsonResponse($response, [
        'message' => 'Person BMI Secure Backend',
        'commit'  => '2 - validation, password hashing, prepared statements'
    ]);
});

$app->get('/api/health', function (Request $request, Response $response) {
    return jsonResponse($response, ['status' => 'ok', 'api' => 'person-bmi-secure-backend']);
});

// ----------------------------------------------------------
// Public route: Register
// FIX 3: Password hashing with password_hash()
// FIX 4: Prepared statement - no SQL injection risk
// FIX 9: Role is always 'user' - frontend cannot register as admin/staff
// FIX 10: Response does not expose password fields
// ----------------------------------------------------------
$app->post('/api/register', function (Request $request, Response $response) {
    try {
        $pdo  = getPDO();
        $data = getRequestData($request);

        $name     = trim($data['name'] ?? '');
        $email    = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';

        if (!$name || !$email || !$password) {
            return jsonResponse($response, ['error' => 'Name, email, and password are required'], 400);
        }

        // FIX 3: Hash password - never store plain text
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // FIX 4: Prepared statement
        // FIX 9: Role forced to 'user' - not accepted from frontend
        $stmt = $pdo->prepare(
            "INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, 'user')"
        );
        $stmt->execute([$name, $email, $passwordHash]);
        $id = $pdo->lastInsertId();

        // FIX 10: Return only safe fields - no password or password_hash
        $stmt = $pdo->prepare("SELECT id, name, email, role, created_at FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        return jsonResponse($response, ['message' => 'User registered successfully', 'user' => $user], 201);
    } catch (Throwable $e) {
        return safeError($response, $e);
    }
});

// ----------------------------------------------------------
// Public route: Login
// FIX 3: password_verify() instead of plain text comparison
// FIX 4: Prepared statement - prevents SQL Injection
// FIX 10: Response does not expose password fields
// ----------------------------------------------------------
$app->post('/api/login', function (Request $request, Response $response) {
    try {
        $pdo  = getPDO();
        $data = getRequestData($request);

        $email    = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';

        if (!$email || !$password) {
            return jsonResponse($response, ['error' => 'Email and password are required'], 400);
        }

        // FIX 4: Prepared statement - email treated as data, not SQL command
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // FIX 3: Use password_verify() to check against bcrypt hash
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return jsonResponse($response, ['error' => 'Invalid email or password'], 401);
        }

        $token = createFakeToken($user);

        // FIX 10: Return only safe user fields
        return jsonResponse($response, [
            'message' => 'Login successful',
            'token'   => $token,
            'user'    => [
                'id'    => $user['id'],
                'name'  => $user['name'],
                'email' => $user['email'],
                'role'  => $user['role']
            ]
        ]);
    } catch (Throwable $e) {
        return safeError($response, $e);
    }
});

// ----------------------------------------------------------
// Protected route: Profile
// FIX 4: Prepared statement
// FIX 10: Returns only safe fields
// ----------------------------------------------------------
$app->get('/api/profile', function (Request $request, Response $response) {
    try {
        $pdo      = getPDO();
        $fakeUser = getFakeUserFromToken($request);

        if (!$fakeUser) {
            return jsonResponse($response, ['error' => 'Unauthorized'], 401);
        }

        $userId = $fakeUser['user_id'];

        // FIX 4 + FIX 10: Prepared statement, safe fields only
        $stmt = $pdo->prepare("SELECT id, name, email, role, created_at FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        return jsonResponse($response, ['user' => $user]);
    } catch (Throwable $e) {
        return safeError($response, $e);
    }
});

// ----------------------------------------------------------
// BMI routes
// ----------------------------------------------------------

// GET /api/persons
// FIX 4: Prepared statement
// FIX 10: Safe fields only
$app->get('/api/persons', function (Request $request, Response $response) {
    try {
        $pdo      = getPDO();
        $fakeUser = getFakeUserFromToken($request);

        if (!$fakeUser) {
            return jsonResponse($response, ['error' => 'Unauthorized'], 401);
        }

        $userId = $fakeUser['user_id'];

        $stmt = $pdo->prepare(
            "SELECT id, user_id, name, age, height, weight, bmi, category, notes, created_at
             FROM persons WHERE user_id = ? ORDER BY id DESC"
        );
        $stmt->execute([$userId]);
        $persons = $stmt->fetchAll();

        return jsonResponse($response, ['persons' => $persons]);
    } catch (Throwable $e) {
        return safeError($response, $e);
    }
});

// POST /api/persons
// FIX 1: Backend validation
// FIX 2: Backend BMI calculation
// FIX 4: Prepared statement
// FIX 9: user_id taken from token, not from frontend
$app->post('/api/persons', function (Request $request, Response $response) {
    try {
        $pdo      = getPDO();
        $data     = getRequestData($request);
        $fakeUser = getFakeUserFromToken($request);

        if (!$fakeUser) {
            return jsonResponse($response, ['error' => 'Unauthorized'], 401);
        }

        // FIX 1: Validate all BMI input fields
        $validationError = validateBmiData($data);
        if ($validationError) {
            return jsonResponse($response, ['error' => $validationError], 400);
        }

        $name   = trim($data['name']);
        $age    = (int) $data['age'];
        $height = (float) $data['height'];
        $weight = (float) $data['weight'];
        $notes  = trim($data['notes'] ?? '');

        // FIX 2: Calculate BMI on backend - never trust frontend values
        $bmi      = calculateBmi($height, $weight);
        $category = getBmiCategory($bmi);

        // FIX 9: user_id from verified token, not from request body
        $userId = $fakeUser['user_id'];

        // FIX 4: Prepared statement
        $stmt = $pdo->prepare(
            "INSERT INTO persons (user_id, name, age, height, weight, bmi, category, notes)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$userId, $name, $age, $height, $weight, $bmi, $category, $notes]);
        $id = $pdo->lastInsertId();

        $stmt = $pdo->prepare(
            "SELECT id, user_id, name, age, height, weight, bmi, category, notes, created_at
             FROM persons WHERE id = ?"
        );
        $stmt->execute([$id]);
        $person = $stmt->fetch();

        return jsonResponse($response, ['message' => 'BMI record created', 'person' => $person], 201);
    } catch (Throwable $e) {
        return safeError($response, $e);
    }
});

// GET /api/persons/{id}
// FIX 4: Prepared statement
$app->get('/api/persons/{id}', function (Request $request, Response $response, array $args) {
    try {
        $pdo      = getPDO();
        $id       = (int) $args['id'];
        $fakeUser = getFakeUserFromToken($request);

        if (!$fakeUser) {
            return jsonResponse($response, ['error' => 'Unauthorized'], 401);
        }

        $stmt = $pdo->prepare(
            "SELECT id, user_id, name, age, height, weight, bmi, category, notes, created_at
             FROM persons WHERE id = ?"
        );
        $stmt->execute([$id]);
        $person = $stmt->fetch();

        if (!$person) {
            return jsonResponse($response, ['error' => 'Record not found'], 404);
        }

        return jsonResponse($response, ['person' => $person]);
    } catch (Throwable $e) {
        return safeError($response, $e);
    }
});

// PUT /api/persons/{id}
// FIX 1: Backend validation
// FIX 2: Backend BMI recalculation
// FIX 4: Prepared statement
// FIX 9: Only allowed fields updated - user_id, role, bmi, category blocked
$app->put('/api/persons/{id}', function (Request $request, Response $response, array $args) {
    try {
        $pdo      = getPDO();
        $id       = (int) $args['id'];
        $data     = getRequestData($request);
        $fakeUser = getFakeUserFromToken($request);

        if (!$fakeUser) {
            return jsonResponse($response, ['error' => 'Unauthorized'], 401);
        }

        // FIX 1: Validate input
        $validationError = validateBmiData($data);
        if ($validationError) {
            return jsonResponse($response, ['error' => $validationError], 400);
        }

        // FIX 9: Only these fields allowed - user_id, bmi, category, role are ignored
        $name   = trim($data['name']);
        $age    = (int) $data['age'];
        $height = (float) $data['height'];
        $weight = (float) $data['weight'];
        $notes  = trim($data['notes'] ?? '');

        // FIX 2: Always recalculate BMI at backend
        $bmi      = calculateBmi($height, $weight);
        $category = getBmiCategory($bmi);

        // FIX 4: Prepared statement
        $stmt = $pdo->prepare(
            "UPDATE persons SET name = ?, age = ?, height = ?, weight = ?, bmi = ?, category = ?, notes = ?
             WHERE id = ?"
        );
        $stmt->execute([$name, $age, $height, $weight, $bmi, $category, $notes, $id]);

        $stmt = $pdo->prepare(
            "SELECT id, user_id, name, age, height, weight, bmi, category, notes, created_at
             FROM persons WHERE id = ?"
        );
        $stmt->execute([$id]);
        $person = $stmt->fetch();

        return jsonResponse($response, ['message' => 'BMI record updated', 'person' => $person]);
    } catch (Throwable $e) {
        return safeError($response, $e);
    }
});

// DELETE /api/persons/{id}
// FIX 4: Prepared statement
$app->delete('/api/persons/{id}', function (Request $request, Response $response, array $args) {
    try {
        $pdo      = getPDO();
        $id       = (int) $args['id'];
        $fakeUser = getFakeUserFromToken($request);

        if (!$fakeUser) {
            return jsonResponse($response, ['error' => 'Unauthorized'], 401);
        }

        $stmt = $pdo->prepare("DELETE FROM persons WHERE id = ?");
        $stmt->execute([$id]);

        return jsonResponse($response, ['message' => 'BMI record deleted']);
    } catch (Throwable $e) {
        return safeError($response, $e);
    }
});

// ----------------------------------------------------------
// Staff routes
// FIX 4: Prepared statements
// FIX 10: Safe fields only (no password exposed)
// Note: Role check added in Commit 3
// ----------------------------------------------------------
$app->get('/api/staff/persons', function (Request $request, Response $response) {
    try {
        $pdo      = getPDO();
        $fakeUser = getFakeUserFromToken($request);

        if (!$fakeUser) {
            return jsonResponse($response, ['error' => 'Unauthorized'], 401);
        }

        $stmt = $pdo->prepare(
            "SELECT persons.id, persons.user_id, persons.name, persons.age,
                    persons.height, persons.weight, persons.bmi, persons.category,
                    persons.notes, persons.created_at, users.email AS owner_email
             FROM persons
             JOIN users ON persons.user_id = users.id
             ORDER BY persons.id DESC"
        );
        $stmt->execute();
        $persons = $stmt->fetchAll();

        return jsonResponse($response, ['persons' => $persons]);
    } catch (Throwable $e) {
        return safeError($response, $e);
    }
});

$app->get('/api/staff/persons/{id}', function (Request $request, Response $response, array $args) {
    try {
        $pdo      = getPDO();
        $id       = (int) $args['id'];
        $fakeUser = getFakeUserFromToken($request);

        if (!$fakeUser) {
            return jsonResponse($response, ['error' => 'Unauthorized'], 401);
        }

        $stmt = $pdo->prepare(
            "SELECT persons.id, persons.user_id, persons.name, persons.age,
                    persons.height, persons.weight, persons.bmi, persons.category,
                    persons.notes, persons.created_at, users.email AS owner_email
             FROM persons
             JOIN users ON persons.user_id = users.id
             WHERE persons.id = ?"
        );
        $stmt->execute([$id]);
        $person = $stmt->fetch();

        if (!$person) {
            return jsonResponse($response, ['error' => 'Record not found'], 404);
        }

        return jsonResponse($response, ['person' => $person]);
    } catch (Throwable $e) {
        return safeError($response, $e);
    }
});

// ----------------------------------------------------------
// Admin routes
// FIX 4: Prepared statements
// FIX 10: No password fields returned
// Note: Role check added in Commit 3
// ----------------------------------------------------------
$app->get('/api/admin/users', function (Request $request, Response $response) {
    try {
        $pdo      = getPDO();
        $fakeUser = getFakeUserFromToken($request);

        if (!$fakeUser) {
            return jsonResponse($response, ['error' => 'Unauthorized'], 401);
        }

        // FIX 10: Select only safe fields - no password or password_hash
        $stmt = $pdo->prepare("SELECT id, name, email, role, created_at FROM users ORDER BY id ASC");
        $stmt->execute();
        $users = $stmt->fetchAll();

        return jsonResponse($response, ['users' => $users]);
    } catch (Throwable $e) {
        return safeError($response, $e);
    }
});

$app->put('/api/admin/users/{id}/role', function (Request $request, Response $response, array $args) {
    try {
        $pdo      = getPDO();
        $id       = (int) $args['id'];
        $data     = getRequestData($request);
        $fakeUser = getFakeUserFromToken($request);

        if (!$fakeUser) {
            return jsonResponse($response, ['error' => 'Unauthorized'], 401);
        }

        $role = $data['role'] ?? 'user';

        if (!in_array($role, ['user', 'staff', 'admin'])) {
            return jsonResponse($response, ['error' => 'Invalid role value'], 400);
        }

        // FIX 4: Prepared statement
        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$role, $id]);

        $stmt = $pdo->prepare("SELECT id, name, email, role, created_at FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        return jsonResponse($response, ['message' => 'User role updated', 'user' => $user]);
    } catch (Throwable $e) {
        return safeError($response, $e);
    }
});

$app->delete('/api/admin/persons/{id}', function (Request $request, Response $response, array $args) {
    try {
        $pdo      = getPDO();
        $id       = (int) $args['id'];
        $fakeUser = getFakeUserFromToken($request);

        if (!$fakeUser) {
            return jsonResponse($response, ['error' => 'Unauthorized'], 401);
        }

        // FIX 4: Prepared statement
        $stmt = $pdo->prepare("DELETE FROM persons WHERE id = ?");
        $stmt->execute([$id]);

        return jsonResponse($response, ['message' => 'BMI record deleted']);
    } catch (Throwable $e) {
        return safeError($response, $e);
    }
});

// ----------------------------------------------------------
// Migration endpoint - run ONCE to hash existing plain text passwords
// Visit: http://localhost:8080/api/migrate-passwords
// Remove this route after running
// ----------------------------------------------------------
$app->get('/api/migrate-passwords', function (Request $request, Response $response) {
    try {
        $pdo   = getPDO();
        $stmt  = $pdo->query("SELECT id, password, password_hash FROM users");
        $users = $stmt->fetchAll();
        $count = 0;

        foreach ($users as $user) {
            // Only migrate if password_hash is not already a bcrypt hash
            if (substr($user['password_hash'] ?? '', 0, 4) !== '$2y$') {
                $hash   = password_hash($user['password'], PASSWORD_DEFAULT);
                $update = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                $update->execute([$hash, $user['id']]);
                $count++;
            }
        }

        return jsonResponse($response, [
            'message' => "Successfully migrated $count user passwords to bcrypt hash"
        ]);
    } catch (Throwable $e) {
        return safeError($response, $e);
    }
});

// Preflight catch-all
$app->options('/{routes:.+}', function (Request $request, Response $response) {
    return $response;
});

$app->run();
