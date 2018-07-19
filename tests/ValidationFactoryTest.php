<?php

namespace Tests;


use PHPUnit\Framework\TestCase;
use validation\ValidationFactory;

class ValidationFactoryTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateForWithInvalidParameter()
    {
        // arrange
        $subject = null;
        $validationFactory = new ValidationFactory();

        // act
        $validationFactory->createFor($subject);

        // assert
    }

}