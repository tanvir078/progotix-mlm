<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MemberCrudController extends Controller
{
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique(User::class, 'username')->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class, 'email')->ignore($user->id)],
            'balance' => ['required', 'numeric', 'min:0'],
            'referrer_id' => ['nullable', 'integer', Rule::exists(User::class, 'id')],
            'is_admin' => ['nullable', 'boolean'],
        ]);

        $user->update([
            ...$validated,
            'is_admin' => $request->boolean('is_admin'),
        ]);

        return back()->with('status', 'Member updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return back()->with('status', 'Member deleted successfully.');
    }
}
