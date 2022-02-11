<?php

namespace Fligno\StarterKit\Traits;

/**
 * Trait UsesCommandCustomMessagesTrait
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 */
trait UsesCommandCustomMessagesTrait
{
    /**
     * @param string $message
     * @param int|string|null $verbosity
     * @return void
     */
    public function ongoing(string $message, int|string  $verbosity = null): void
    {
        $this->note($message, 'ONGOING', $verbosity);
    }

    /**
     * @param string $message
     * @param int|string|null $verbosity
     * @return void
     */
    public function done(string $message, int|string  $verbosity = null): void
    {
        $this->note($message, 'DONE', $verbosity);
    }

    /**
     * @param string $message
     * @param int|string|null $verbosity
     * @return void
     */
    public function failed(string $message, int|string  $verbosity = null): void
    {
        $this->error('<fg=white;bg=red>[ ERROR ]</> ' . $message, $verbosity);
    }

    /**
     * @param string $message
     * @param string $title
     * @param int|string|null $verbosity
     * @return void
     */
    public function note(string $message, string $title = 'INFO', int|string  $verbosity = null): void
    {
        $this->info('<fg=white;bg=green>[ ' . $title . ' ]</> ' . $message, $verbosity);
    }
}
