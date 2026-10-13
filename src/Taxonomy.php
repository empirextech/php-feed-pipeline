<?php

declare(strict_types=1);

namespace EmpireX\FeedPipeline;

final class Taxonomy
{
    /**
     * @param array<string, string> $rules  lowercase needle => canonical path
     */
    public function __construct(
        private array $rules,
        private readonly string $fallback = 'General',
    ) {
    }

    public function map(string $sourceCategory, string $title = ''): string
    {
        $haystack = mb_strtolower($sourceCategory . ' ' . $title);
        foreach ($this->rules as $needle => $path) {
            if ($needle !== '' && str_contains($haystack, mb_strtolower($needle))) {
                return $path;
            }
        }

        return $this->fallback;
    }
}
