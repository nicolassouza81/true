<?php

declare(strict_types=1);

namespace Core\Mail;

/**
 * Contrato para drivers de envio de e-mail.
 * Troque o driver em config/app.php > mail.driver sem alterar
 * nenhum código que use a classe Mailer.
 */
interface MailDriverInterface
{
    /**
     * Envia um e-mail HTML.
     *
     * @param string $to        Endereço de destino
     * @param string $from      Endereço remetente
     * @param string $fromName  Nome do remetente
     * @param string $subject   Assunto
     * @param string $html      Corpo HTML completo
     */
    public function send(
        string $to,
        string $from,
        string $fromName,
        string $subject,
        string $html
    ): bool;
}
