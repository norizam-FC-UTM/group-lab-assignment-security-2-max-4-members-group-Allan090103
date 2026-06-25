<?php
// ==========================================================
// INSECURE STARTER CONFIGURATION
// ==========================================================
// This file is intentionally simple for classroom use.
// Students should later discuss why secrets/configuration should
// not be hardcoded in real projects.

return [
    'db_host'    => '127.0.0.1:3307',
    'db_name'    => 'security_bmi_lab',
    'db_user'    => 'root',
    'db_pass'    => '',
    'db_charset' => 'utf8mb4',
    'jwt_secret' => 'SECJ3483_JWT_SECRET_KEY_2024_SECURE',
    'jwt_expiry' => 3600
];
