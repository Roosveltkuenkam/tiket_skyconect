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

        $firstReadyRouter = Router::where('user_id', $user->id)
            ->where('status', 'active')
            ->whereHas('plans', function ($query) {
                $query->where('is_active', true)
                    ->whereHas('tickets', function ($ticketQuery) {
                        $ticketQuery->where('status', 'available');
                    });
            })
            ->oldest()
            ->first();

        $paymentConfigured = true;
        $saleLink = $firstReadyRouter ? $firstReadyRouter->publicPortalUrl() : null;

        $steps = [
            [
                'title' => __('ui.dashboard_pages.steps.router_title'),
                'description' => __('ui.dashboard_pages.steps.router_description'),
                'done' => $routersCount > 0,
                'done_label' => __('ui.dashboard_pages.steps.router_done'),
                'action_label' => __('ui.dashboard_pages.steps.router_title'),
                'action_url' => route('dashboard.routers.create'),
            ],
            [
                'title' => __('ui.dashboard_pages.steps.plan_title'),
                'description' => __('ui.dashboard_pages.steps.plan_description'),
                'done' => $plansCount > 0,
                'done_label' => __('ui.dashboard_pages.steps.plan_done'),
                'action_label' => __('ui.dashboard_pages.steps.plan_title'),
                'action_url' => route('dashboard.plans.create'),
            ],
            [
                'title' => __('ui.dashboard_pages.steps.tickets_title'),
                'description' => __('ui.dashboard_pages.steps.tickets_description'),
                'done' => $availableTicketsCount > 0,
                'done_label' => __('ui.dashboard_pages.steps.tickets_done'),
                'action_label' => __('ui.dashboard_pages.steps.import_action'),
                'action_url' => route('dashboard.tickets.import.create'),
            ],
            [
                'title' => __('ui.dashboard_pages.steps.payment_title'),
                'description' => __('ui.dashboard_pages.steps.payment_description'),
                'done' => $paymentConfigured,
                'done_label' => __('ui.dashboard_pages.steps.payment_done'),
                'action_label' => __('ui.dashboard_pages.steps.payment_action'),
                'action_url' => route('dashboard.payments.index'),
            ],
            [
                'title' => __('ui.dashboard_pages.steps.sale_link_title'),
                'description' => __('ui.dashboard_pages.steps.sale_link_description'),
                'done' => (bool) $saleLink,
                'done_label' => __('ui.dashboard_pages.steps.sale_link_done'),
                'action_label' => __('ui.dashboard_pages.steps.portal_action'),
                'action_url' => $saleLink ?: route('dashboard.plans.index'),
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
