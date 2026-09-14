<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\LeadModel;
use App\Models\PipelineStageModel;
use Core\Env;
use Core\Mailer;
use Core\Request;
use Core\Response;

final class OrcamentoController
{
    public function store(Request $request): void
    {
        $data = $request->all();

        $name     = trim((string) ($data['name'] ?? $data['nome'] ?? ''));
        $phone    = trim((string) ($data['phone'] ?? $data['telefone'] ?? ''));
        $email    = trim((string) ($data['email'] ?? ''));
        $company  = trim((string) ($data['company'] ?? $data['empresa'] ?? ''));
        $type     = trim((string) ($data['event_type'] ?? $data['tipo'] ?? ''));
        $date     = trim((string) ($data['event_date'] ?? $data['data'] ?? ''));
        $location = trim((string) ($data['location'] ?? $data['local'] ?? ''));
        $audience = trim((string) ($data['audience'] ?? $data['publico'] ?? ''));
        $details  = trim((string) ($data['details'] ?? $data['detalhes'] ?? ''));
        
        $services = $data['services'] ?? $data['servicos'] ?? [];
        if (is_string($services)) {
            $services = array_filter(array_map('trim', explode(',', $services)));
        }
        $servicesList = !empty($services) ? implode(', ', (array) $services) : 'Nenhum informado';

        if ($name === '' || $phone === '') {
            Response::json(['error' => 'Nome e telefone são obrigatórios.'], 422);
        }

        // 1. Buscar a primeira etapa do funil (ex: "Novo lead")
        $stageModel = new PipelineStageModel();
        $stages = $stageModel->allOrdered();
        $firstStageId = !empty($stages) ? (int) $stages[0]['id'] : 1;

        // Formatação da data (dd/mm/aaaa)
        $formattedDate = $date;
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $m)) {
            $formattedDate = "{$m[3]}/{$m[2]}/{$m[1]}";
        }

        // 2. Montar notas detalhadas para o CRM
        $notes = "📋 SOLICITAÇÃO VIA SITE (TRUE EVENTOS)\n";
        $notes .= "• Tipo de Evento: " . ($type ?: 'Não informado') . "\n";
        $notes .= "• Data Prevista: " . ($formattedDate ?: 'Não informada') . "\n";
        $notes .= "• Local: " . ($location ?: 'Não informado') . "\n";
        $notes .= "• Público Estimado: " . ($audience ?: 'Não informado') . "\n";
        $notes .= "• Soluções: {$servicesList}\n";
        if ($details !== '') {
            $notes .= "\n📝 Detalhes adicionais:\n{$details}\n";
        }

        // 3. Gravar no banco de dados (LeadModel)
        $leadModel = new LeadModel();
        $leadId = $leadModel->insert([
            'stage_id'   => $firstStageId,
            'name'       => $name,
            'company'    => $company ?: null,
            'email'      => $email ?: null,
            'phone'      => $phone ?: null,
            'notes'      => $notes,
            'sort_order' => $leadModel->nextSortOrder($firstStageId),
        ]);

        $quotePayload = [
            'lead_id'    => $leadId,
            'name'       => $name,
            'phone'      => $phone,
            'email'      => $email,
            'company'    => $company,
            'event_type' => $type ?: 'Evento',
            'event_date' => $formattedDate ?: 'A definir',
            'location'   => $location,
            'audience'   => $audience,
            'services'   => $servicesList,
            'details'    => $details,
        ];

        // 4. Disparo Imediato de E-mail
        $emailAdminSent  = false;
        $emailClientSent = false;
        try {
            $mailer = new Mailer();
            $emailAdminSent = $mailer->sendQuoteAdminNotification($quotePayload);

            if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $emailClientSent = $mailer->sendQuoteClientConfirmation($email, $name, $quotePayload);
            }
        } catch (\Throwable $e) {
            error_log('Erro ao disparar e-mail de orçamento: ' . $e->getMessage());
        }

        // 5. Preparação e Disparo do WhatsApp
        $whatsappNumber = preg_replace('/\D/', '', (string) Env::get('WHATSAPP_NUMBER', '5561998448371'));
        if ($whatsappNumber === '') {
            $whatsappNumber = '5561998448371';
        }

        $whatsappMessage = implode("\n", [
            'Olá, TRUE Eventos! Gostaria de solicitar um orçamento pelo site.',
            '',
            "👤 RESPONSÁVEL: {$name}",
            "📱 WHATSAPP: {$phone}",
            "✉️ E-MAIL: " . ($email ?: 'Não informado'),
            "🏢 EMPRESA: " . ($company ?: 'Não informada'),
            '',
            "🎉 TIPO DE EVENTO: " . ($type ?: 'Não informado'),
            "📅 DATA PREVISTA: " . ($formattedDate ?: 'Não informada'),
            "📍 LOCAL: " . ($location ?: 'Não informado'),
            "👥 PÚBLICO ESTIMADO: " . ($audience ?: 'Não informado'),
            "⚡ SOLUÇÕES: {$servicesList}",
            '',
            "📝 DETALHES: " . ($details ?: 'Sem observações adicionais'),
        ]);

        $whatsappUrl = "https://wa.me/{$whatsappNumber}?text=" . rawurlencode($whatsappMessage);

        // Registro em log de WhatsApp
        $waLogPath = dirname(__DIR__, 2) . '/storage/logs/whatsapp.log';
        $waLogEntry = sprintf(
            "[%s] Lead #%d | Para: +%s | Cliente: %s (%s) | Evento: %s\n",
            date('Y-m-d H:i:s'),
            $leadId,
            $whatsappNumber,
            $name,
            $phone,
            $type ?: 'Evento'
        );
        @file_put_contents($waLogPath, $waLogEntry, FILE_APPEND | LOCK_EX);

        // Disparo automático via Webhook de WhatsApp (caso configurado em .env)
        $whatsappApiUrl = (string) Env::get('WHATSAPP_API_URL', '');
        $whatsappApiDispatched = false;
        if (!empty($whatsappApiUrl)) {
            try {
                $ch = curl_init($whatsappApiUrl);
                $apiPayload = json_encode([
                    'number'  => $whatsappNumber,
                    'message' => $whatsappMessage,
                    'lead_id' => $leadId,
                    'client'  => $quotePayload,
                ]);
                curl_setopt_array($ch, [
                    CURLOPT_POST           => true,
                    CURLOPT_POSTFIELDS     => $apiPayload,
                    CURLOPT_HTTPHEADER     => [
                        'Content-Type: application/json',
                        'Authorization: Bearer ' . Env::get('WHATSAPP_API_TOKEN', ''),
                    ],
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT        => 3,
                ]);
                $res = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                $whatsappApiDispatched = ($httpCode >= 200 && $httpCode < 300);
            } catch (\Throwable $e) {
                error_log('Erro ao disparar API WhatsApp: ' . $e->getMessage());
            }
        }

        Response::json([
            'success'                 => true,
            'message'                 => 'Orçamento registrado e enviado com sucesso por WhatsApp e E-mail!',
            'lead_id'                 => $leadId,
            'email_admin_sent'        => $emailAdminSent,
            'email_client_sent'       => $emailClientSent,
            'whatsapp_url'            => $whatsappUrl,
            'whatsapp_message'        => $whatsappMessage,
            'whatsapp_api_dispatched' => $whatsappApiDispatched,
        ], 201);
    }
}
