<?php

namespace Tests\AppBundle\Util;

use AppBundle\Util\Calculator;
use PHPUnit\Framework\TestCase;

class CalculatorTest extends TestCase
{   
    public function testAdd()
    {
        $calc = new Calculator();
        $result = $calc->add(30, 12);
        $this->assertEquals(42, $result);
    }
}