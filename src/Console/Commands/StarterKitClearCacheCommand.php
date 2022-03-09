<?php

namespace Fligno\StarterKit\Console\Commands;

use Illuminate\Console\Command;

/**
 * Class StarterKitClearCacheCommand
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 */
class StarterKitClearCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'sk:cache:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear cached file/folder locations, policy maps, observer maps, and repository maps.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        starterKit()->clearCache();

        return self::SUCCESS;
    }
}
