<?php

namespace App\Services;

use App\Models\MlmSetting;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CommissionRuleService
{
    private const CACHE_KEY = 'mlm.commission-rules';

    private const SETTING_KEY = 'commission_rules';

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        if (! Schema::hasTable('mlm_settings')) {
            return $this->defaults();
        }

        /** @var array<string, mixed> $rules */
        $rules = Cache::rememberForever(self::CACHE_KEY, function (): array {
            $setting = MlmSetting::query()
                ->where('key', self::SETTING_KEY)
                ->first();

            if (! $setting) {
                return $this->defaults();
            }

            return array_replace_recursive($this->defaults(), $setting->payload ?? []);
        });

        return $rules;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function update(array $payload, User $admin): MlmSetting
    {
        $rules = array_replace_recursive($this->defaults(), $payload);

        /** @var MlmSetting $setting */
        $setting = DB::transaction(function () use ($rules, $admin): MlmSetting {
            return MlmSetting::query()->updateOrCreate(
                ['key' => self::SETTING_KEY],
                [
                    'payload' => $rules,
                    'updated_by' => $admin->id,
                ],
            );
        });

        Cache::forget(self::CACHE_KEY);

        return $setting->fresh();
    }

    /**
     * @return array<int, float>
     */
    public function subscriptionLevelDistribution(): array
    {
        /** @var array<int|string, float|int|string> $distribution */
        $distribution = data_get($this->rules(), 'subscription.level_distribution', []);

        return $this->normalizeDistribution($distribution);
    }

    /**
     * @return array<int, float>
     */
    public function retailTeamDistribution(): array
    {
        /** @var array<int|string, float|int|string> $distribution */
        $distribution = data_get($this->rules(), 'retail.team_distribution', []);

        return $this->normalizeDistribution($distribution);
    }

    public function binaryPairRate(): float
    {
        return round((float) data_get($this->rules(), 'binary.pair_rate', 0.10), 4);
    }

    /**
     * @return array<string, mixed>
     */
    public function refundPolicy(): array
    {
        /** @var array<string, mixed> $policy */
        $policy = data_get($this->rules(), 'refund', []);

        return $policy;
    }

    /**
     * @return array<string, mixed>
     */
    public function defaults(): array
    {
        return [
            'subscription' => [
                'level_distribution' => collect(config('mlm.commission.subscription.level_distribution', []))
                    ->mapWithKeys(fn (float|int|string $ratio, int|string $level): array => [(int) $level => (float) $ratio])
                    ->sortKeys()
                    ->all(),
            ],
            'retail' => [
                'team_distribution' => collect(config('mlm.commission.retail.team_distribution', [1 => 1.0]))
                    ->mapWithKeys(fn (float|int|string $ratio, int|string $level): array => [(int) $level => (float) $ratio])
                    ->sortKeys()
                    ->all(),
            ],
            'binary' => [
                'pair_rate' => (float) config('mlm.commission.binary.pair_rate', 0.10),
            ],
            'refund' => [
                'window_days' => 7,
                'allow_order_refunds' => true,
                'allow_subscription_refunds' => true,
                'auto_reverse_commissions' => true,
                'policy_text' => (string) config('mlm.refund_policy', 'Full refund within 7 days for unshipped orders. Commissions auto-reversed. Contact support for exceptions.'),
            ],
        ];
    }

    /**
     * @param  array<int|string, float|int|string>  $distribution
     * @return array<int, float>
     */
    private function normalizeDistribution(array $distribution): array
    {
        return collect($distribution)
            ->mapWithKeys(fn (float|int|string $ratio, int|string $level): array => [(int) $level => round((float) $ratio, 4)])
            ->filter(fn (float $ratio, int $level): bool => $level > 0 && $ratio > 0)
            ->sortKeys()
            ->all();
    }
}
