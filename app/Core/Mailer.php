<?php

namespace App\Core;

final class Mailer
{
    public static function send(string $to, string $subject, string $body, array $options = []): bool
    {
        $driver = strtolower(getenv('MAIL_MAILER') ?: 'mail');
        $fromAddress = getenv('MAIL_FROM_ADDRESS') ?: 'integracja@integracjastudencka.pl';
        $fromName = getenv('MAIL_FROM_NAME') ?: 'Integracja Studencka';
        $replyTo = $options['reply_to'] ?? $fromAddress;
        $headers = [
            'From' => self::formatAddress($fromAddress, $fromName),
            'Reply-To' => $replyTo,
            'MIME-Version' => '1.0',
            'Content-Type' => 'text/plain; charset=UTF-8',
        ];

        if ($driver === 'smtp') {
            return self::sendSmtp($to, $subject, $body, $headers);
        }

        if ($driver === 'log') {
            self::logMail($to, $subject, $body, $headers);
            return true;
        }

        $sent = mail($to, self::encodeHeader($subject), $body, self::headersToString($headers));
        if (!$sent) {
            self::logMail($to, $subject, $body, $headers);
        }
        return $sent;
    }

    public static function contactRecipient(): string
    {
        return getenv('CONTACT_TO_ADDRESS') ?: 'kontakt@integracjastudencka.pl';
    }

    private static function sendSmtp(string $to, string $subject, string $body, array $headers): bool
    {
        $host = getenv('SMTP_HOST') ?: '';
        $port = (int) (getenv('SMTP_PORT') ?: 587);
        $username = getenv('SMTP_USERNAME') ?: '';
        $password = getenv('SMTP_PASSWORD') ?: '';
        $encryption = strtolower(getenv('SMTP_ENCRYPTION') ?: 'tls');

        if ($host === '' || $username === '' || $password === '') {
            self::logMail($to, $subject, $body, $headers, 'Brak danych SMTP.');
            return false;
        }

        $target = ($encryption === 'ssl' ? 'ssl://' : '') . $host;
        $socket = @stream_socket_client($target . ':' . $port, $errno, $errstr, 20);
        if (!$socket) {
            self::logMail($to, $subject, $body, $headers, "SMTP connect error: {$errno} {$errstr}");
            return false;
        }

        stream_set_timeout($socket, 20);
        $ok = self::smtpExpect($socket, [220])
            && self::smtpCommand($socket, 'EHLO ' . self::smtpDomain(), [250]);

        if ($ok && $encryption === 'tls') {
            $ok = self::smtpCommand($socket, 'STARTTLS', [220]);
            if ($ok) {
                $ok = stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)
                    && self::smtpCommand($socket, 'EHLO ' . self::smtpDomain(), [250]);
            }
        }

        $message = self::buildMessage($to, $subject, $body, $headers);
        $fromAddress = self::extractEmail($headers['From']);
        $ok = $ok
            && self::smtpCommand($socket, 'AUTH LOGIN', [334])
            && self::smtpCommand($socket, base64_encode($username), [334])
            && self::smtpCommand($socket, base64_encode($password), [235])
            && self::smtpCommand($socket, 'MAIL FROM:<' . $fromAddress . '>', [250])
            && self::smtpCommand($socket, 'RCPT TO:<' . $to . '>', [250, 251])
            && self::smtpCommand($socket, 'DATA', [354])
            && self::smtpCommand($socket, $message . "\r\n.", [250]);

        self::smtpCommand($socket, 'QUIT', [221]);
        fclose($socket);

        if (!$ok) {
            self::logMail($to, $subject, $body, $headers, 'SMTP sending failed.');
        }
        return $ok;
    }

    private static function buildMessage(string $to, string $subject, string $body, array $headers): string
    {
        $headers['To'] = $to;
        $headers['Subject'] = self::encodeHeader($subject);
        $headers['Date'] = date('r');
        $headers['Message-ID'] = '<' . bin2hex(random_bytes(12)) . '@' . self::smtpDomain() . '>';

        return self::headersToString($headers) . "\r\n\r\n" . str_replace(["\r\n", "\r"], "\n", $body);
    }

    private static function smtpCommand($socket, string $command, array $expected): bool
    {
        fwrite($socket, $command . "\r\n");
        return self::smtpExpect($socket, $expected);
    }

    private static function smtpExpect($socket, array $expected): bool
    {
        $line = '';
        while (($buffer = fgets($socket, 515)) !== false) {
            $line = $buffer;
            if (strlen($buffer) >= 4 && $buffer[3] === ' ') {
                break;
            }
        }

        $code = (int) substr($line, 0, 3);
        return in_array($code, $expected, true);
    }

    private static function headersToString(array $headers): string
    {
        $lines = [];
        foreach ($headers as $name => $value) {
            $lines[] = $name . ': ' . $value;
        }
        return implode("\r\n", $lines);
    }

    private static function formatAddress(string $email, string $name): string
    {
        return self::encodeHeader($name) . ' <' . $email . '>';
    }

    private static function encodeHeader(string $value): string
    {
        return '=?UTF-8?B?' . base64_encode($value) . '?=';
    }

    private static function extractEmail(string $address): string
    {
        if (preg_match('/<([^>]+)>/', $address, $matches)) {
            return $matches[1];
        }
        return trim($address);
    }

    private static function smtpDomain(): string
    {
        $host = parse_url(getenv('APP_URL') ?: '', PHP_URL_HOST);
        return $host ?: 'integracjastudencka.pl';
    }

    private static function logMail(string $to, string $subject, string $body, array $headers, string $note = ''): void
    {
        $dir = __DIR__ . '/../../storage/logs';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $log = '[' . date('Y-m-d H:i:s') . "] TO: {$to} SUBJECT: {$subject}\n";
        if ($note !== '') {
            $log .= "NOTE: {$note}\n";
        }
        $log .= self::headersToString($headers) . "\n\n" . $body . "\n---\n";
        file_put_contents($dir . '/mail.log', $log, FILE_APPEND);
    }
}
