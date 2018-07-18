<?php


namespace Tests;

use PHPUnit\Framework\TestCase;
use validation\Custom\ValidationStrategyInterface;

class ValidationTest extends TestCase
{

    public function testValidationRun()
    {
        // arrange
        $validationStrategy = $this
            ->getMockBuilder(ValidationStrategyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        //$validationStrategy->method('validate')


        // act

        // assert
    }

}