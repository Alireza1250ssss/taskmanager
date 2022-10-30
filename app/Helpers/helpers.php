<?php

use Illuminate\Pagination\LengthAwarePaginator;

function getFloatBetween(float $num1, float $num2): float
{
    $maxDecimalsCount = (int)max(strpos(strrev($num1), '.'), strpos(strrev($num2), '.'));
    $distance = ($num2 - $num1) * pow(10, $maxDecimalsCount);
    if ($distance == 1)
        $maxDecimalsCount++;
    elseif ($distance == 2)
        return ($num1 * pow(10, $maxDecimalsCount) + 1) / pow(10, $maxDecimalsCount);
    $exponent = pow(10, $maxDecimalsCount);
    $min = ($num1 * $exponent + 1);
    $max = ($num2 * $exponent - 1);
    return rand($min, $max) / $exponent;
}

function execPrint($command)
{

    $result = array();
    exec($command, $result);
    print("<pre>");
    foreach ($result as $line) {
        print($line . "\n");
    }
    print("</pre>");
}

function cleanCollection(LengthAwarePaginator &$paginator)
{
    $res = $paginator->getCollection()->filter(fn($item, $key) => !empty($item->getAttributes()))->values();
    $paginator->setCollection($res);
}

function accessViewFilter(LengthAwarePaginator &$paginator)
{
    $paginator->getCollection()->map(fn($item, $key) => $item->touchRetrieved());
}
