<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ReferralNetworkService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class NetworkController extends Controller
{
    public function __construct(
        private readonly ReferralNetworkService $referralNetworkService,
    ) {}

    public function __invoke(Request $request): View
    {
        /** @var User $user */
        $user = $request->user()->load([
            'referrer',
            'binaryParent',
            'binaryChildren',
            'currentRank',
            'latestActiveSubscription.plan',
        ]);

        $downlineLevels = $this->referralNetworkService->levels($user, 5);
        $directReferrals = $downlineLevels->get(1, collect());
        $binaryPositions = $user->binaryChildren
            ->pluck('binary_position')
            ->filter()
            ->values();

        return view('mlm.network', [
            'user' => $user,
            'directReferrals' => $directReferrals,
            'downlineLevels' => $downlineLevels,
            'downlineStats' => $this->referralNetworkService->stats($user, 5),
            'placementAvailability' => [
                'left_open' => ! $binaryPositions->contains(User::BINARY_LEFT),
                'right_open' => ! $binaryPositions->contains(User::BINARY_RIGHT),
            ],
        ]);
    }
}
