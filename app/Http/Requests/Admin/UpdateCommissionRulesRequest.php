<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateCommissionRulesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'subscription_levels' => ['required', 'array', 'min:1'],
            'subscription_levels.*.level' => ['required', 'integer', 'min:1', 'distinct'],
            'subscription_levels.*.ratio' => ['required', 'numeric', 'gt:0', 'lte:1'],
            'retail_team_distribution' => ['required', 'array', 'min:1'],
            'retail_team_distribution.*.level' => ['required', 'integer', 'min:1', 'distinct'],
            'retail_team_distribution.*.ratio' => ['required', 'numeric', 'gt:0', 'lte:1'],
            'binary_pair_rate' => ['required', 'numeric', 'gte:0', 'lte:1'],
            'refund_window_days' => ['required', 'integer', 'min:0', 'max:365'],
            'allow_order_refunds' => ['nullable', 'boolean'],
            'allow_subscription_refunds' => ['nullable', 'boolean'],
            'auto_reverse_commissions' => ['nullable', 'boolean'],
            'refund_policy_text' => ['required', 'string', 'max:5000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $subscriptionTotal = collect($this->validated('subscription_levels', []))
                ->sum(fn (array $item): float => (float) ($item['ratio'] ?? 0));

            if ($subscriptionTotal > 1.0001) {
                $validator->errors()->add('subscription_levels', 'Subscription level distribution cannot exceed 100%.');
            }

            $retailTotal = collect($this->validated('retail_team_distribution', []))
                ->sum(fn (array $item): float => (float) ($item['ratio'] ?? 0));

            if ($retailTotal > 1.0001) {
                $validator->errors()->add('retail_team_distribution', 'Retail team distribution cannot exceed 100%.');
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return [
            'subscription' => [
                'level_distribution' => collect($this->validated('subscription_levels'))
                    ->mapWithKeys(fn (array $item): array => [
                        (int) $item['level'] => round((float) $item['ratio'], 4),
                    ])
                    ->sortKeys()
                    ->all(),
            ],
            'retail' => [
                'team_distribution' => collect($this->validated('retail_team_distribution'))
                    ->mapWithKeys(fn (array $item): array => [
                        (int) $item['level'] => round((float) $item['ratio'], 4),
                    ])
                    ->sortKeys()
                    ->all(),
            ],
            'binary' => [
                'pair_rate' => round((float) $this->validated('binary_pair_rate'), 4),
            ],
            'refund' => [
                'window_days' => (int) $this->validated('refund_window_days'),
                'allow_order_refunds' => $this->boolean('allow_order_refunds'),
                'allow_subscription_refunds' => $this->boolean('allow_subscription_refunds'),
                'auto_reverse_commissions' => $this->boolean('auto_reverse_commissions'),
                'policy_text' => trim((string) $this->validated('refund_policy_text')),
            ],
        ];
    }
}
