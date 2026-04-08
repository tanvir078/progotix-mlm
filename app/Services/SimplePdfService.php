<?php

namespace App\Services;

class SimplePdfService
{
    /**
     * @param  array<int, string>  $lines
     */
    public function makeDocument(string $title, array $lines): string
    {
        $title = $this->escape($title);
        $content = "BT\n/F1 20 Tf\n50 790 Td\n({$title}) Tj\nET\n";

        $y = 760;

        foreach ($lines as $line) {
            $escaped = $this->escape($line);
            $content .= "BT\n/F1 11 Tf\n50 {$y} Td\n({$escaped}) Tj\nET\n";
            $y -= 22;
        }

        $stream = "<< /Length ".strlen($content)." >>\nstream\n{$content}endstream";

        $objects = [];
        $objects[] = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj";
        $objects[] = "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj";
        $objects[] = "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>\nendobj";
        $objects[] = "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj";
        $objects[] = "5 0 obj\n{$stream}\nendobj";

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object."\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        $pdf .= "trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefOffset}\n%%EOF";

        return $pdf;
    }

    private function escape(string $value): string
    {
        $sanitized = preg_replace('/[^\x20-\x7E]/', '?', $value) ?? '';

        return str_replace(['\\', '(', ')'], ['\\\\', '\(', '\)'], $sanitized);
    }
}
