<?php

namespace Fligno\StarterKit\Traits;

use Illuminate\Support\Str;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

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
        $this->setupOutputFormatters();
        $this->error(Str::finish('<red-bg-bold>[ ERROR ]</red-bg-bold> '.$message, '.'), $verbosity);
    }

    /**
     * @param  string  $message
     * @param  int|string|null  $verbosity
     * @return void
     */
    public function warning(string $message, int|string $verbosity = null): void
    {
        $this->setupOutputFormatters();
        $this->warn(Str::finish('<yellow-bg-bold>[ WARNING ]</yellow-bg-bold> '.$message, '.'), $verbosity);
    }

    /**
     * @param  string  $message
     * @param  string  $title
     * @param  int|string|null  $verbosity
     * @return void
     */
    public function note(string $message, string $title = 'INFO', int|string $verbosity = null): void
    {
        $this->setupOutputFormatters();
        $this->info(Str::finish('<green-bg-bold>[ '.$title.' ]</green-bg-bold> '.$message, '.'), $verbosity);
    }

    /**
     * @return void
     */
    private function setupOutputFormatters(): void
    {
        $colors = ['green', 'yellow', 'red', 'white'];

        foreach ($colors as $color) {
            $this->output->getFormatter()->setStyle($color.'-bg-bold', new OutputFormatterStyle('white', $color, ['bold']));
            $this->output->getFormatter()->setStyle($color.'-bold', new OutputFormatterStyle($color, null, ['bold']));
        }

        $this->output->getFormatter()->setStyle('blink-icon', new OutputFormatterStyle(options: ['blink']));
    }
}
