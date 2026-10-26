<?php
// Handles the contact form submission. Sets $errors, $sent and the form values.
require_once __DIR__ . '/config.php';

$errors  = [];
$sent    = false;
$name    = '';
$email   = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    return;
}

$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');

// Honeypot field: real visitors leave it empty, bots usually fill it in.
if (!empty($_POST['website'])) {
    $sent = true;
    return;
}

if ($name === '') {
    $errors[] = t('contact.error.name');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = t('contact.error.email');
}
if ($message === '') {
    $errors[] = t('contact.error.message');
}
if ($errors) {
    return;
}

// Strip line breaks from header values to prevent header injection.
$safeName  = str_replace(["\r", "\n"], '', $name);
$safeEmail = str_replace(["\r", "\n"], '', $email);

$subject = 'New message from ' . $site['name'] . ' contact form';
$body    = "Name: $safeName\nEmail: $safeEmail\n\n$message\n";
$headers = [
    'From'         => $site['contact_from'],
    'Reply-To'     => $safeEmail,
    'Content-Type' => 'text/plain; charset=UTF-8',
];

if (mail($site['contact_to'], $subject, $body, $headers)) {
    $sent = true;
    $name = $email = $message = '';
} else {
    $errors[] = t('contact.error.send');
}
