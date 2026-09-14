<?php

declare(strict_types=1);

namespace App\Controllers\Billing;

use App\Models\PlanModel;
use App\Models\SubscriptionModel;
use App\Services\StripeService;
use Core\Auth;
use Core\Config;
use Core\Request;
use Core\Response;
use Core\Session;

/**
 * Fluxo de assinatura via Stripe Checkout: listagem de planos,
 * criação de Checkout Session, retorno de sucesso e Billing Portal.
 */
final class SubscriptionController
{
    public function plans(Request $request): void
    {
        $plans = (new PlanModel())->active();

        Response::view('billing/view_plans', [
            'title' => 'Planos',
            'plans' => $plans,
            'plansModel' => new PlanModel(),
        ]);
    }

    /** Inicia o checkout para um plano (cria Checkout Session e redireciona). */
    public function checkout(Request $request): void
    {
        $user = Auth::user();

        if ($user === null) {
            Response::redirect('/login');
        }

        $planId = (int) $request->param('id');
        $plan = (new PlanModel())->find($planId);

        if ($plan === null || empty($plan['stripe_price_id'])) {
            Session::flash('error', 'Plano inválido ou não configurado no Stripe.');
            Response::redirect('/plans');
        }

        $appUrl = Config::get('app.url');

        try {
            $stripe = new StripeService();

            $session = $stripe->createCheckoutSession(
                priceId: $plan['stripe_price_id'],
                customerEmail: $user['email'],
                successUrl: "{$appUrl}/billing/success?session_id={CHECKOUT_SESSION_ID}",
                cancelUrl: "{$appUrl}/plans",
                metadata: [
                    'user_id' => (string) $user['id'],
                    'plan_id' => (string) $plan['id'],
                ]
            );

            Response::redirect($session['url']);
        } catch (\RuntimeException $e) {
            Session::flash('error', 'Não foi possível iniciar o checkout: ' . $e->getMessage());
            Response::redirect('/plans');
        }
    }

    /** Página de retorno após checkout (Stripe redireciona aqui). */
    public function success(Request $request): void
    {
        $sessionId = (string) $request->query('session_id', '');

        Response::view('billing/success', [
            'title'     => 'Assinatura confirmada',
            'sessionId' => $sessionId,
        ]);
    }

    /** Redireciona o usuário para o Stripe Billing Portal. */
    public function portal(Request $request): void
    {
        $user = Auth::user();

        if ($user === null) {
            Response::redirect('/login');
        }

        $subscription = (new SubscriptionModel())->findByUser((int) $user['id']);

        if ($subscription === null || empty($subscription['stripe_customer_id'])) {
            Session::flash('error', 'Você ainda não possui uma assinatura ativa.');
            Response::redirect('/dashboard');
        }

        $appUrl = Config::get('app.url');

        try {
            $stripe = new StripeService();
            $portal = $stripe->createBillingPortalSession(
                $subscription['stripe_customer_id'],
                "{$appUrl}/dashboard"
            );

            Response::redirect($portal['url']);
        } catch (\RuntimeException $e) {
            Session::flash('error', 'Não foi possível abrir o portal de cobrança: ' . $e->getMessage());
            Response::redirect('/dashboard');
        }
    }
}
