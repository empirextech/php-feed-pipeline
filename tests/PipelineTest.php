<?php

declare(strict_types=1);

namespace EmpireX\FeedPipeline\Tests;

use EmpireX\FeedPipeline\Deduper;
use EmpireX\FeedPipeline\Pipeline;
use EmpireX\FeedPipeline\Taxonomy;
use PHPUnit\Framework\TestCase;

final class PipelineTest extends TestCase
{
    public function testDedupesTwoAdvertisersOnTheSameGtin(): void
    {
        $pipeline = new Pipeline(taxonomy: new Taxonomy(['pipe' => 'Plumbing > Copper']));
        $out = $pipeline->ingest([
            ['TITLE' => 'Copper pipe 15mm', 'ean' => '5901234123457', 'price' => '12.50', 'brand' => 'Acme'],
            ['name' => 'Pipe copper 15 mm', 'gtin' => '590 1234 123457', 'price' => 13.10, 'brand' => 'Other'],
        ]);

        self::assertCount(1, $out);
        self::assertSame('gtin:5901234123457', $out[0]['identity']);
        self::assertSame('Plumbing > Copper', $out[0]['taxonomy']);
        self::assertSame(1310, $out[0]['price_minor']);
    }

    public function testTitleSimilarityForNearDuplicates(): void
    {
        $score = (new Deduper())->titleSimilarity('Copper pipe 15mm fitting', 'copper pipe 15 mm fitting');
        self::assertGreaterThan(0.5, $score);
    }
}
