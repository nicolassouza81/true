<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\LeadModel;
use App\Models\PipelineStageModel;
use App\Models\SubscriptionModel;
use Core\Auth;
use Core\Config;
use Core\Request;
use Core\Response;

final class DashboardController
{
    public function index(Request $request): void
    {
        $user = Auth::user();
        $subscription = null;

        if (Config::feature('billing') && $user !== null) {
            $subscription = (new SubscriptionModel())->findByUser((int) $user['id']);
        }

        $leadModel = new LeadModel();
        $stageModel = new PipelineStageModel();

        $totalLeads = $leadModel->count();
        $stages = $stageModel->allOrdered();
        $recentLeads = $leadModel->all([], 'created_at', 'DESC', 6);

        // Agrupar contagem por etapa
        $stageCounts = [];
        foreach ($stages as $st) {
            $stageCounts[$st['id']] = [
                'name'  => $st['name'],
                'color' => $st['color'] ?? '#5b8def',
                'count' => $leadModel->count(['stage_id' => $st['id']]),
            ];
        }

        Response::view('dashboard/view_dashboard', [
            'title'        => 'Painel de Controle',
            'user'         => $user,
            'subscription' => $subscription,
            'totalLeads'   => $totalLeads,
            'stages'       => $stages,
            'stageCounts'  => $stageCounts,
            'recentLeads'  => $recentLeads,
        ], 'admin');
    }
}
