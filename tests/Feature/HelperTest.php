<?php

use Illuminate\Support\Str;
use function Pest\Faker\faker;
use function Pest\Laravel\artisan;
use function PHPUnit\Framework\assertTrue;

it('can update env variables', function (string $key, string $value, string|null $title, bool $override) {
    assertTrue(set_contents_to_env([$key => $value], $title, $override));

    $key = Str::of($key)->lower()->snake('_')->upper()->jsonSerialize();
    $find[] = get_combined_key_value($key, $value);

    if ($title) {
        $find[] = Str::of($title)->headline()->prepend('# ')->jsonSerialize();
    }

    assertTrue(Str::contains(file_get_contents(base_path('.env')), $find));

    $value = Str::random(20);

    expect(set_contents_to_env([$key => $value], $title, $override))->toBe($override);
})->with([
    'with title' => [
        'key' => faker()->sentence(faker()->numberBetween(1, 2)),
        'value' => faker()->sentence(faker()->numberBetween(1, 3)),
        'title' => faker()->sentence,
        'override' => false
    ],
    'without title' => [
        'key' => faker()->sentence(faker()->numberBetween(1, 2)),
        'value' => faker()->sentence(faker()->numberBetween(1, 3)),
        'title' => null,
        'override' => false
    ],
    'with title and override to true' => [
        'key' => faker()->sentence(faker()->numberBetween(1, 2)),
        'value' => faker()->sentence(faker()->numberBetween(1, 3)),
        'title' => faker()->sentence,
        'override' => false
    ],
    'without title and override to true' => [
        'key' => faker()->sentence(faker()->numberBetween(1, 2)),
        'value' => faker()->sentence(faker()->numberBetween(1, 3)),
        'title' => null,
        'override' => false
    ]
]);
