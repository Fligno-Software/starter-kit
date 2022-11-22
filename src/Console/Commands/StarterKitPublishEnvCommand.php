<?php

namespace Fligno\StarterKit\Console\Commands;

use Fligno\BoilerplateGenerator\Traits\UsesCommandMultipleTargetsTrait;
use Fligno\StarterKit\Data\ServiceProviderData;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

/**
 * Class StarterKitPublishEnvCommand
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 */
class StarterKitPublishEnvCommand extends Command
{
    use UsesCommandMultipleTargetsTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'sk:env:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish all environment variables from root, packages, and domains.';

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->addMultipleTargetsOption();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->setupOutputFormatters();

        if (! starter_kit()->shouldPublishEnvVars()) {
            $this->failed('Failed to publish any env variables');
            $this->info('Set "SK_PUBLISH_ENV_VARS" env variable to true');

            return self::FAILURE;
        }

        $this->setTargetsAndDomains();

        $providers = starter_kit()->getProviders()
            ->mapToGroups(fn (array $arr) => [$arr['package'] ?? $this->default_package => $arr])
            ->only($this->targets);

        $progress = $this->output->createProgressBar($providers->count());

        $this->note('Only service providers that extends <default-bold>Fligno\StarterKit\Abstracts\BaseStarterKitServiceProvider</default-bold> will be processed.');
        $this->newLine();
        $providers->each(function (Collection $list, string $package) use ($progress) {
            $progress->advance();
            $progress->display();
            $this->newLine(2);

            $count = '<green-bold>'.$list->count().'</green-bold>';
            $package = $package == $this->default_package ? 'Laravel' : $package;

            $this->ongoing("Processing $count service provider/s from <default-bold>$package</default-bold>");

            $list->each(function (array $arr) {
                $data = ServiceProviderData::from($arr);

                $of = $data->domain ? " of <default-bold>$data->domain</default-bold> domain" : null;
                $message = "env variables from <default-bold>$data->class</default-bold>$of";

                if ($data->publishEnvVars()) {
                    $this->ongoing("Successfully published $message");
                } else {
                    $this->warning("Failed to publish $message");
                }
            });
            $this->newLine();
        });

        return self::SUCCESS;
    }

    /**
     * Class type to append on filename.
     *
     * @return string|null
     */
    protected function getClassType(): ?string
    {
        return null;
    }
}
