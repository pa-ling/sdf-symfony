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
        echo 'CalculatorTest';        
        // assert that your calculator added the numbers correctly!
        $this->assertEquals(42, $result);
    }
}