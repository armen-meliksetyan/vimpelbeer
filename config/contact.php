<?php

require_once __DIR__ . '/seo.php';

$contactLocal = __DIR__ . '/contact.local.php';
if (is_file($contactLocal)) {
    require $contactLocal;
}

if (!defined('CONTACT_RECIPIENT_EMAIL')) {
    define('CONTACT_RECIPIENT_EMAIL', '');
}

function vimpel_contact_recipient(): string
{
    $configured = trim(CONTACT_RECIPIENT_EMAIL);
    if ($configured !== '' && filter_var($configured, FILTER_VALIDATE_EMAIL)) {
        return $configured;
    }

    return SITE_EMAIL;
}

function vimpel_contact_endpoint(): string
{
    $base = vimpel_site_base();
    return ($base === '' ? '' : rtrim($base, '/')) . '/api/contact.php';
}

function vimpel_contact_config(string $key): string
{
    if (defined($key)) {
        $value = constant($key);
        return is_string($value) || is_numeric($value) ? trim((string) $value) : '';
    }

    $env = getenv($key);
    return is_string($env) ? trim($env) : '';
}
