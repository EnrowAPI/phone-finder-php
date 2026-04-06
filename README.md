# Phone Finder - PHP Library

[![Packagist Version](https://img.shields.io/packagist/v/enrow/phone-finder)](https://packagist.org/packages/enrow/phone-finder)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](https://opensource.org/licenses/MIT)
[![GitHub stars](https://img.shields.io/github/stars/EnrowAPI/phone-finder-php)](https://github.com/EnrowAPI/phone-finder-php)
[![Last commit](https://img.shields.io/github/last-commit/EnrowAPI/phone-finder-php)](https://github.com/EnrowAPI/phone-finder-php/commits)

Find mobile phone numbers from LinkedIn profiles or a name and company. Integrate phone discovery into your sales outreach or enrichment pipeline.

Powered by [Enrow](https://enrow.io) -- only charged when a phone number is found.

## Installation

```bash
composer require enrow/phone-finder
```

Requires PHP 8.1+ and Guzzle 7.

## Simple Usage

### Search by LinkedIn URL (preferred)

```php
use PhoneFinder\PhoneFinder;

$search = PhoneFinder::find('your_api_key', [
    'linkedinUrl' => 'https://www.linkedin.com/in/timcook/',
]);

$result = PhoneFinder::get('your_api_key', $search['id']);

echo $result['number'];        // +14155551234
echo $result['country'];       // US
echo $result['qualification']; // found
```

### Search by name and company

```php
$search = PhoneFinder::find('your_api_key', [
    'fullName' => 'Tim Cook',
    'companyDomain' => 'apple.com',
]);
```

`find()` returns a search ID. The search runs asynchronously -- call `get()` to retrieve the result once it's ready. You can also pass a `webhook` URL to get notified automatically.

## Search by company name

If you don't have the domain, you can search by company name instead.

```php
$search = PhoneFinder::find('your_api_key', [
    'fullName' => 'Tim Cook',
    'companyName' => 'Apple Inc.',
]);
```

## Bulk search

```php
use PhoneFinder\PhoneFinder;

$batch = PhoneFinder::findBulk('your_api_key', [
    'searches' => [
        ['linkedinUrl' => 'https://www.linkedin.com/in/timcook/'],
        ['fullName' => 'Satya Nadella', 'companyDomain' => 'microsoft.com'],
        ['fullName' => 'Jensen Huang', 'companyName' => 'NVIDIA'],
    ],
]);

// $batch['batchId'], $batch['total'], $batch['status']

$results = PhoneFinder::getBulk('your_api_key', $batch['batchId']);
// $results['results'] -- array of phone results
```

Up to 5,000 searches per batch. Pass a `webhook` URL to get notified when the batch completes.

## Error handling

```php
try {
    PhoneFinder::find('bad_key', [
        'linkedinUrl' => 'https://www.linkedin.com/in/test/',
    ]);
} catch (\RuntimeException $e) {
    // $e->getMessage() contains the API error description
    // Common errors:
    // - "Invalid or missing API key" (401)
    // - "Your credit balance is insufficient." (402)
    // - "Rate limit exceeded" (429)
}
```

## Getting an API key

Register at [app.enrow.io](https://app.enrow.io) to get your API key. You get **50 free credits** with no credit card required.

50 credits per phone found (only charged when found). Paid plans start at **$17/mo** for 20 phones up to **$497/mo** for 2,000 phones. See [pricing](https://enrow.io/pricing).

## Documentation

- [Enrow API documentation](https://docs.enrow.io)
- [Full Enrow SDK](https://github.com/EnrowAPI/enrow-php) -- includes email finder, email verifier, reverse email lookup, and more

## License

MIT -- see [LICENSE](LICENSE) for details.
