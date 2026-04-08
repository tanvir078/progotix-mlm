<?php

namespace App\Http\Controllers;

use App\Services\BinaryTreeService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class BinaryTreeController extends Controller
{
    public function __construct(
        private readonly BinaryTreeService $binaryTreeService,
    ) {
    }

    public function __invoke(Request $request): View
    {
        $user = $request->user()->load(['binaryParent', 'binaryChildren', 'binaryLedger']);

        return view('mlm.binary-tree', [
            'user' => $user,
            'tree' => $this->binaryTreeService->snapshot($user, 2),
        ]);
    }
}
