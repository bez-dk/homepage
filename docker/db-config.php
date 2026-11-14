<?php
// db-config.php used inside Docker. docker-compose.yml mounts it over
// public/includes/db-config.php in the container, so a real db-config.php on
// your machine is never used or touched. The values come from .env.

return [
    'db_host'     => 'db',
    'db_name'     => getenv('DB_NAME') ?: 'homepage',
    'db_user'     => getenv('DB_USER') ?: 'homepage',
    'db_password' => getenv('DB_PASSWORD') ?: 'homepage',

    'admin_password_hash' => getenv('ADMIN_PASSWORD_HASH') ?: '',
];
