<?php
// Copy this file to db-config.php (in the same folder) and fill in your details.
// db-config.php is ignored by git, so the passwords never end up in the repository.
// On one.com the database details are in the control panel under "Databases".

return [
    'db_host'     => 'localhost',
    'db_name'     => '',
    'db_user'     => '',
    'db_password' => '',

    // Password hash for the recipe admin at /admin. Leave it empty, open /admin
    // and the page will make a hash for the password you choose. Paste it in here.
    'admin_password_hash' => '',
];
