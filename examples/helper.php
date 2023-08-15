<?php declare(strict_types=1);

use Foxess\Formatter\HtmlTableDataFormatter;
use Foxess\Formatter\CsvDataFormatter;

function outputJson($title, $data)
{
    echo "<h2>$title</h2>";
    echo "<pre>" . json_encode($data, JSON_PRETTY_PRINT) . "</pre>";
}
function outputHtml($title, $data, $totalColumn)
{
    echo "<h2>$title</h2>";
    $formatter = new HtmlTableDataFormatter();
    echo $formatter->transform($data, $totalColumn);
}
function outputCsv($title, $data)
{
    echo "<h2>$title</h2>";
    $formatter = new CsvDataFormatter();
    echo "<pre>".$formatter->transform($data)."</pre>";
}

?>


