<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class ConfigEditorService
{
    public static function updateMlmConfig(array $data): void
    {
        $configPath = config_path('mlm.php');

        if (!File::exists($configPath)) {
            throw new \Exception('mlm.php config not found');
        }

        $content = File::get($configPath);

        // Parse PHP array - use regex/tokenizer for safety
        $commissionData = self::formatConfigArray($data['commission']);
        $refundPolicy = var_export($data['refund_policy'], true);

        // Replace commission section
        $patterns = [
            "/'commission' => \[[\s\S]*?(?=\],)/" => "'commission' => " . $commissionData . ",",
            "/'refund_policy' => .*,/" => "'refund_policy' => {$refundPolicy},",
        ];

        $updatedContent = preg_replace(
            array_keys($patterns),
            array_values($patterns),
            $content,
            1
        );

        if ($updatedContent === $content) {
            throw new \Exception('Config update failed - pattern not matched');
        }

        File::put($configPath, $updatedContent);

        // Clear caches
        exec('php artisan config:clear');
        exec('php artisan config:cache');
        exec('php artisan route:cache');
    }

    private static function formatConfigArray(array $array): string
    {
        $result = "[\n";
        foreach ($array as $key => $value) {
            $result .= "            {$key} => " . self::exportValue($value) . ",\n";
        }
        $result .= "        ],";
        return $result;
    }

    private static function exportValue($value): string
    {
        if (is_array($value)) {
            return self::formatConfigArray($value);
        }
        return var_export($value, true);
    }
}

