<?php

declare(strict_types=1);

/**
 * Send mail via PHP mail() to the configured inbox (info@vimpelbeer.am).
 * SMTP fallback is disabled; see commented code below if needed later.
 */
function vimpel_mail_from_address(): string
{
    $from = vimpel_contact_config('CONTACT_MAIL_FROM');
    if ($from !== '' && filter_var($from, FILTER_VALIDATE_EMAIL)) {
        return $from;
    }

    if (defined('SITE_EMAIL') && filter_var(SITE_EMAIL, FILTER_VALIDATE_EMAIL)) {
        return SITE_EMAIL;
    }

    $fromDomain = parse_url(SITE_URL, PHP_URL_HOST) ?: 'vimpelbeer.am';
    return 'noreply@' . $fromDomain;
}

/** Primary sender — PHP mail(). */
function vimpel_send_php_mail(
    string $to,
    string $subject,
    string $bodyText,
    string $replyToName,
    string $replyToEmail,
    ?string $bodyHtml = null
): bool {
    $from = vimpel_mail_from_address();
    $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $replyTo = $replyToName . ' <' . $replyToEmail . '>';
    $bodyText = str_replace(["\r\n", "\r", "\n"], "\r\n", $bodyText);

    if ($bodyHtml !== null && $bodyHtml !== '') {
        $boundary = 'vimpel_' . bin2hex(random_bytes(8));
        $headers = [
            'MIME-Version: 1.0',
            'From: Vimpel Website <' . $from . '>',
            'Reply-To: ' . $replyTo,
            'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
            'X-Mailer: PHP/' . PHP_VERSION,
        ];
        $body = implode("\r\n", [
            '--' . $boundary,
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
            '',
            $bodyText,
            '',
            '--' . $boundary,
            'Content-Type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
            '',
            $bodyHtml,
            '',
            '--' . $boundary . '--',
        ]);
    } else {
        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'From: Vimpel Website <' . $from . '>',
            'Reply-To: ' . $replyTo,
            'X-Mailer: PHP/' . PHP_VERSION,
        ];
        $body = $bodyText;
    }

    return @mail($to, $encodedSubject, $body, implode("\r\n", $headers));
}

/*
function vimpel_smtp_config(): ?array
{
    $host = vimpel_contact_config('CONTACT_SMTP_HOST');
    $user = vimpel_contact_config('CONTACT_SMTP_USER');
    $pass = vimpel_contact_config('CONTACT_SMTP_PASSWORD');
    if ($host === '' || $user === '' || $pass === '') {
        return null;
    }

    $port = (int) (vimpel_contact_config('CONTACT_SMTP_PORT') ?: 465);
    if ($port <= 0) {
        $port = 465;
    }

    $from = vimpel_contact_config('CONTACT_SMTP_FROM');
    if ($from === '' || !filter_var($from, FILTER_VALIDATE_EMAIL)) {
        $from = $user;
    }

    return [
        'host' => $host,
        'port' => $port,
        'user' => $user,
        'pass' => $pass,
        'from' => $from,
    ];
}
*/

function vimpel_mail_escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Build plain-text + HTML bodies for contact form notifications. */
function vimpel_build_contact_email(
    string $name,
    string $phone,
    string $email,
    string $subject,
    string $message,
    string $ip,
    string $sentAt
): array {
    $text = implode("\r\n", [
        'New message from the Vimpel website contact form',
        '',
        'Name:    ' . $name,
        'Phone:   ' . $phone,
        'Email:   ' . $email,
        'Subject: ' . $subject,
        '',
        'Message:',
        $message,
        '',
        '---',
        'Sent: ' . $sentAt,
        'IP:   ' . $ip,
    ]);

    $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body style="margin:0;padding:0;background:#f4f1ec;font-family:Georgia,serif;color:#2c1810;">'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f1ec;padding:24px 12px;">'
        . '<tr><td align="center">'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border:1px solid #e2d8cf;border-radius:12px;overflow:hidden;">'
        . '<tr><td style="background:#2c1810;padding:20px 24px;">'
        . '<p style="margin:0;font-size:18px;font-weight:bold;color:#ffffff;letter-spacing:0.04em;">VIMPEL</p>'
        . '<p style="margin:6px 0 0;font-size:13px;color:#d4c4b0;">New contact form message</p>'
        . '</td></tr>'
        . '<tr><td style="padding:24px;">'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:15px;line-height:1.5;">'
        . '<tr><td style="padding:8px 0;width:110px;color:#7a6558;font-weight:bold;vertical-align:top;">Name</td>'
        . '<td style="padding:8px 0;color:#2c1810;">' . vimpel_mail_escape($name) . '</td></tr>'
        . '<tr><td style="padding:8px 0;color:#7a6558;font-weight:bold;vertical-align:top;">Phone</td>'
        . '<td style="padding:8px 0;color:#2c1810;">' . vimpel_mail_escape($phone) . '</td></tr>'
        . '<tr><td style="padding:8px 0;color:#7a6558;font-weight:bold;vertical-align:top;">Email</td>'
        . '<td style="padding:8px 0;color:#2c1810;"><a href="mailto:' . vimpel_mail_escape($email) . '" style="color:#8b1a1a;text-decoration:none;">'
        . vimpel_mail_escape($email) . '</a></td></tr>'
        . '<tr><td style="padding:8px 0;color:#7a6558;font-weight:bold;vertical-align:top;">Subject</td>'
        . '<td style="padding:8px 0;color:#2c1810;">' . vimpel_mail_escape($subject) . '</td></tr>'
        . '</table>'
        . '<p style="margin:20px 0 8px;font-size:13px;font-weight:bold;color:#7a6558;letter-spacing:0.06em;text-transform:uppercase;">Message</p>'
        . '<div style="background:#faf7f4;border:1px solid #e8ddd3;border-radius:8px;padding:16px;font-size:15px;line-height:1.6;color:#2c1810;white-space:pre-wrap;">'
        . vimpel_mail_escape($message)
        . '</div>'
        . '</td></tr>'
        . '<tr><td style="padding:16px 24px 20px;border-top:1px solid #eee4da;font-size:12px;color:#9a8578;">'
        . 'Sent: ' . vimpel_mail_escape($sentAt) . '<br>IP: ' . vimpel_mail_escape($ip)
        . '</td></tr>'
        . '</table></td></tr></table></body></html>';

    return ['text' => $text, 'html' => $html];
}

/*
function vimpel_smtp_expect(string $response, array $codes): bool
{
    $code = (int) substr($response, 0, 3);
    return in_array($code, $codes, true);
}

function vimpel_send_smtp_mail(
    array $smtp,
    string $to,
    string $subject,
    string $bodyText,
    string $replyToName,
    string $replyToEmail,
    ?string $bodyHtml = null
): bool {
    $errno = 0;
    $errstr = '';
    $useSsl = $smtp['port'] === 465;
    $transport = $useSsl
        ? "ssl://{$smtp['host']}:{$smtp['port']}"
        : "tcp://{$smtp['host']}:{$smtp['port']}";

    $context = null;
    if ($useSsl) {
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);
    }

    $socket = @stream_socket_client(
        $transport,
        $errno,
        $errstr,
        25,
        STREAM_CLIENT_CONNECT,
        $context
    );
    if (!$socket) {
        return false;
    }

    stream_set_timeout($socket, 25);

    $read = function () use ($socket): string {
        $data = '';
        while (!feof($socket)) {
            $line = fgets($socket, 515);
            if ($line === false) {
                break;
            }
            $data .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        return $data;
    };

    $expect = function (array $codes) use ($read): bool {
        return vimpel_smtp_expect($read(), $codes);
    };

    $write = function (string $cmd) use ($socket): void {
        fwrite($socket, $cmd . "\r\n");
    };

    if (!$expect([220])) {
        fclose($socket);
        return false;
    }

    $write('EHLO vimpelbeer.am');
    $ehlo = $read();
    if (!vimpel_smtp_expect($ehlo, [250])) {
        fclose($socket);
        return false;
    }

    if (!$useSsl && stripos($ehlo, 'STARTTLS') !== false) {
        $write('STARTTLS');
        if (!$expect([220])) {
            fclose($socket);
            return false;
        }
        $crypto = STREAM_CRYPTO_METHOD_TLS_CLIENT;
        if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
            $crypto |= STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
        }
        if (!stream_socket_enable_crypto($socket, true, $crypto)) {
            fclose($socket);
            return false;
        }
        $write('EHLO vimpelbeer.am');
        if (!$expect([250])) {
            fclose($socket);
            return false;
        }
    }

    $write('AUTH LOGIN');
    if (!$expect([334])) {
        fclose($socket);
        return false;
    }
    $write(base64_encode($smtp['user']));
    if (!$expect([334])) {
        fclose($socket);
        return false;
    }
    $write(base64_encode($smtp['pass']));
    if (!$expect([235])) {
        fclose($socket);
        return false;
    }

    $write('MAIL FROM:<' . $smtp['from'] . '>');
    if (!$expect([250])) {
        fclose($socket);
        return false;
    }

    $write('RCPT TO:<' . $to . '>');
    if (!$expect([250, 251])) {
        fclose($socket);
        return false;
    }

    $write('DATA');
    if (!$expect([354])) {
        fclose($socket);
        return false;
    }

    $replyTo = $replyToName . ' <' . $replyToEmail . '>';
    $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

    if ($bodyHtml !== null && $bodyHtml !== '') {
        $boundary = 'vimpel_' . bin2hex(random_bytes(8));
        $message = implode("\r\n", [
            'From: Vimpel Website <' . $smtp['from'] . '>',
            'To: <' . $to . '>',
            'Reply-To: ' . $replyTo,
            'MIME-Version: 1.0',
            'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
            'Subject: ' . $encodedSubject,
            '',
            '--' . $boundary,
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
            '',
            $bodyText,
            '',
            '--' . $boundary,
            'Content-Type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
            '',
            $bodyHtml,
            '',
            '--' . $boundary . '--',
        ]);
    } else {
        $bodyText = str_replace(["\r\n", "\r", "\n"], "\r\n", $bodyText);
        $message = implode("\r\n", [
            'From: Vimpel Website <' . $smtp['from'] . '>',
            'To: <' . $to . '>',
            'Reply-To: ' . $replyTo,
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
            'Subject: ' . $encodedSubject,
            '',
            $bodyText,
        ]);
    }

    fwrite($socket, $message . "\r\n.\r\n");
    if (!$expect([250])) {
        fclose($socket);
        return false;
    }

    $write('QUIT');
    fclose($socket);

    return true;
}
*/

function vimpel_send_contact_mail(
    string $to,
    string $subject,
    string $bodyText,
    string $replyToName,
    string $replyToEmail,
    ?string $bodyHtml = null
): bool {
    return vimpel_send_php_mail($to, $subject, $bodyText, $replyToName, $replyToEmail, $bodyHtml);

    /*
    if (vimpel_send_php_mail($to, $subject, $bodyText, $replyToName, $replyToEmail, $bodyHtml)) {
        return true;
    }

    $smtp = vimpel_smtp_config();
    if ($smtp !== null) {
        return vimpel_send_smtp_mail($smtp, $to, $subject, $bodyText, $replyToName, $replyToEmail, $bodyHtml);
    }

    return false;
    */
}
