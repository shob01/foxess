<?php declare(strict_types=1);

namespace Foxess\Formatter;

/**
 * Interface for formatting Foxess report or raw data tables
 */
interface IDataFormatter
{
    /**
     * Transforms the given data into an output string
     *
     * @param array $data   data array returned from either CloudApi class methods
     *                      getReport() or getRaw()
     * @return string       returns the given data table transformed into a string representation
     */
    public function transform(array $data): string;
}