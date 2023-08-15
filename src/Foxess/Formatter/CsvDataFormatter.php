<?php

declare(strict_types=1);

namespace Foxess\Formatter;

class CsvDataFormatter extends AbstractDataFormatter
{
    public function __construct(
        protected $sepStr = ';',
        protected $quoteText = true,
        $decimalSep = ",",
        $thousandsSep = ""
    ) {
        parent::__construct($decimalSep, $thousandsSep);
    }
    public function begin(): string
    {
        return '';
    }
    public function beginHeader(): string
    {
        return '';
    }
    public function endHeader(): string
    {
        return '';
    }
    public function headField($value): string
    {
        return $this->quoteText ? '"' . $value . '"' : $value;
    }
    public function field(string $valueStr, string $type = "text"): string
    {
        switch ($type) {
            case 'number':
                $output = $valueStr;
                break;
            default:
                $output = $this->quoteText ? '"' . $valueStr . '"' : $valueStr;
        }
        return $output;
    }
    public function fieldSeperator(): string
    {
        return $this->sepStr;
    }
    public function beginLine(): string
    {
        return '';
    }
    public function endLine(): string
    {
        return PHP_EOL;
    }
    public function end(): string
    {
        return '';
    }
}
