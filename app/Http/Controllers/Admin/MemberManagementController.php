<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class MemberManagementController extends Controller
{
    public function __invoke(Request $request): View
    {
        $search = trim((string) $request->string('search'));

        $members = User::query()
            ->where('is_admin', false)
            ->with(['referrer', 'binaryParent', 'subscriptions.plan'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nested) use ($search): void {
                    $nested
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('member_code', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.members', [
            'members' => $members,
            'search' => $search,
            'referrers' => User::query()
                ->orderBy('name')
                ->get(['id', 'name', 'username']),
        ]);
    }
}
