<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MlmRank;
use App\Models\User;
use App\Services\RankService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RankManagementController extends Controller
{
    public function __construct(
        private readonly RankService $rankService,
    ) {}

    public function index(): View
    {
        return view('admin.ranks', [
            'ranks' => MlmRank::query()
                ->withCount('achievements')
                ->orderBy('sort_order')
                ->paginate(12),
            'badgeColors' => ['zinc', 'emerald', 'sky', 'amber', 'violet', 'rose'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'slug' => Str::slug((string) ($request->input('slug') ?: $request->input('name'))),
        ]);

        $data = $this->validateRank($request);

        MlmRank::query()->create($data);
        $this->syncRanks();

        return back()->with('status', 'Rank created successfully.');
    }

    public function update(Request $request, MlmRank $rank): RedirectResponse
    {
        $request->merge([
            'slug' => Str::slug((string) ($request->input('slug') ?: $request->input('name'))),
        ]);

        $data = $this->validateRank($request, $rank);

        $rank->update($data);
        $this->syncRanks();

        return back()->with('status', 'Rank updated successfully.');
    }

    public function destroy(MlmRank $rank): RedirectResponse
    {
        $rank->delete();
        $this->syncRanks();

        return back()->with('status', 'Rank deleted successfully.');
    }

    private function validateRank(Request $request, ?MlmRank $rank = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('mlm_ranks', 'slug')->ignore($rank?->id)],
            'badge_color' => ['required', 'string', Rule::in(['zinc', 'emerald', 'sky', 'amber', 'violet', 'rose'])],
            'direct_referrals_required' => ['required', 'integer', 'min:0'],
            'personal_sales_required' => ['required', 'numeric', 'min:0'],
            'team_volume_required' => ['required', 'numeric', 'min:0'],
            'bonus_amount' => ['required', 'numeric', 'min:0'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);
    }

    private function syncRanks(): void
    {
        User::query()
            ->where('is_admin', false)
            ->get()
            ->each(fn (User $user) => $this->rankService->sync($user));
    }
}
