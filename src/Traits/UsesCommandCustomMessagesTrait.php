<?php

namespace Fligno\StarterKit\Traits;

use Illuminate\Support\Str;

/**
 * Trait UsesCommandCustomMessagesTrait
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 */
trait UsesCommandCustomMessagesTrait
{
    /**
     * @param  string  $message
     * @param  bool  $prepend_ellipsis
     * @param  int|string|null  $verbosity
     * @return void
     */
    public function ongoing(string $message, bool $prepend_ellipsis = true, int|string $verbosity = null): void
    {
        $message = $message.($prepend_ellipsis ? '...' : null);
        $this->note($message, 'ONGOING', $verbosity);
    }

    /**
     * @param  string  $message
     * @param  int|string|null  $verbosity
     * @return void
     */
    public function done(string $message, int|string $verbosity = null): void
    {
        $this->note($message, 'DONE', $verbosity);
    }

    /**
     * @param  string  $message
     * @param  int|string|null  $verbosity
     * @return void
     */
    public function failed(string $message, int|string $verbosity = null): void
    {
        $this->error(Str::finish('<fg=white;bg=red>[ ERROR ]</> '.$message, '.'), $verbosity);
    }

    /**
     * @param  string  $message
     * @param  int|string|null  $verbosity
     * @return void
     */
    public function warning(string $message, int|string $verbosity = null): void
    {
        $this->warn(Str::finish('<fg=white;bg=yellow>[ WARNING ]</> '.$message, '.'), $verbosity);
    }

    /**
     * @param  string  $message
     * @param  string  $title
     * @param  int|string|null  $verbosity
     * @return void
     */
    public function note(string $message, string $title = 'INFO', int|string $verbosity = null): void
    {
        $this->info(Str::finish('<fg=white;bg=green>[ '.$title.' ]</> '.$message, '.'), $verbosity);
    }
}
