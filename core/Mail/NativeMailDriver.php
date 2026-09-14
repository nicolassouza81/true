<?php

declare(strict_types=1);

namespace Core\Mail;

/**
 * Driver usando mail() nativo do PHP.
 * Funciona em shared hosting sem configuração extra,
 * mas pode cair em spam em provedores rigorosos.
 * Use apenas em desenvolvimento ou como fallback.
 */
final class NativeMailDriver implements MailDriverInterface
{
    public function send(
        string $to,
        string $from,
        string $fromName,
        string $subject,
        string $html
    ): bool {
        $fromHeader = $fromName
            ? '=?UTF-8?B?' . base64_encode($fromName) . '?= <' . $from . '>'
            : $from;

        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$fromHeader}\r\n";
        $headers .= "Reply-To: {$from}\r\n";
        $headers .= "X-Mailer: PHP/" . PHP_VERSION . "\r\n";

        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

        // Log para auditoria e conferência em desenvolvimento/produção
        $logPath = dirname(__DIR__, 2) . '/storage/logs/mail.log';
        $logEntry = sprintf(
            "[%s] Para: %s | Assunto: %s | De: %s <%s>\n---\n",
            date('Y-m-d H:i:s'),
            $to,
            $subject,
            $fromName,
            $from
        );
        @file_put_contents($logPath, $logEntry, FILE_APPEND | LOCK_EX);

        // Em ambientes locais ou sem sendmail configurado no php.ini, evita lançar notice/warning não capturado
        $sent = @mail($to, $encodedSubject, $html, $headers);

        return $sent !== false;
    }
}
