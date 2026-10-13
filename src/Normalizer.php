<?php

declare(strict_types=1);

namespace EmpireX\FeedPipeline;

final class Normalizer
{
    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    public function row(array $row): array
    {
        $row = $this->lowerKeys($row);

        return [
            'title' => $this->title((string) ($row['title'] ?? $row['name'] ?? '')),
            'brand' => $this->token((string) ($row['brand'] ?? $row['manufacturer'] ?? '')),
            'mpn' => strtoupper($this->token((string) ($row['mpn'] ?? $row['manufacturer_part_number'] ?? ''))),
            'gtin' => $this->digits((string) ($row['gtin'] ?? $row['ean'] ?? $row['ean13'] ?? $row['barcode'] ?? '')),
            'sku' => $this->token((string) ($row['sku'] ?? $row['id'] ?? '')),
            'price_minor' => $this->priceMinor($row['price'] ?? $row['sale_price'] ?? 0),
            'currency' => strtoupper(substr((string) ($row['currency'] ?? 'EUR'), 0, 3)) ?: 'EUR',
            'category' => $this->title((string) ($row['category'] ?? $row['product_type'] ?? $row['category_path'] ?? '')),
            'url' => trim((string) ($row['url'] ?? $row['link'] ?? '')),
            'image' => trim((string) ($row['image'] ?? $row['image_link'] ?? '')),
            'availability' => $this->availability($row),
        ];
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function lowerKeys(array $row): array
    {
        $out = [];
        foreach ($row as $key => $value) {
            $out[strtolower((string) $key)] = $value;
        }

        return $out;
    }

    private function title(string $value): string
    {
        $text = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = trim(preg_replace('/\s+/u', ' ', $text) ?? $text);

        return mb_substr($text, 0, 180);
    }

    private function token(string $value): string
    {
        return trim(preg_replace('/\s+/', ' ', $value) ?? $value);
    }

    private function digits(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?? '';
    }

    private function priceMinor(mixed $value): int
    {
        if (is_int($value)) {
            return max(0, $value);
        }
        $decimal = number_format((float) str_replace(',', '.', (string) $value), 2, '.', '');

        $parts = explode('.', $decimal, 2);

        return ((int) $parts[0] * 100) + (int) ($parts[1] ?? 0);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function availability(array $row): string
    {
        $raw = strtolower((string) ($row['availability'] ?? $row['stock_status'] ?? ''));
        $stock = (int) ($row['stock'] ?? 0);

        return match (true) {
            str_contains($raw, 'out') => 'out_of_stock',
            $stock > 0, str_contains($raw, 'in stock'), $raw === 'in_stock' => 'in_stock',
            default => 'out_of_stock',
        };
    }
}
