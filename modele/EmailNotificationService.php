<?php
declare(strict_types=1);

final class EmailNotificationService
{
    private const DEFAULT_RECIPIENT = 'abdelmalakgafsi@gmail.com';
    private const LOG_FILE = __DIR__ . '/../logs/email_notifications.log';
    private static ?array $smtpConfig = null;

    private static function getSmtpConfig(): array
    {
        if (self::$smtpConfig !== null) {
            return self::$smtpConfig;
        }

        $configFile = __DIR__ . '/../config/email.php';
        if (file_exists($configFile)) {
            $config = require $configFile;
            self::$smtpConfig = $config['smtp'] ?? [];
        } else {
            self::$smtpConfig = [];
        }

        return self::$smtpConfig;
    }

    public static function sendDeclarationNotification(
        string $subject,
        string $action,
        int $id,
        array $data,
        ?array $targeting = null,
        ?string $recipient = null
    ): bool {
        $recipient = self::resolveRecipient($recipient);
        if ($recipient === null) {
            self::log('FAILED', $subject, null, 'No valid recipient');
            return false;
        }

        $textBody = self::buildTextBody($action, $id, $data, $targeting);
        $htmlBody = self::buildHtmlBody($subject, $action, $id, $data, $targeting);

        $config = self::getSmtpConfig();
        $smtpHost = trim((string) ($config['host'] ?? ''));
        if ($smtpHost !== '' && self::smtpSend($recipient, $subject, $textBody, $htmlBody)) {
            self::log('SUCCESS', $subject, $recipient, 'Sent through SMTP');
            return true;
        }

        $sent = self::mailSend($recipient, $subject, $textBody, $htmlBody);
        self::log($sent ? 'SUCCESS' : 'FAILED', $subject, $recipient, $sent ? 'Sent through mail()' : 'mail() returned false');

        return $sent;
    }

    private static function resolveRecipient(?string $recipient): ?string
    {
        $candidates = [
            $recipient,
            getenv('LOSTFOUND_NOTIFICATION_EMAIL') ?: null,
            self::DEFAULT_RECIPIENT,
        ];

        foreach ($candidates as $candidate) {
            $candidate = trim((string) $candidate);
            if ($candidate !== '' && filter_var($candidate, FILTER_VALIDATE_EMAIL)) {
                return $candidate;
            }
        }

        return null;
    }

    private static function mailSend(string $recipient, string $subject, string $textBody, string $htmlBody): bool
    {
        $boundary = '=_LostFound_' . bin2hex(random_bytes(8));
        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
            'From: Lost & Found <' . self::fromAddress() . '>',
            'Reply-To: ' . self::fromAddress(),
        ];

        $message = "--$boundary\r\n"
            . "Content-Type: text/plain; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: 8bit\r\n\r\n"
            . $textBody . "\r\n\r\n"
            . "--$boundary\r\n"
            . "Content-Type: text/html; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: 8bit\r\n\r\n"
            . $htmlBody . "\r\n\r\n"
            . "--$boundary--";

        return @mail($recipient, $subject, $message, implode("\r\n", $headers));
    }

    private static function smtpSend(string $recipient, string $subject, string $textBody, string $htmlBody): bool
    {
        $config = self::getSmtpConfig();
        $host = trim((string) ($config['host'] ?? ''));
        $port = (int) ($config['port'] ?? 587);
        $username = trim((string) ($config['user'] ?? ''));
        $password = (string) ($config['pass'] ?? '');
        $encryption = strtolower(trim((string) ($config['encryption'] ?? 'tls')));

        if ($host === '' || $username === '' || $password === '') {
            return false;
        }

        $remote = ($encryption === 'ssl' ? 'ssl://' : '') . $host . ':' . $port;
        $socket = @stream_socket_client($remote, $errorNumber, $errorMessage, 15, STREAM_CLIENT_CONNECT);
        if (!$socket) {
            self::log('FAILED', 'SMTP', $recipient, 'Connection failed: ' . $errorMessage);
            return false;
        }

        stream_set_timeout($socket, 15);
        $from = self::fromAddress($username);
        $message = self::buildSmtpMessage($from, $recipient, $subject, $textBody, $htmlBody);

        $commands = [
            ['expect' => [220], 'send' => null],
            ['expect' => [250], 'send' => 'EHLO localhost'],
        ];

        foreach ($commands as $command) {
            if (!$command['send']) {
                if (!self::smtpReadCode($socket, $command['expect'])) {
                    fclose($socket);
                    return false;
                }
                continue;
            }

            if (!self::smtpWrite($socket, $command['send']) || !self::smtpReadCode($socket, $command['expect'])) {
                fclose($socket);
                return false;
            }
        }

        if ($encryption === 'tls') {
            if (!self::smtpWrite($socket, 'STARTTLS') || !self::smtpReadCode($socket, [220])) {
                fclose($socket);
                return false;
            }

            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                fclose($socket);
                return false;
            }

            if (!self::smtpWrite($socket, 'EHLO localhost') || !self::smtpReadCode($socket, [250])) {
                fclose($socket);
                return false;
            }
        }

        if (
            !self::smtpWrite($socket, 'AUTH LOGIN') || !self::smtpReadCode($socket, [334]) ||
            !self::smtpWrite($socket, base64_encode($username)) || !self::smtpReadCode($socket, [334]) ||
            !self::smtpWrite($socket, base64_encode($password)) || !self::smtpReadCode($socket, [235])
        ) {
            fclose($socket);
            return false;
        }

        if (
            !self::smtpWrite($socket, 'MAIL FROM:<' . $from . '>') || !self::smtpReadCode($socket, [250]) ||
            !self::smtpWrite($socket, 'RCPT TO:<' . $recipient . '>') || !self::smtpReadCode($socket, [250, 251]) ||
            !self::smtpWrite($socket, 'DATA') || !self::smtpReadCode($socket, [354]) ||
            !self::smtpWriteRaw($socket, $message . "\r\n.\r\n") || !self::smtpReadCode($socket, [250]) ||
            !self::smtpWrite($socket, 'QUIT')
        ) {
            fclose($socket);
            return false;
        }

        fclose($socket);
        return true;
    }

    private static function smtpReadCode($socket, array $expectedCodes): bool
    {
        $response = '';

        while (!feof($socket)) {
            $line = fgets($socket, 512);
            if ($line === false) {
                break;
            }

            $response .= $line;
            if (preg_match('/^(\d{3})([ -])/', $line, $matches) === 1 && $matches[2] === ' ') {
                $code = (int) $matches[1];
                return in_array($code, $expectedCodes, true);
            }
        }

        return false;
    }

    private static function smtpWrite($socket, string $command): bool
    {
        return self::smtpWriteRaw($socket, $command . "\r\n");
    }

    private static function smtpWriteRaw($socket, string $data): bool
    {
        return fwrite($socket, $data) !== false;
    }

    private static function buildSmtpMessage(string $from, string $recipient, string $subject, string $textBody, string $htmlBody): string
    {
        $boundary = '=_LostFound_' . bin2hex(random_bytes(8));

        $headers = [
            'From: Lost & Found <' . $from . '>',
            'To: ' . $recipient,
            'Subject: ' . self::encodeHeader($subject),
            'MIME-Version: 1.0',
            'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
        ];

        return implode("\r\n", $headers) . "\r\n\r\n"
            . "--$boundary\r\n"
            . "Content-Type: text/plain; charset=UTF-8\r\n\r\n"
            . $textBody . "\r\n\r\n"
            . "--$boundary\r\n"
            . "Content-Type: text/html; charset=UTF-8\r\n\r\n"
            . $htmlBody . "\r\n\r\n"
            . "--$boundary--";
    }

    private static function encodeHeader(string $value): string
    {
        return '=?UTF-8?B?' . base64_encode($value) . '?=';
    }

    private static function fromAddress(?string $preferred = null): string
    {
        $config = self::getSmtpConfig();
        $candidate = trim((string) ($preferred ?: $config['from'] ?? getenv('LOSTFOUND_SMTP_FROM') ?: 'no-reply@localhost'));

        return filter_var($candidate, FILTER_VALIDATE_EMAIL) ? $candidate : 'no-reply@localhost';
    }

    private static function buildTextBody(string $action, int $id, array $data, ?array $targeting): string
    {
        $titre = (string) ($data['titre'] ?? $data['title'] ?? '');
        $description = (string) ($data['description'] ?? '');
        $categorie = (string) ($data['categorie'] ?? '');
        $lieu = (string) ($data['lieu_perte'] ?? '');
        $datePerte = (string) ($data['date_perte'] ?? '');
        $statut = (string) ($data['statut'] ?? 'perdu');

        return "Une declaration a ete $action dans Lost & Found.\n\n"
            . "ID: $id\n"
            . "Titre: $titre\n"
            . "Description: $description\n"
            . "Categorie: $categorie\n"
            . "Lieu: $lieu\n"
            . "Date de perte: $datePerte\n"
            . "Statut: $statut\n\n"
            . ($targeting ? (string) ($targeting['message'] ?? '') : '');
    }

    private static function buildHtmlBody(string $subject, string $action, int $id, array $data, ?array $targeting): string
    {
        $titre = htmlspecialchars((string) ($data['titre'] ?? $data['title'] ?? ''), ENT_QUOTES, 'UTF-8');
        $description = htmlspecialchars((string) ($data['description'] ?? ''), ENT_QUOTES, 'UTF-8');
        $categorie = htmlspecialchars((string) ($data['categorie'] ?? ''), ENT_QUOTES, 'UTF-8');
        $lieu = htmlspecialchars((string) ($data['lieu_perte'] ?? ''), ENT_QUOTES, 'UTF-8');
        $datePerte = htmlspecialchars((string) ($data['date_perte'] ?? ''), ENT_QUOTES, 'UTF-8');
        $statut = htmlspecialchars((string) ($data['statut'] ?? 'perdu'), ENT_QUOTES, 'UTF-8');
        $safeSubject = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
        $safeAction = htmlspecialchars($action, ENT_QUOTES, 'UTF-8');
        $safeId = htmlspecialchars((string) $id, ENT_QUOTES, 'UTF-8');

        $targetingHtml = '';
        if ($targeting !== null && trim((string) ($targeting['message'] ?? '')) !== '') {
            $message = htmlspecialchars((string) $targeting['message'], ENT_QUOTES, 'UTF-8');
            $targetingHtml = '<div style="margin-top:18px;padding:14px 16px;border-radius:10px;background:#f3f8ff;border:1px solid #cfe1f7;">'
                . '<div style="font-size:12px;font-weight:700;color:#0F3B6E;margin-bottom:6px;">Alerte de proximite</div>'
                . '<div style="font-size:13px;color:#20354d;line-height:1.5;">' . $message . '</div>'
                . '</div>';
        }

        return '<!DOCTYPE html>'
            . '<html lang="fr">'
            . '<body style="margin:0;padding:0;background:#eef3f8;font-family:Segoe UI,Arial,sans-serif;color:#1d2b3a;">'
            . '<div style="max-width:640px;margin:24px auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #dbe5ef;">'
            . '<div style="background:linear-gradient(135deg,#1976D2,#0F3B6E);padding:20px 24px;color:#fff;">'
            . '<div style="font-size:22px;font-weight:700;">ECO RIDE</div>'
            . '<div style="font-size:12px;opacity:.9;">Notification Lost &amp; Found</div>'
            . '</div>'
            . '<div style="padding:22px 24px;">'
            . '<h1 style="margin:0 0 8px 0;font-size:22px;color:#0F3B6E;">' . $safeSubject . '</h1>'
            . '<p style="margin:0 0 16px 0;font-size:14px;color:#4b5f74;">Une declaration a ete <strong>' . $safeAction . '</strong>.</p>'
            . '<table style="width:100%;border-collapse:collapse;background:#f8fbff;border:1px solid #dbe7f3;border-radius:10px;overflow:hidden;">'
            . '<tr><td style="padding:12px 14px;border-bottom:1px solid #e3edf7;width:38%;">ID</td><td style="padding:12px 14px;border-bottom:1px solid #e3edf7;">#' . $safeId . '</td></tr>'
            . '<tr><td style="padding:12px 14px;border-bottom:1px solid #e3edf7;">Titre</td><td style="padding:12px 14px;border-bottom:1px solid #e3edf7;">' . $titre . '</td></tr>'
            . '<tr><td style="padding:12px 14px;border-bottom:1px solid #e3edf7;">Description</td><td style="padding:12px 14px;border-bottom:1px solid #e3edf7;">' . $description . '</td></tr>'
            . '<tr><td style="padding:12px 14px;border-bottom:1px solid #e3edf7;">Categorie</td><td style="padding:12px 14px;border-bottom:1px solid #e3edf7;">' . $categorie . '</td></tr>'
            . '<tr><td style="padding:12px 14px;border-bottom:1px solid #e3edf7;">Lieu</td><td style="padding:12px 14px;border-bottom:1px solid #e3edf7;">' . $lieu . '</td></tr>'
            . '<tr><td style="padding:12px 14px;border-bottom:1px solid #e3edf7;">Date de perte</td><td style="padding:12px 14px;border-bottom:1px solid #e3edf7;">' . $datePerte . '</td></tr>'
            . '<tr><td style="padding:12px 14px;">Statut</td><td style="padding:12px 14px;">' . $statut . '</td></tr>'
            . '</table>'
            . $targetingHtml
            . '</div>'
            . '</div>'
            . '</body></html>';
    }

    private static function log(string $status, string $subject, ?string $recipient, string $reason): void
    {
        $directory = dirname(self::LOG_FILE);
        if (!is_dir($directory)) {
            @mkdir($directory, 0755, true);
        }

        $line = sprintf(
            '[%s] STATUS=%s | SUBJECT="%s" | RECIPIENT="%s" | REASON="%s"%s',
            date('Y-m-d H:i:s'),
            $status,
            str_replace('"', "'", $subject),
            str_replace('"', "'", (string) $recipient),
            str_replace('"', "'", $reason),
            PHP_EOL
        );

        @file_put_contents(self::LOG_FILE, $line, FILE_APPEND);
    }
}