<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateCommissionRulesRequest;
use App\Models\MlmPlan;
use App\Services\CommissionRuleService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class CommissionRuleManagementController extends Controller
{
    public function __construct(
        private readonly CommissionRuleService $commissionRuleService,
    ) {}

    public function index(): View
    {
        $rules = $this->commissionRuleService->rules();
        $subscriptionLevels = data_get($rules, 'subscription.level_distribution', []);
        $retailLevels = data_get($rules, 'retail.team_distribution', []);

        return view('admin.commission-rules', [
            'plans' => MlmPlan::query()->orderBy('sort_order')->get(),
            'rules' => $rules,
            'stats' => [
                'plan_count' => MlmPlan::query()->count(),
                'subscription_levels' => count($subscriptionLevels),
                'subscription_total' => collect($subscriptionLevels)->sum(),
                'retail_levels' => count($retailLevels),
                'retail_total' => collect($retailLevels)->sum(),
            ],
        ]);
    }

    public function update(UpdateCommissionRulesRequest $request): RedirectResponse
    {
        $this->commissionRuleService->update(
            $request->payload(),
            $request->user(),
        );

        return back()->with('status', 'Commission rules updated successfully.');
    }
}
