<?php

namespace Tests;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tests\Utils\TestEntity;
use validation\Custom\FixtureStrategyInterface;
use validation\Custom\ValidationStrategyInterface;
use validation\ValidationFactory;

class ValidationTest extends TestCase
{
    private function prepareValidationStrategy(): MockObject
    {
        $validationStrategy = $this
            ->getMockBuilder(ValidationStrategyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $validationStrategy
            ->expects($this->atLeastOnce())
            ->method('validate');

        return $validationStrategy;
    }

    /**
     * @param \Exception $exception
     * @return MockObject
     */
    private function prepareValidationStrategyWithException(\Exception $exception): MockObject
    {
        $validationStrategy = $this
            ->getMockBuilder(ValidationStrategyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $validationStrategy->method('validate')
            ->willThrowException($exception);

        return $validationStrategy;
    }

    private function prepareValidationStrategyWithExceptionOnFirstCall(\Exception $exception): MockObject
    {
        $validationStrategy = $this
            ->getMockBuilder(ValidationStrategyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $validationStrategy
            ->expects($this->exactly(2))
            ->method('validate')
            ->will($this->onConsecutiveCalls($this->throwException($exception), null));

        return $validationStrategy;
    }

    /**
     * @param \Exception $exception
     * @return MockObject
     */
    private function prepareFixtureStrategyWithException(\Exception $exception): MockObject
    {
        $fixtureStrategy = $this
            ->getMockBuilder(FixtureStrategyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $fixtureStrategy
            ->method('fix')
            ->willThrowException($exception);

        return $fixtureStrategy;
    }

    private function prepareFixtureStrategy(): MockObject
    {
        $fixtureStrategy = $this
            ->getMockBuilder(FixtureStrategyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $fixtureStrategy
            ->expects($this->once())
            ->method('fix');

        return $fixtureStrategy;
    }

    private function prepareFixtureStrategyWithoutException(): MockObject
    {
        $fixtureStrategy = $this
            ->getMockBuilder(FixtureStrategyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $fixtureStrategy
            ->method('fix');

        return $fixtureStrategy;
    }

    /**
     * @param \Exception $firstException
     * @param \Exception $secondException
     * @return MockObject
     */
    private function prepareValidationStrategyWithTwoExecutionsAndOneExceptionForEach(\Exception $firstException, \Exception $secondException): MockObject
    {
        $validationStrategy = $this
            ->getMockBuilder(ValidationStrategyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $validationStrategy
            ->expects($this->any())
            ->method('validate')
            ->willThrowException($firstException);

        $validationStrategy
            ->expects($this->any())
            ->method('validate')
            ->willThrowException($secondException);

        return $validationStrategy;
    }

    /**
     * @throws \validation\Exceptions\InvalidValidationStrategyException
     * @throws \Exception
     */
    public function testWhenValidationRunsCallValidateMethodFromValidationStrategy()
    {
        // arrange
        $validationStrategy = $this->prepareValidationStrategy();
        $subject = new TestEntity();
        $factory = new ValidationFactory();

        // act
        $factory->createFor($subject)
            ->validateWith($validationStrategy)
            ->run();

        // assert
    }

    /**
     * @throws \validation\Exceptions\InvalidValidationStrategyException
     * @throws \Exception
     */
    public function testWhenValidationFailThenCallFixMethodFromFixtureStrategy()
    {
        // arrange
        $validationException = new \Exception();
        $validationStrategy = $this->prepareValidationStrategyWithExceptionOnFirstCall($validationException);

        $fixtureStrategy = $this->prepareFixtureStrategy();

        $subject = new TestEntity();
        $factory = new ValidationFactory();

        // act
        $factory
            ->createFor($subject)
            ->validateWith($validationStrategy)
            ->fixWith($fixtureStrategy)
            ->run();

        // assert
    }

    /**
     * @throws \Exception
     */
    public function testWhenValidationWithoutFixtureStrategyIsMadeThenFailsMustThrowsExceptionFromValidation()
    {
        // arrange
        $exceptionFromValidation = new \Exception();

        $validationStrategy = $this->prepareValidationStrategyWithException($exceptionFromValidation);

        $subject = new TestEntity();

        $factory = new ValidationFactory();

        $exceptionResult = null;

        // act
        try {
            $factory->createFor($subject)
                ->validateWith($validationStrategy)
                ->run();
        } catch (\Exception $ex){
            $exceptionResult = $ex;
        }

        // assert
        $this->assertSame($exceptionFromValidation, $exceptionResult);
    }

    public function testWhenValidationWithFixtureStrategyIsMadeThenFixtureFailsMustThrowsExceptionFromFixture()
    {
        // arrange
        $exceptionFromValidation = new \Exception();
        $validationStrategy = $this->prepareValidationStrategyWithException($exceptionFromValidation);

        $exceptionFromFixture = new \Exception();
        $fixtureStrategy = $this->prepareFixtureStrategyWithException($exceptionFromFixture);

        $subject = new TestEntity();

        $factory = new ValidationFactory();

        $exceptionResult = null;

        // act
        try {
            $factory->createFor($subject)
                ->validateWith($validationStrategy)
                ->fixWith($fixtureStrategy)
                ->run();
        } catch (\Exception $ex){
            $exceptionResult = $ex;
        }

        // assert
        $this->assertSame($exceptionFromFixture, $exceptionResult);
    }

    public function testWhenValidationWithFixtureStrategyIsMadeThenValidationFailsAndFixtureDontWorkMustThrowsExceptionFromSecondValidation()
    {
        // arrange
        $firstExceptionFromValidation = new \Exception('First exception');
        $secondExceptionFromValidation = new \Exception('Second exception');
        $validationStrategy = $this->prepareValidationStrategyWithTwoExecutionsAndOneExceptionForEach($firstExceptionFromValidation, $secondExceptionFromValidation);

        $fixtureStrategy = $this->prepareFixtureStrategyWithoutException();

        $subject = new TestEntity();

        $factory = new ValidationFactory();

        $exceptionResult = null;

        // act
        try {
            $factory->createFor($subject)
                ->validateWith($validationStrategy)
                ->fixWith($fixtureStrategy)
                ->run();
        } catch (\Exception $ex){
            $exceptionResult = $ex;
        }

        // assert
        $this->assertSame($secondExceptionFromValidation, $exceptionResult);
    }
}