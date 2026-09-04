<?php
/**
 * Pwrgrow contact form handler for Bluehost (PHP mail).
 * Upload alongside index.html in public_html.
 */

declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html');
    exit;
}

// Honeypot — bots often fill hidden fields
if (!empty($_POST['website'] ?? '')) {
    header('Location: index.html?sent=1#contact-section');
    exit;
}

function field(string $key): string
{
    $value = trim((string) ($_POST[$key] ?? ''));
    return strip_tags($value);
}

$name = field('name');
$email = field('email');
$phone = field('phone');
$city = field('city');
$message = field('message');

$valid = $name !== ''
    && $email !== ''
    && filter_var($email, FILTER_VALIDATE_EMAIL)
    && $phone !== ''
    && $city !== ''
    && $message !== '';

if (!$valid) {
    header('Location: index.html?error=1#contact-section');
    exit;
}

$to = 'contact@pwrgrow.com';
$subject = 'Nuevo contacto desde pwrgrow.com — ' . $name;

$body = "Nuevo mensaje desde el formulario de contacto\n\n"
    . "Nombre: {$name}\n"
    . "Email: {$email}\n"
    . "Teléfono: {$phone}\n"
    . "Ciudad: {$city}\n\n"
    . "Mensaje:\n{$message}\n";

$safeFrom = str_replace(["\r", "\n"], '', $email);
$headers = [
    'From: Pwrgrow Web <noreply@pwrgrow.com>',
    'Reply-To: ' . $safeFrom,
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'X-Mailer: PHP/' . phpversion(),
];

$sent = @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, implode("\r\n", $headers));

if ($sent) {
    header('Location: index.html?sent=1#contact-section');
} else {
    header('Location: index.html?error=1#contact-section');
}
exit;
