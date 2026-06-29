<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/bootstrap.php';
require_once dirname(__DIR__) . '/config/mail.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$now = time();
if (!empty($_SESSION['vimpel_contact_last']) && ($now - (int) $_SESSION['vimpel_contact_last']) < 60) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Please wait before sending another message.']);
    exit;
}

$raw = getenv('VIMPEL_POST_BODY');
if (!is_string($raw) || $raw === '') {
    $raw = file_get_contents('php://input') ?: '';
}
$data = json_decode($raw, true);
if (!is_array($data)) {
    $data = $_POST;
}

$name    = trim((string) ($data['name'] ?? ''));
$phone   = trim((string) ($data['phone'] ?? ''));
$email   = trim((string) ($data['email'] ?? ''));
$subject = trim((string) ($data['subject'] ?? ''));
$message = trim((string) ($data['message'] ?? ''));
$honeypot = trim((string) ($data['website'] ?? ''));

if ($honeypot !== '') {
    echo json_encode(['success' => true, 'message' => 'Thank you.']);
    exit;
}

$errors = [];
if ($name === '' || mb_strlen($name) > 120) {
    $errors[] = 'name';
}
if ($phone === '' || mb_strlen($phone) > 40) {
    $errors[] = 'phone';
}
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 160) {
    $errors[] = 'email';
}
if ($subject === '' || mb_strlen($subject) > 160) {
    $errors[] = 'subject';
}
if ($message === '' || mb_strlen($message) > 5000) {
    $errors[] = 'message';
}

if ($errors) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Please check the form fields and try again.',
        'fields'  => $errors,
    ]);
    exit;
}

$recipient = vimpel_contact_recipient();
$mailSubject = '[Vimpel Contact] ' . $subject;
$sentAt = gmdate('Y-m-d H:i:s') . ' UTC';
$ip = trim((string) ($_SERVER['REMOTE_ADDR'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 'unknown'));

$mailContent = vimpel_build_contact_email($name, $phone, $email, $subject, $message, $ip, $sentAt);

$sent = vimpel_send_contact_mail(
    $recipient,
    $mailSubject,
    $mailContent['text'],
    $name,
    $email,
    $mailContent['html']
);

if (!$sent && vimpel_is_preview()) {
    $storageDir = dirname(__DIR__) . '/storage';
    if (!is_dir($storageDir)) {
        @mkdir($storageDir, 0755, true);
    }
    $logFile = $storageDir . '/contact-submissions.log';
    $entry = $sentAt . " → {$recipient}\n{$mailContent['text']}\n---\n";
    if (@file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX) !== false) {
        $sent = true;
    }
}

if (!$sent) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Unable to send your message right now. Please try again or email us directly.',
    ]);
    exit;
}

$_SESSION['vimpel_contact_last'] = $now;

echo json_encode([
    'success' => true,
    'message' => 'Thank you. Your message has been sent.',
]);
