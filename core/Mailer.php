<?php

declare(strict_types=1);

namespace Core;

use Core\Mail\MailDriverInterface;
use Core\Mail\MailgunDriver;
use Core\Mail\NativeMailDriver;
use RuntimeException;

/**
 * Fachada de envio de e-mail.
 *
 * O driver é selecionado via MAIL_DRIVER no .env:
 *   MAIL_DRIVER=mailgun  → usa API Mailgun (recomendado para produção)
 *   MAIL_DRIVER=native   → usa mail() nativo do PHP (fallback/dev)
 *
 * Todos os e-mails da aplicação passam por esta classe.
 * Para adicionar um novo driver: implemente MailDriverInterface e
 * registre o alias aqui em resolveDriver().
 */
final class Mailer
{
    private MailDriverInterface $driver;
    private string $from;
    private string $fromName;
    private string $appName;
    private string $appUrl;

    public function __construct(?MailDriverInterface $driver = null)
    {
        $this->driver   = $driver ?? $this->resolveDriver();
        $this->from     = (string) Env::get('MAIL_FROM', 'noreply@localhost');
        $this->fromName = (string) Env::get('MAIL_FROM_NAME', Config::get('app.name', 'App'));
        $this->appName  = (string) Config::get('app.name', 'App');
        $this->appUrl   = rtrim((string) Env::get('APP_URL', 'http://localhost'), '/');
    }

    /**
     * Envia um e-mail HTML simples.
     *
     * @param string $to      Endereço de destino
     * @param string $subject Assunto
     * @param string $body    Corpo HTML (será envolvido no layout)
     */
    public function send(string $to, string $subject, string $body): bool
    {
        $html = $this->wrapLayout($subject, $body);

        return $this->driver->send(
            to:       $to,
            from:     $this->from,
            fromName: $this->fromName,
            subject:  $subject,
            html:     $html
        );
    }

    /**
     * E-mail de recuperação de senha.
     *
     * @param string $token Token em claro (não o hash)
     */
    public function sendPasswordReset(string $to, string $name, string $token): bool
    {
        $resetUrl = $this->appUrl
            . '/reset-password?token=' . urlencode($token)
            . '&email=' . urlencode($to);

        $appName  = htmlspecialchars($this->appName);
        $userName = htmlspecialchars($name);
        $accent   = (string) Config::get('app.branding.accent_color', '#5b8def');

        $body = "
            <p>Olá, <strong>{$userName}</strong>!</p>
            <p>Recebemos uma solicitação para redefinir a senha da sua conta
               no <strong>{$appName}</strong>.</p>
            <p>Clique no botão abaixo para criar uma nova senha.
               Este link expira em <strong>1 hora</strong>.</p>
            <p style=\"text-align:center;margin:32px 0\">
                <a href=\"{$resetUrl}\"
                   style=\"background:{$accent};color:#fff;padding:12px 28px;
                           border-radius:6px;text-decoration:none;
                           font-weight:600;display:inline-block\">
                    Redefinir senha
                </a>
            </p>
            <p>Ou copie e cole este link no seu navegador:</p>
            <p style=\"word-break:break-all;color:#6c757d;font-size:13px\">{$resetUrl}</p>
            <p style=\"margin-top:32px;color:#6c757d;font-size:13px\">
                Se você não solicitou a redefinição de senha, ignore este e-mail.
                Sua senha permanece a mesma.
            </p>
        ";

        return $this->send($to, "Redefinir senha — {$this->appName}", $body);
    }

    /** E-mail de boas-vindas após registro. */
    public function sendWelcome(string $to, string $name): bool
    {
        $appName  = htmlspecialchars($this->appName);
        $userName = htmlspecialchars($name);
        $dashUrl  = $this->appUrl . '/dashboard';
        $accent   = (string) Config::get('app.branding.accent_color', '#ff4b19');

        $body = "
            <p>Olá, <strong>{$userName}</strong>!
               Seja bem-vindo(a) ao <strong>{$appName}</strong>.</p>
            <p>Sua conta foi criada com sucesso.</p>
            <p style=\"text-align:center;margin:32px 0\">
                <a href=\"{$dashUrl}\"
                   style=\"background:{$accent};color:#fff;padding:12px 28px;
                           border-radius:6px;text-decoration:none;
                           font-weight:600;display:inline-block\">
                    Acessar o painel
                </a>
            </p>
        ";

        return $this->send($to, "Bem-vindo(a) ao {$this->appName}!", $body);
    }

    /**
     * E-mail imediato de notificação para a equipe TRUE Eventos ao receber novo orçamento.
     */
    public function sendQuoteAdminNotification(array $quote): bool
    {
        $adminEmail = (string) Env::get('MAIL_ADMIN_NOTIFY', 'alquimiaeventosdf@hotmail.com');
        $appName    = htmlspecialchars($this->appName);
        $name       = htmlspecialchars($quote['name'] ?? 'Cliente');
        $phone      = htmlspecialchars($quote['phone'] ?? '');
        $email      = htmlspecialchars($quote['email'] ?? 'Não informado');
        $company    = htmlspecialchars($quote['company'] ?? 'Não informado');
        $type       = htmlspecialchars($quote['event_type'] ?? 'Evento');
        $date       = htmlspecialchars($quote['event_date'] ?? 'Não informada');
        $location   = htmlspecialchars($quote['location'] ?? 'Não informado');
        $audience   = htmlspecialchars($quote['audience'] ?? 'Não informado');
        $services   = htmlspecialchars($quote['services'] ?? 'Soluções TRUE Eventos');
        $details    = nl2br(htmlspecialchars($quote['details'] ?? ''));
        
        $cleanPhone = preg_replace('/\D/', '', $phone);
        $leadId     = (int) ($quote['lead_id'] ?? 0);
        $pipelineUrl = $this->appUrl . '/admin/pipeline' . ($leadId > 0 ? '/leads/' . $leadId . '/edit' : '');

        $body = "
            <div style=\"color:#f2f0eb;\">
                <p style=\"font-size:16px;margin-top:0;\">Uma nova solicitação de orçamento foi enviada pelo site oficial da <strong>{$appName}</strong>.</p>

                <table width=\"100%\" cellpadding=\"10\" cellspacing=\"0\" style=\"margin:24px 0;background:#141414;border:1px solid #2d2d2d;border-radius:8px;color:#f2f0eb;font-size:14px;border-collapse:collapse;\">
                    <tr><td width=\"32%\" style=\"color:#a1a1aa;border-bottom:1px solid #242424;\"><strong>Cliente / Responsável:</strong></td><td style=\"border-bottom:1px solid #242424;font-size:15px;font-weight:bold;color:#ffffff;\">{$name}</td></tr>
                    <tr><td style=\"color:#a1a1aa;border-bottom:1px solid #242424;\"><strong>WhatsApp / Telefone:</strong></td><td style=\"border-bottom:1px solid #242424;\"><a href=\"https://wa.me/{$cleanPhone}\" style=\"color:#dcff36;text-decoration:none;font-weight:bold;\">{$phone} ↗</a></td></tr>
                    <tr><td style=\"color:#a1a1aa;border-bottom:1px solid #242424;\"><strong>E-mail:</strong></td><td style=\"border-bottom:1px solid #242424;\">{$email}</td></tr>
                    <tr><td style=\"color:#a1a1aa;border-bottom:1px solid #242424;\"><strong>Empresa / Org.:</strong></td><td style=\"border-bottom:1px solid #242424;\">{$company}</td></tr>
                    <tr><td style=\"color:#a1a1aa;border-bottom:1px solid #242424;\"><strong>Tipo de Evento:</strong></td><td style=\"border-bottom:1px solid #242424;font-weight:bold;\">{$type}</td></tr>
                    <tr><td style=\"color:#a1a1aa;border-bottom:1px solid #242424;\"><strong>Data Prevista:</strong></td><td style=\"border-bottom:1px solid #242424;\">{$date}</td></tr>
                    <tr><td style=\"color:#a1a1aa;border-bottom:1px solid #242424;\"><strong>Local / Cidade:</strong></td><td style=\"border-bottom:1px solid #242424;\">{$location}</td></tr>
                    <tr><td style=\"color:#a1a1aa;border-bottom:1px solid #242424;\"><strong>Público Estimado:</strong></td><td style=\"border-bottom:1px solid #242424;\">{$audience}</td></tr>
                    <tr><td style=\"color:#a1a1aa;border-bottom:1px solid #242424;\"><strong>Soluções Solicitadas:</strong></td><td style=\"border-bottom:1px solid #242424;color:#ff4b19;font-weight:bold;\">{$services}</td></tr>
                    <tr><td style=\"color:#a1a1aa;vertical-align:top;\"><strong>Detalhes Adicionais:</strong></td><td style=\"line-height:1.6;\">" . ($details ?: '<em style=\"color:#71717a\">Nenhum detalhe adicional</em>') . "</td></tr>
                </table>

                <div style=\"margin:28px 0;text-align:center;\">
                    <a href=\"https://wa.me/{$cleanPhone}\" style=\"display:inline-block;background:#25D366;color:#ffffff;padding:12px 24px;border-radius:6px;text-decoration:none;font-weight:bold;margin-right:12px;\">
                        Chamar no WhatsApp
                    </a>
                    <a href=\"{$pipelineUrl}\" style=\"display:inline-block;background:#ff4b19;color:#ffffff;padding:12px 24px;border-radius:6px;text-decoration:none;font-weight:bold;\">
                        Ver no Funil de Vendas
                    </a>
                </div>
            </div>
        ";

        return $this->send($adminEmail, "⚡ Novo Orçamento: {$name} — {$type}", $body);
    }

    /**
     * E-mail imediato de confirmação enviado ao cliente solicitante.
     */
    public function sendQuoteClientConfirmation(string $clientEmail, string $clientName, array $quote): bool
    {
        $appName    = htmlspecialchars($this->appName);
        $name       = htmlspecialchars($clientName);
        $type       = htmlspecialchars($quote['event_type'] ?? 'Evento');
        $date       = htmlspecialchars($quote['event_date'] ?? 'Data a definir');
        $location   = htmlspecialchars($quote['location'] ?? '');
        $services   = htmlspecialchars($quote['services'] ?? 'Som, Iluminação, Painéis de LED e Estruturas');

        $body = "
            <div style=\"color:#f2f0eb;\">
                <h2 style=\"color:#ff4b19;margin-top:0;font-size:22px;\">Olá, {$name}!</h2>
                <p style=\"font-size:15px;line-height:1.7;\">
                    Recebemos com sucesso o seu pedido de orçamento para <strong>{$type}</strong> no <strong>{$appName}</strong>.
                </p>
                <p style=\"color:#a1a1aa;font-size:14px;line-height:1.6;\">
                    Nossa equipe técnica já iniciou o dimensionamento de equipamentos e soluções de engenharia para o seu evento. Em breve entraremos em contato diretamente com você pelo WhatsApp para apresentar a melhor proposta.
                </p>

                <div style=\"background:#141414;border:1px solid #2d2d2d;border-radius:8px;padding:18px;margin:24px 0;\">
                    <p style=\"margin:0 0 10px;color:#a1a1aa;font-size:12px;text-transform:uppercase;letter-spacing:1px;\"><strong>Resumo da sua solicitação</strong></p>
                    <p style=\"margin:6px 0;font-size:14px;\"><strong>Evento:</strong> {$type}</p>
                    <p style=\"margin:6px 0;font-size:14px;\"><strong>Data:</strong> {$date}</p>
                    " . ($location ? "<p style=\"margin:6px 0;font-size:14px;\"><strong>Local:</strong> {$location}</p>" : "") . "
                    <p style=\"margin:6px 0;font-size:14px;\"><strong>Soluções Solicitadas:</strong> <span style=\"color:#dcff36;font-weight:bold;\">{$services}</span></p>
                </div>

                <p style=\"color:#f2f0eb;font-size:14px;\">
                    Deseja enviar agora a planta do local ou adiantar detalhes? Fale diretamente conosco pelo canal prioritário:
                </p>
                <div style=\"margin:24px 0;text-align:center;\">
                    <a href=\"https://wa.me/5561998448371\" style=\"display:inline-block;background:#25D366;color:#ffffff;padding:12px 24px;border-radius:6px;text-decoration:none;font-weight:bold;\">
                        Conversar agora pelo WhatsApp: (61) 99844-8371
                    </a>
                </div>
            </div>
        ";

        return $this->send($clientEmail, "Recebemos sua solicitação de orçamento — {$appName}", $body);
    }

    /**
     * Envolve o corpo em layout HTML responsivo.
     * Usa a cor primária definida em config/app.php > branding.
     */
    private function wrapLayout(string $subject, string $body): string
    {
        $appName = htmlspecialchars($this->appName);
        $primary = (string) Config::get('app.branding.primary_color', '#5b8def');
        $year    = date('Y');

        return <<<HTML
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width,initial-scale=1">
            <title>{$subject}</title>
        </head>
        <body style="margin:0;padding:0;background:#f5f6fa;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;color:#1a1a2e">
            <table width="100%" cellpadding="0" cellspacing="0" style="padding:40px 16px">
                <tr>
                    <td align="center">
                        <table width="100%" style="max-width:560px;background:#ffffff;border-radius:12px;border:1px solid #e2e6ea;overflow:hidden">
                            <tr>
                                <td style="background:{$primary};padding:28px 32px">
                                    <p style="margin:0;font-size:20px;font-weight:700;color:#ffffff">{$appName}</p>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:32px;font-size:15px;line-height:1.7">
                                    {$body}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:20px 32px;border-top:1px solid #e2e6ea;background:#f5f6fa">
                                    <p style="margin:0;font-size:12px;color:#6c757d;text-align:center">
                                        &copy; {$year} {$appName}. Todos os direitos reservados.
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        HTML;
    }

    private function resolveDriver(): MailDriverInterface
    {
        $driver = strtolower((string) Env::get('MAIL_DRIVER', 'native'));

        return match ($driver) {
            'mailgun' => new MailgunDriver(),
            'native'  => new NativeMailDriver(),
            default   => throw new RuntimeException(
                "Driver de e-mail desconhecido: '{$driver}'. Use 'mailgun' ou 'native'."
            ),
        };
    }
}
