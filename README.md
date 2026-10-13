# PHP Feed Pipeline

Advertiser feeds arrive as messy CSV/XML. Two merchants sell the same SKU under different titles. This library is the ingest core we use before search, storefront, or affiliate click-out.

Built by **SC EMPIRE GLOBAL SOLUTIONS SRL** (EMPIREX Tech). Original demonstration of the affiliate-aggregation domain: normalize, identity, taxonomy. Not a dump of a live advertiser network, not 2Performant credentials, not a product catalog.

## Pipeline

```
raw row → Normalizer → Deduper identity → Taxonomy map → unique offer
```

1. **Normalize** — case-insensitive keys, HTML-stripped titles, digit GTIN, integer money.
2. **Dedupe** — GTIN, then brand+MPN, then title fingerprint. Trigram Jaccard for near-duplicates.
3. **Taxonomy** — first matching needle wins, otherwise `General`.

## Usage

```php
$pipeline = new EmpireX\FeedPipeline\Pipeline(
    taxonomy: new EmpireX\FeedPipeline\Taxonomy(['pipe' => 'Plumbing > Copper']),
);

$offers = $pipeline->ingest([
    ['TITLE' => 'Copper pipe 15mm', 'ean' => '5901234123457', 'price' => '12.50'],
    ['name' => 'Pipe copper 15 mm', 'gtin' => '590 1234 123457', 'price' => 13.10],
]);
// one offer, identity gtin:5901234123457
```

## Tests

```bash
composer install
composer test
```

## License

MIT — EMPIREX Tech / SC EMPIRE GLOBAL SOLUTIONS SRL
