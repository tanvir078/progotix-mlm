<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class NetworkController extends Controller
{
    public function __invoke(Request $request): View
    {
        /** @var User $user */
        $user = $request->user()->load('referrer');

        $directReferrals = $user->referrals()
            ->withCount('referrals')
            ->latest()
            ->get();

        $secondLevelMembers = $directReferrals->load('referrals')->flatMap(
            fn (User $referral): Collection => $referral->referrals
        );

        return view('mlm.network', [
            'user' => $user,
            'directReferrals' => $directReferrals,
            'secondLevelMembers' => $secondLevelMembers,
        ]);
    }
}
