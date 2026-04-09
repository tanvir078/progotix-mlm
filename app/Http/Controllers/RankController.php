<?php

namespace App\Http\Controllers;

use App\Models\MlmRank;
use App\Services\RankService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class RankController extends Controller
{
    public function __construct(
        private readonly RankService $rankService,
    ) {}

    public function __invoke(Request $request): View
    {
        $user = $request->user()->load(['currentRank', 'rankAchievements.rank']);
        $metrics = $this->rankService->metrics($user);
        $ranks = MlmRank::query()->orderBy('sort_order')->get();
        $currentRank = $this->rankService->currentEligibleRank($user);
        $nextRank = $ranks->first(function (MlmRank $rank) use ($metrics): bool {
            return $metrics['direct_referrals'] < $rank->direct_referrals_required
                || $metrics['personal_sales'] < (float) $rank->personal_sales_required
                || $metrics['team_volume'] < (float) $rank->team_volume_required;
        });
        $achievements = $user->rankAchievements()->with('rank')->latest('achieved_at')->get();

        $progress = $nextRank ? [
            [
                'label' => 'Direct Referrals',
                'current' => (float) $metrics['direct_referrals'],
                'target' => (float) $nextRank->direct_referrals_required,
                'suffix' => '',
            ],
            [
                'label' => 'Personal Sales',
                'current' => (float) $metrics['personal_sales'],
                'target' => (float) $nextRank->personal_sales_required,
                'suffix' => ' USD',
            ],
            [
                'label' => 'Team Volume',
                'current' => (float) $metrics['team_volume'],
                'target' => (float) $nextRank->team_volume_required,
                'suffix' => ' BV',
            ],
        ] : collect();

        $progress = collect($progress)->map(function (array $item): array {
            $target = max(0.01, (float) $item['target']);
            $current = (float) $item['current'];

            return [
                ...$item,
                'remaining' => max(0, round($target - $current, 2)),
                'percentage' => min(100, round(($current / $target) * 100, 1)),
            ];
        });

        return view('mlm.ranks', [
            'currentRank' => $currentRank,
            'nextRank' => $nextRank,
            'metrics' => $metrics,
            'ranks' => $ranks,
            'achievements' => $achievements,
            'progress' => $progress,
            'rankStats' => [
                'current_rank' => $currentRank?->name ?? 'Starter',
                'unlocked_count' => $currentRank
                    ? (int) $ranks->where('sort_order', '<=', $currentRank->sort_order)->count()
                    : 0,
                'achievement_count' => (int) $achievements->count(),
                'reward_total' => (float) $achievements->sum('bonus_amount'),
            ],
        ]);
    }
}
