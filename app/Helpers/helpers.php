<?php

function getFloatBetween(float $num1 , float $num2) : float
{
    $maxDecimalsCount = (int)max(strpos(strrev($num1) , '.'),strpos(strrev($num2) , '.'));
    $distance = ($num2 - $num1) * pow(10,$maxDecimalsCount);
    if ($distance == 1)
        $maxDecimalsCount++;
    elseif ($distance == 2)
        return ($num1 * pow(10,$maxDecimalsCount) + 1) / pow(10,$maxDecimalsCount);
    $exponent = pow(10,$maxDecimalsCount);
    $min = ($num1 * $exponent + 1);
    $max = ($num2 * $exponent - 1);
    return rand($min,$max)/$exponent;
}
