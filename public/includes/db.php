<?php
// MySQL connection. The login details live in db-config.php, which is not in git:
// copy db-config.example.php to db-config.php and fill it in.

// The settings from db-config.php, or null if the file is missing.
function db_config(): ?array
{
    static $config = false;
    if ($config === false) {
        $file   = __DIR__ . '/db-config.php';
        $config = is_file($file) ? require $file : null;
    }
    return is_array($config) ? $config : null;
}

// The shared database connection, or null if it isn't set up or can't connect.
// Pages check for null and show a friendly message instead of an error.
function db(): ?PDO
{
    static $pdo = false;
    if ($pdo !== false) {
        return $pdo;
    }

    $pdo    = null;
    $config = db_config();
    if (empty($config['db_name'])) {
        return null;
    }

    try {
        $pdo = new PDO(
            'mysql:host=' . $config['db_host'] . ';dbname=' . $config['db_name'] . ';charset=utf8mb4',
            $config['db_user'],
            $config['db_password'],
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );
    } catch (PDOException $exception) {
        error_log('Database connection failed: ' . $exception->getMessage());
    }
    return $pdo;
}
