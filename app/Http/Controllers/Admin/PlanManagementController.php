<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MlmPlan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PlanManagementController extends Controller
{
    public function index(): View
    {
        return view('admin.plans', [
            'plans' => MlmPlan::query()->orderBy('sort_order')->paginate(12),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        MlmPlan::create($validated);

        return back()->with('status', 'Plan created successfully.');
    }

    public function update(Request $request, MlmPlan $plan): RedirectResponse
    {
        $validated = $this->validated($request, $plan);

        $plan->update($validated);

        return back()->with('status', 'Plan updated successfully.');
    }

    public function destroy(MlmPlan $plan): RedirectResponse
    {
        $plan->delete();

        return back()->with('status', 'Plan deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?MlmPlan $plan = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'code' => ['required', 'string', 'max:50', Rule::unique(MlmPlan::class, 'code')->ignore($plan?->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0'],
            'direct_bonus' => ['required', 'numeric', 'min:0'],
            'level_bonus' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]) + [
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
