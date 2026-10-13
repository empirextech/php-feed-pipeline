<?php

declare(strict_types=1);

namespace EmpireX\FeedPipeline;

final class Pipeline
{
    public function __construct(
        private readonly Normalizer $normalizer = new Normalizer(),
        private readonly Deduper $deduper = new Deduper(),
        private readonly Taxonomy $taxonomy = new Taxonomy([]),
    ) {
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return list<array<string, mixed>>
     */
    public function ingest(array $rows): array
    {
        $byId = [];
        foreach ($rows as $row) {
            $item = $this->normalizer->row($row);
            if ($item['title'] === '') {
                continue;
            }
            $item['identity'] = $this->deduper->identity($item);
            $item['taxonomy'] = $this->taxonomy->map((string) $item['category'], (string) $item['title']);
            $byId[$item['identity']] = $item;
        }

        return array_values($byId);
    }
}
