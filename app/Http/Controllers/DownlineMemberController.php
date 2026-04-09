<?php

namespace App\Http\Controllers;

use App\Http\Requests\Mlm\StoreDownlineMemberRequest;
use App\Services\MemberRegistrationService;
use Illuminate\Http\RedirectResponse;

class DownlineMemberController extends Controller
{
    public function __construct(
        private readonly MemberRegistrationService $memberRegistrationService,
    ) {}

    public function store(StoreDownlineMemberRequest $request): RedirectResponse
    {
        $member = $this->memberRegistrationService->registerUnderSponsor(
            $request->payload(),
            $request->user(),
            $request->placementPreference(),
            true,
        );

        return back()->with('status', 'Downline ID '.$member->member_code.' opened successfully for @'.$member->username.'.');
    }
}
