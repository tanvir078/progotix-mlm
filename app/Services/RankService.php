<?php

namespace App\Services;

use App\Models\MlmRank;
use App\Models\MlmRankAchievement;
use App\Models\MlmTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RankService
{
    /**
     * @return array{direct_referrals:int,personal_sales:float,team_volume:float}
     */
    public function metrics(User $user): array
    {
        return [
            'direct_referrals' => $user->referrals()->count(),
            'personal_sales' => (float) $user->retailSalesTotal(),
            'team_volume' => (float) $user->teamSalesVolume(),
        ];
    }

    public function currentEligibleRank(User $user): ?MlmRank
    {
        $metrics = $this->metrics($user);

        return MlmRank::query()
            ->orderByDesc('sort_order')
            ->get()
            ->first(function (MlmRank $rank) use ($metrics): bool {
                return $metrics['direct_referrals'] >= $rank->direct_referrals_required
                    && $metrics['personal_sales'] >= (float) $rank->personal_sales_required
                    && $metrics['team_volume'] >= (float) $rank->team_volume_required;
            });
    }

    public function sync(User $user): ?MlmRankAchievement
    {
        $eligibleRank = $this->currentEligibleRank($user);

        return DB::transaction(function () use ($user, $eligibleRank): ?MlmRankAchievement {
            $user->refresh();

            if (! $eligibleRank) {
                if ($user->current_rank_id !== null) {
                    $user->forceFill([
                        'current_rank_id' => null,
                    ])->save();
                }

                return null;
            }

            if ($user->current_rank_id === $eligibleRank->id) {
                return null;
            }

            $achievement = MlmRankAchievement::query()->firstOrCreate(
                [
                    'user_id' => $user->id,
                    'rank_id' => $eligibleRank->id,
                ],
                [
                    'bonus_amount' => $eligibleRank->bonus_amount,
                    'achieved_at' => now(),
                ]
            );

            $user->forceFill([
                'current_rank_id' => $eligibleRank->id,
            ])->save();

            if ($achievement->wasRecentlyCreated && (float) $eligibleRank->bonus_amount > 0) {
                $user->transactions()->create([
                    'type' => MlmTransaction::TYPE_RANK_BONUS,
                    'direction' => 'credit',
                    'amount' => $eligibleRank->bonus_amount,
                    'title' => $eligibleRank->name.' rank upgrade reward',
                    'note' => 'Rank reward generated from retail-first performance goals.',
                    'posted_at' => now(),
                ]);

                $user->increment('balance', $eligibleRank->bonus_amount);
            }

            return $achievement;
        });
    }
}
