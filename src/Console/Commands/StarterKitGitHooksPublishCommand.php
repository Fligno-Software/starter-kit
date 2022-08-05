<?php

namespace Fligno\StarterKit\Console\Commands;

use Fligno\StarterKit\Traits\UsesCommandCustomMessagesTrait;
use Illuminate\Console\Command;

/**
 * Class StarterKitGitHooksPublishCommand
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 */
class StarterKitGitHooksPublishCommand extends Command
{
    use UsesCommandCustomMessagesTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'sk:hooks:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish the Git Hooks config file.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->ongoing('Publishing "git-hooks.php" config file...');

        $this->call('vendor:publish', ['--tag' => 'git-hooks.config']);

        $this->done('Published "git-hooks.php" config file...');

        return self::SUCCESS;
    }
}
