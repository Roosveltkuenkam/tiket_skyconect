<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Router;
use App\Models\Ticket;
use Illuminate\Http\Request;

class DashboardOnboardingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $routersCount = Router::where('user_id', $user->id)->count();

        $plansQuery = Plan::whereHas('router', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        });

        $plansCount = (clone $plansQuery)->count();

        $availableTicketsCount = Ticket::where('status', 'available')
            ->whereHas('plan.router', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->count();

        $firstReadyPlan = (clone $plansQuery)
            ->where('is_active', true)
            ->whereHas('tickets', function ($query) {
                $query->where('status', 'available');
            })
            ->oldest()
            ->first();

        $paymentConfigured = true;
        $saleLink = $firstReadyPlan ? route('orders.create', $firstReadyPlan) : null;

        $steps = [
            [
                'title' => 'Ajouter un routeur',
                'description' => 'Rattachez votre premier hotspot MikroTik ou Mikhmon a votre espace.',
                'done' => $routersCount > 0,
                'done_label' => 'Routeur cree',
                'action_label' => 'Ajouter un routeur',
                'action_url' => route('dashboard.routers.create'),
            ],
            [
                'title' => 'Creer un forfait',
                'description' => 'Definissez une duree, un prix et le routeur concerne.',
                'done' => $plansCount > 0,
                'done_label' => 'Forfait cree',
                'action_label' => 'Creer un forfait',
                'action_url' => route('dashboard.plans.create'),
            ],
            [
                'title' => 'Importer des tickets',
                'description' => 'Importez vos vouchers Mikhmon pour disposer d’un stock vendable.',
                'done' => $availableTicketsCount > 0,
                'done_label' => 'Tickets disponibles',
                'action_label' => 'Importer les tickets',
                'action_url' => route('dashboard.tickets.import.create'),
            ],
            [
                'title' => 'Configurer le paiement',
                'description' => 'Le paiement simule est actif en attendant les documents officiels Campay.',
                'done' => $paymentConfigured,
                'done_label' => 'Paiement pret',
                'action_label' => 'Voir les paiements',
                'action_url' => route('dashboard.payments.index'),
            ],
            [
                'title' => 'Obtenir le lien de vente',
                'description' => 'Copiez ce lien dans votre portail captif pour vendre le forfait.',
                'done' => (bool) $saleLink,
                'done_label' => 'Lien de vente pret',
                'action_label' => 'Voir les forfaits',
                'action_url' => route('dashboard.plans.index'),
                'sale_link' => $saleLink,
            ],
        ];

        $completedSteps = collect($steps)->where('done', true)->count();
        $progress = (int) round(($completedSteps / count($steps)) * 100);

        return view('dashboard.onboarding.index', compact(
            'steps',
            'completedSteps',
            'progress',
            'saleLink',
            'routersCount',
            'plansCount',
            'availableTicketsCount'
        ));
    }
}
