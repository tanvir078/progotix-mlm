<?php

namespace App\Http\Controllers;

use App\Models\MlmPlan;
use App\Services\MlmActivationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function __construct(
        private readonly MlmActivationService $activationService,
    ) {
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        return view('mlm.plans', [
            'plans' => MlmPlan::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(),
            'activeSubscription' => $user->activeSubscription(),
            'history' => $user->subscriptions()
                ->with('plan')
                ->latest('started_at')
                ->get(),
        ]);
    }

    public function store(Request $request, MlmPlan $plan): RedirectResponse
    {
        $user = $request->user();

        abort_unless($plan->is_active, 404);

        $this->activationService->activate($user, $plan);

        return back()->with('status', "{$plan->name} package activated successfully.");
    }
}
