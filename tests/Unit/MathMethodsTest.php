<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class MathMethodsTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @param float $num1
     * @param float $num2
     * @return void
     * @dataProvider floatProvider
     */
    public function test_get_float_between_accuracy(float $num1 ,float $num2)
    {
        $num2 = max($num2,$num1);
        $num1 = min($num2,$num1);
        $betweenNumber = getFloatBetween($num1,$num2);
        $comparison = $betweenNumber < $num2 && $betweenNumber > $num1;
        $this->assertTrue($comparison);
    }

    public function floatProvider(): array
    {
        return [
          'three_decimals' => [2.4446 , 2.445]  ,
          'big_difference' => [1000 , 2000] ,

        ];
    }
}
