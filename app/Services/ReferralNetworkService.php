<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;

class ReferralNetworkService
{
    /**
     * @return Collection<int, Collection<int, User>>
     */
    public function levels(User $user, int $maxDepth = 5): Collection
    {
        $levels = collect();
        $parentIds = collect([$user->id]);

        for ($level = 1; $level <= $maxDepth; $level++) {
            $members = User::query()
                ->whereIn('referrer_id', $parentIds)
                ->withCount('referrals')
                ->with(['currentRank', 'latestActiveSubscription.plan'])
                ->orderBy('created_at')
                ->get();

            if ($members->isEmpty()) {
                break;
            }

            $levels->put($level, $members);
            $parentIds = $members->pluck('id');
        }

        return $levels;
    }

    /**
     * @return array{
     *     direct_count:int,
     *     total_count:int,
     *     active_count:int,
     *     pending_count:int,
     *     active_direct_count:int,
     *     pending_direct_count:int,
     *     max_depth:int
     * }
     */
    public function stats(User $user, int $maxDepth = 5): array
    {
        $levels = $this->levels($user, $maxDepth);
        $directMembers = $levels->get(1, collect());
        $allMembers = $levels->flatten(1);

        return [
            'direct_count' => (int) $directMembers->count(),
            'total_count' => (int) $allMembers->count(),
            'active_count' => (int) $allMembers->filter(
                fn (User $member): bool => $member->latestActiveSubscription !== null
            )->count(),
            'pending_count' => (int) $allMembers->filter(
                fn (User $member): bool => $member->latestActiveSubscription === null
            )->count(),
            'active_direct_count' => (int) $directMembers->filter(
                fn (User $member): bool => $member->latestActiveSubscription !== null
            )->count(),
            'pending_direct_count' => (int) $directMembers->filter(
                fn (User $member): bool => $member->latestActiveSubscription === null
            )->count(),
            'max_depth' => (int) ($levels->keys()->max() ?? 0),
        ];
    }
}
