<?php declare(strict_types=1);

namespace Foxess\Formatter;

class HtmlTableDataFormatter extends AbstractDataFormatter
{
    public function begin(): string
    {
        return '<table class="fs-table">' . PHP_EOL;
    }
    public function beginHeader(): string
    {
        return '<thead>' . PHP_EOL;
    }
    public function endHeader(): string
    {
        return '</thead><tbody>' . PHP_EOL;
    }
    public function headField($value): string
    {
        return "<th>$value</th>" . PHP_EOL;
    }
    public function beginLine(): string
    {
        return '<tr>' . PHP_EOL;
    }
    public function endLine(): string
    {
        return '</tr>' . PHP_EOL;
    }
    public function field(string $valueStr,string $type="text"): string
    {
        return '<td class="'.$type.'">'.$valueStr.'</td>' . PHP_EOL;
    }
    public function fieldSeperator(): string
    {
        //not applicable here
        return '';
    }
    public function end(): string
    {
        return '</tbody></table>' . PHP_EOL;
    }
}