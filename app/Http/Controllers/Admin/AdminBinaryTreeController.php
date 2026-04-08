<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\BinaryTreeService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AdminBinaryTreeController extends Controller
{
    public function __construct(
        private readonly BinaryTreeService $binaryTreeService,
    ) {
    }

    public function __invoke(Request $request, ?User $user = null): View
    {
        $root = $user
            ?? User::query()->whereNull('binary_parent_id')->orderByDesc('is_admin')->orderBy('id')->firstOrFail();

        return view('admin.binary-tree', [
            'root' => $root->load(['binaryParent', 'binaryChildren']),
            'tree' => $this->binaryTreeService->snapshot($root, 3),
            'members' => User::query()->where('is_admin', false)->orderBy('name')->get(['id', 'name', 'username']),
        ]);
    }
}
