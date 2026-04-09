<?php

namespace App\Services;

use App\Models\MlmPaymentMethod;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PaymentMethodService
{
    /**
     * @return Collection<int, MlmPaymentMethod>
     */
    public function availableForUser(User $user, ?string $flow = null): Collection
    {
        return $this->queryForUser($user, $flow)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function findAvailableForUser(User $user, int $paymentMethodId, string $flow): ?MlmPaymentMethod
    {
        return $this->queryForUser($user, $flow)
            ->whereKey($paymentMethodId)
            ->first();
    }

    private function queryForUser(User $user, ?string $flow = null): Builder
    {
        return MlmPaymentMethod::query()
            ->where('is_active', true)
            ->when($flow === 'deposit', fn (Builder $query) => $query->where('supports_deposit', true))
            ->when($flow === 'withdrawal', fn (Builder $query) => $query->where('supports_withdrawal', true))
            ->where(function (Builder $query) use ($user): void {
                $query->whereNull('country_code');

                if ($user->country_code) {
                    $query->orWhere('country_code', $user->country_code);
                }
            });
    }
}
