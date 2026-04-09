<?php

namespace App\Livewire\Admin;

use App\Services\ConfigEditorService;
use Livewire\Component;
use Livewire\WithFileUploads; // Not needed but for future

class CommissionRules extends Component
{
    public array $subscriptionLevels = [];
    public array $retailTeamDist = [];
    public array $binaryRates = [
        'left_right' => 0.10,
        'matching_bonus' => 0.05,
    ];
    public string $refundPolicy = '';

    public function mount()
    {
        $this->subscriptionLevels = config('mlm.commission.subscription.level_distribution', []);
        $this->retailTeamDist = config('mlm.commission.retail.team_distribution', []);
        $this->binaryRates = config('mlm.commission.binary', ['left_right' => 0.10]);
        $this->refundPolicy = config('mlm.refund_policy', 'Default: Full refund within 7 days, commissions reversed.');
    }

    public function addSubscriptionLevel()
    {
        $this->subscriptionLevels[] = ['level' => count($this->subscriptionLevels) + 1, 'ratio' => 0.00];
    }

    public function removeSubscriptionLevel($index)
    {
        unset($this->subscriptionLevels[$index]);
        $this->subscriptionLevels = array_values($this->subscriptionLevels);
    }

    public function addRetailDist()
    {
        $this->retailTeamDist[] = ['level' => count($this->retailTeamDist) + 1, 'ratio' => 0.00];
    }

    public function removeRetailDist($index)
    {
        unset($this->retailTeamDist[$index]);
        $this->retailTeamDist = array_values($this->retailTeamDist);
    }

    public function save()
    {
        $configData = [
            'commission' => [
                'subscription' => [
                    'level_distribution' => collect($this->subscriptionLevels)
                        ->mapWithKeys(fn($item) => [(int)$item['level'] => (float)$item['ratio']])
                        ->toArray(),
                ],
                'retail' => [
                    'team_distribution' => collect($this->retailTeamDist)
                        ->mapWithKeys(fn($item) => [(int)$item['level'] => (float)$item['ratio']])
                        ->toArray(),
                ],
                'binary' => $this->binaryRates,
            ],
            'refund_policy' => $this->refundPolicy,
        ];

        ConfigEditorService::updateMlmConfig($configData);

        $this->dispatch('config-saved');
        session()->flash('message', 'Commission rules updated and cached.');
    }

    public function render()
    {
        return view('livewire.admin.commission-rules');
    }
}

