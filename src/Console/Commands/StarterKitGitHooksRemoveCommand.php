<?php

namespace Fligno\StarterKit\Console\Commands;

/**
 * Class StarterKitGitHooksRemoveCommand
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 */
class StarterKitGitHooksRemoveCommand extends StarterKitGitHooksApplyCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'sk:hooks:remove';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove Git Hooks from composer.json.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $contents = $this->getContentsFromComposerJson();

        // Unset hooks on composer.json

        collect([
            'extra' => 'hooks',
            'scripts' => 'cghooks',
        ])->each(function ($subkey, $key) use (&$contents) {
            if ($subkey === 'hooks') {
                collect($contents[$key][$subkey])->each(function ($item, $hook) use (&$contents, $key, $subkey) {
                    $contents[$key][$subkey][$hook] = [];
                });
                $this->saveContentsToComposerJson($contents);
            }

            unset($contents[$key][$subkey]);
        });

        collect([
            'post-update-cmd' => 'cghooks update',
            'post-install-cmd' => 'cghooks add --ignore-lock',
        ])->each(function ($command, $key) use (&$contents) {
            if (isset($contents['scripts'][$key])) {
                $target = $contents['scripts'][$key];

                if (is_array($target)) {
                    if ($index = array_search($command, $target)) {
                        unset($contents['scripts'][$key][$index]);
                    }
                } elseif ($target === $command) {
                    unset($contents['scripts'][$key]);
                }
            }
        });

        $this->saveContentsToComposerJson($contents);

        return self::SUCCESS;
    }
}
