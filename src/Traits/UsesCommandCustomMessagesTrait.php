<?php

namespace Fligno\StarterKit\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableCellStyle;
use Symfony\Component\Console\Helper\TableStyle;

/**
 * Trait UsesCommandCustomMessagesTrait
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 */
trait UsesCommandCustomMessagesTrait
{
    /**
     * @param  string  $message
     * @param  bool  $add_ellipsis
     * @param  int|string|null  $verbosity
     * @return void
     */
    public function ongoing(string $message, bool $add_ellipsis = true, int|string $verbosity = null): void
    {
        $message = $message.($add_ellipsis ? '...' : null);
        $this->note(message: $message, title: 'ONGOING', verbosity: $verbosity);
    }

    /**
     * @param  string  $message
     * @param  int|string|null  $verbosity
     * @return void
     */
    public function done(string $message, int|string $verbosity = null): void
    {
        $this->note(message: $message, title: 'DONE', verbosity: $verbosity);
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
     * @param  bool  $add_period
     * @param  int|string|null  $verbosity
     * @return void
     */
    public function note(string $message, string $title = 'INFO', bool $add_period = true, int|string $verbosity = null): void
    {
        $this->setupOutputFormatters();
        $message = "<green-bg-bold>[ $title ]</green-bg-bold> $message";
        $this->info(Str::of($message)->when($add_period, fn (Stringable $str) => $str->finish('.'))->jsonSerialize(), $verbosity);
    }

    /**
     * @return void
     */
    private function setupOutputFormatters(): void
    {
        $colors = [
            'black',
            'red',
            'green',
            'yellow',
            'blue',
            'magenta',
            'cyan',
            'white',
            'default',
        ];

        foreach ($colors as $color) {
            $this->output->getFormatter()->setStyle($color.'-bg-bold', new OutputFormatterStyle('default', $color, ['bold']));
            $this->output->getFormatter()->setStyle($color.'-bg-bold-blink', new OutputFormatterStyle('default', $color, ['bold', 'blink']));
            $this->output->getFormatter()->setStyle($color.'-bold', new OutputFormatterStyle($color, null, ['bold']));
            $this->output->getFormatter()->setStyle($color.'-bold-blink', new OutputFormatterStyle($color, null, ['bold', 'blink']));
        }

        $this->output->getFormatter()->setStyle('blink-icon', new OutputFormatterStyle(options: ['blink']));
    }

    /**
     * @param  string|null  $str
     * @param  string  $color
     * @return string
     */
    public function getBoldText(string $str = null, string $color = 'green'): string
    {
        return "<$color-bold>$str</$color-bold>";
    }

    /**
     * @link https://symfony.com/doc/current/components/console/helpers/table.html
     *
     * @param  string|null  $title
     * @param  array  $headers
     * @param  Collection|array  $rows
     * @param  string  $title_format
     * @param  TableStyle|string  $table_style
     * @return Table|null
     */
    public function createTable(string $title = null, array $headers = [], Collection|array $rows = [], string $title_format = 'default-bold', TableStyle|string $table_style = 'box'): Table|null
    {
        $rows = collect($rows);

        if ($rows->count()) {
            // Create a new Table instance.
            $table = new Table($this->output);

            // Set the contents of the table.
            if ($title) {
                $cell = $this->createTableCell(Str::upper($title), $title_format, count($headers));
                $headers = [[$cell], $headers];
            }

            $table->setHeaders($headers);
            $table->setRows($rows->toArray());

            // Render the table to the output.
            return $table->setStyle($table_style);
        }

        return null;
    }

    /**
     * @param  string  $text
     * @param  string  $text_format
     * @param  int  $colspan
     * @return TableCell
     */
    public function createTableCell(string $text, string $text_format = 'green-bold', int $colspan = 1): TableCell
    {
        return new TableCell($text, [
            'colspan' => $colspan,
            'style' => new TableCellStyle([
                'align' => 'center',
                'cellFormat' => "<$text_format>%s</$text_format>",
            ]),
        ]);
    }
}
