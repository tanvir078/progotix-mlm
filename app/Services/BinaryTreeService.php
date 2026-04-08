<?php

namespace App\Services;

use App\Models\User;
use SplQueue;

class BinaryTreeService
{
    public function placeUser(User $user, ?User $sponsor = null): void
    {
        if ($user->binary_parent_id || ! $sponsor) {
            return;
        }

        $queue = new SplQueue();
        $queue->enqueue($sponsor->fresh());

        while (! $queue->isEmpty()) {
            /** @var User $candidate */
            $candidate = $queue->dequeue();
            $candidate->loadMissing('binaryChildren');

            $leftChild = $candidate->binaryChildren->firstWhere('binary_position', User::BINARY_LEFT);
            $rightChild = $candidate->binaryChildren->firstWhere('binary_position', User::BINARY_RIGHT);

            if (! $leftChild) {
                $user->forceFill([
                    'binary_parent_id' => $candidate->id,
                    'binary_position' => User::BINARY_LEFT,
                ])->save();

                return;
            }

            if (! $rightChild) {
                $user->forceFill([
                    'binary_parent_id' => $candidate->id,
                    'binary_position' => User::BINARY_RIGHT,
                ])->save();

                return;
            }

            $queue->enqueue($leftChild);
            $queue->enqueue($rightChild);
        }
    }

    public function snapshot(User $root, int $depth = 3): array
    {
        return [
            'member' => $root,
            'left' => $depth > 0 ? $this->snapshotChild($root, User::BINARY_LEFT, $depth - 1) : null,
            'right' => $depth > 0 ? $this->snapshotChild($root, User::BINARY_RIGHT, $depth - 1) : null,
        ];
    }

    private function snapshotChild(User $root, string $position, int $depth): ?array
    {
        $child = $root->binaryChildren()->where('binary_position', $position)->first();

        if (! $child) {
            return null;
        }

        return [
            'member' => $child,
            'left' => $depth > 0 ? $this->snapshotChild($child, User::BINARY_LEFT, $depth - 1) : null,
            'right' => $depth > 0 ? $this->snapshotChild($child, User::BINARY_RIGHT, $depth - 1) : null,
        ];
    }
}
