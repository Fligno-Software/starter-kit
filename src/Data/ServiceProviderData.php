<?php

namespace Fligno\StarterKit\Data;

use Fligno\StarterKit\Abstracts\BaseJsonSerializable;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

/**
 * Class ServiceProviderData
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 */
class ServiceProviderData extends BaseJsonSerializable
{
    /**
     * @var string
     */
    public string $name;

    /**
     * @var string
     */
    public string $composer;

    /**
     * @var string|null
     */
    public string|null $package = null;

    /**
     * @var string|null
     */
    public string|null $domain = null;

    /**
     * @var string
     */
    public string $path;

    /**
     * @param  mixed  $data
     * @param  string|null  $key
     */
    public function __construct(mixed $data = [], ?string $key = null)
    {
        parent::__construct($data, $key);

        // Add to StarterKit's paths
        if ($data instanceof ServiceProvider) {
            starterKit()->addToPaths($this);
        }
    }

    /**
     * @param  ServiceProvider  $provider
     * @return array
     */
    protected function parseServiceProvider(ServiceProvider $provider): array
    {
        $domain = domain_encode(get_class($provider));
        $provider_directory = get_dir_from_object_class_dir($provider);

        $domain_decoded = null;

        $directory = Str::of($provider_directory)
            ->when(
                $domain,
                function (Stringable $str) use ($domain, &$domain_decoded) {
                    return $str->before($domain_decoded = domain_decode($domain));
                },
                fn (Stringable $str) => $str->before('src')->before('app')
            )
            ->jsonSerialize();

        $search = 'composer.json';

        $composer = guess_file_or_directory_path($directory, $search, true);

        $package = get_contents_from_composer_json($composer)?->get('name');

        $package = $package == 'laravel/laravel' ? null : $package;

        $path = Str::of($composer)
            ->before($search)
            ->when($domain_decoded, fn (Stringable $str) => $str->rtrim('/')->append($domain_decoded))
            ->jsonSerialize();

        return [
            'name' => class_basename(get_class($provider)),
            'composer' => $composer,
            'package' => $package,
            'domain' => $domain,
            'path' => $path,
        ];
    }

    /**
     * @return Collection
     */
    public function getPackageDomainData(): Collection
    {
        return starterKit()->getPaths($this->package, $this->domain);
    }
}
