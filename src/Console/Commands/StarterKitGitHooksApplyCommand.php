<?php

namespace Fligno\StarterKit\Console\Commands;

use Fligno\StarterKit\Traits\UsesCommandCustomMessagesTrait;
use Illuminate\Console\Command;

/**
 * Class StarterKitGitHooksApplyCommand
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 */
class StarterKitGitHooksApplyCommand extends Command
{
    use UsesCommandCustomMessagesTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'sk:hooks:apply';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add Git Hooks to composer.json.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->ongoing('Applying Git Hooks...');

        if (! $contents = $this->getContentsFromComposerJson()) {
            return self::FAILURE;
        }

        // Append git hooks to composer.json
        $contents['extra']['hooks'] = starterKit()->getGitHooks();

        $contents['scripts']['cghooks'] = './vendor/bin/cghooks';

        collect([
            'post-update-cmd' => 'cghooks update',
            'post-install-cmd' => 'cghooks add --ignore-lock',
        ])->each(function ($command, $key) use (&$contents) {
            if (isset($contents['scripts'][$key])) {
                $target = $contents['scripts'][$key];

                if (is_array($target)) {
                    if (! array_search($command, $target)) {
                        $contents['scripts'][$key][] = $command;
                    }
                } elseif ($target !== $command) {
                    $contents['scripts'][$key][] = $target;
                    $contents['scripts'][$key][] = $command;
                }
            } else {
                $contents['scripts'][$key] = $command;
            }
        });

        $this->saveContentsToComposerJson($contents);

        $this->done('Applied Git Hooks.');

        return self::SUCCESS;
    }

    /**
     * @return string
     */
    public function getComposerJsonPath(): string
    {
        return base_path('composer.json');
    }

    /**
     * @return array|null
     */
    public function getContentsFromComposerJson(): ?array
    {
        if ($contents = getContentsFromComposerJson()) {
            return $contents->toArray();
        } else {
            $this->failed('Failed to load contents from composer.json file.');

            return null;
        }
    }

    /**
     * @param  array  $contents
     * @return void
     */
    public function saveContentsToComposerJson(array $contents): void
    {
        // Encode associative array to string (prevent escaped slashes)
        $encoded = json_encode($contents, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        // Save to actual composer.json
        file_put_contents($this->getComposerJsonPath(), $encoded);

        $process = make_process([
            base_path('vendor/bin/cghooks'),
            'update',
        ]);

        $process->setTimeout(30000)->run();
    }
}
