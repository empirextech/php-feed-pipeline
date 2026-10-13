<?php

declare(strict_types=1);

namespace EmpireX\FeedPipeline;

/**
 * Deterministic identity for offers coming from several advertisers.
 * Order: GTIN → brand+MPN → normalized title fingerprint.
 */
final class Deduper
{
    /**
     * @param array<string, mixed> $normalized
     */
    public function identity(array $normalized): string
    {
        $gtin = (string) ($normalized['gtin'] ?? '');
        if (strlen($gtin) >= 8) {
            return 'gtin:' . $gtin;
        }

        $brand = mb_strtolower((string) ($normalized['brand'] ?? ''));
        $mpn = (string) ($normalized['mpn'] ?? '');
        if ($brand !== '' && $mpn !== '') {
            return 'mpn:' . $brand . ':' . mb_strtolower($mpn);
        }

        $title = mb_strtolower((string) ($normalized['title'] ?? ''));

        return 'title:' . hash('sha256', $brand . '|' . $title);
    }

    /**
     * Cheap tri-gram Jaccard for near-duplicate titles when GTIN is missing.
     */
    public function titleSimilarity(string $left, string $right): float
    {
        $a = $this->grams(mb_strtolower($left));
        $b = $this->grams(mb_strtolower($right));
        if ($a === [] || $b === []) {
            return 0.0;
        }
        $inter = count(array_intersect($a, $b));
        $union = count(array_unique([...$a, ...$b]));

        return $union === 0 ? 0.0 : $inter / $union;
    }

    /**
     * @return list<string>
     */
    private function grams(string $text): array
    {
        $text = preg_replace('/[^a-z0-9]+/u', '', $text) ?? $text;
        $len = mb_strlen($text);
        if ($len < 3) {
            return $len === 0 ? [] : [$text];
        }
        $grams = [];
        for ($i = 0; $i <= $len - 3; $i++) {
            $grams[] = mb_substr($text, $i, 3);
        }

        return $grams;
    }
}
