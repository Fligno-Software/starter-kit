<?php

namespace Fligno\StarterKit\Data;

use Fligno\StarterKit\Abstracts\BaseJsonSerializable;
use Fligno\StarterKit\Abstracts\BaseStarterKitServiceProvider;
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
    public string $class;

    /**
     * @var string|null
     */
    public string|null $composer;

    public ServiceProvider|null $provider;

    /**
     * @var string|null
     */
    public string|null $package = null;

    /**
     * @var string|null
     */
    public string|null $domain = null;

    /**
     * @var string|null
     */
    public string|null $path;

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
        $this->provider = $provider;

        $class = get_class($provider);
        $domain = domain_encode($class);
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

        info($class.': '.$composer);

        $path = Str::of($composer)
            ->before($search)
            ->when($domain_decoded, fn (Stringable $str) => $str->rtrim('/')->append($domain_decoded))
            ->jsonSerialize();

        return [
            'class' => get_class($provider),
            'composer' => $composer,
            'package' => $package,
            'domain' => $domain,
            'path' => $path,
        ];
    }

    /**
     * @return ServiceProvider|null
     */
    public function getServiceProvider(): ?ServiceProvider
    {
        return $this->provider ?? app()->getProvider($this->class);
    }

    /**
     * @return Collection|null
     */
    public function getPackageEnvVars(): Collection|null
    {
        $provider = $this->getServiceProvider();
        if ($provider instanceof BaseStarterKitServiceProvider) {
            return collect($provider->getEnvVars())
                ->map(fn ($item) => (is_string($item) || is_null($item)) ? $item : json_encode($item));
        }

        return null;
    }

    /**
     * @return bool
     */
    public function publishEnvVars(): bool
    {
        if ($env_vars = $this->getPackageEnvVars()) {
            $title = $this->package ?? 'Laravel';
            if ($this->domain) {
                $title .= ' ('.$this->domain.')';
            }

            return add_contents_to_env($env_vars, $title);
        }

        return false;
    }
}
