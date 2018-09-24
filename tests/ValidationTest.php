<?php

namespace Tests;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tests\Utils\MyExceptionClass;
use Tests\Utils\TestEntity;
use validation\Custom\AbstractFixtureStrategy;
use validation\Custom\AbstractValidationStrategy;
use validation\Exceptions\ValidationException;
use validation\stock\DateTime\StartAndEndDateStockStrategy;
use validation\ValidationFactory;

class ValidationTest extends TestCase
{
    private function prepareValidationStrategy(): MockObject
    {
        $validationStrategy = $this
            ->getMockBuilder(AbstractValidationStrategy::class)
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
            ->getMockBuilder(AbstractValidationStrategy::class)
            ->disableOriginalConstructor()
            ->getMock();

        $validationStrategy->method('validate')
            ->willThrowException($exception);

        return $validationStrategy;
    }

    private function prepareValidationStrategyWithExceptionOnFirstCall(\Exception $exception): MockObject
    {
        $validationStrategy = $this
            ->getMockBuilder(AbstractValidationStrategy::class)
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
            ->getMockBuilder(AbstractFixtureStrategy::class)
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
            ->getMockBuilder(AbstractFixtureStrategy::class)
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
            ->getMockBuilder(AbstractFixtureStrategy::class)
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
            ->getMockBuilder(AbstractValidationStrategy::class)
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
        $message = 'This message';
        $exceptionFromValidation = new \Exception($message);

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

        $this->assertContains($message, $exceptionResult->getMessage());
    }

    public function testWhenValidationWithFixtureStrategyIsMadeThenFixtureFailsMustThrowsExceptionFromFixture()
    {
        // arrange
        $messageFromValidation = 'From validation';
        $exceptionFromValidation = new \Exception($messageFromValidation);
        $validationStrategy = $this->prepareValidationStrategyWithException($exceptionFromValidation);

        $messageFromFixture = 'From fixture';
        $exceptionFromFixture = new \Exception($messageFromFixture);
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
        $this->assertContains($messageFromFixture, $exceptionResult->getMessage());
        $this->assertNotContains($messageFromValidation, $exceptionResult->getMessage());
    }

    public function testWhenValidationWithFixtureStrategyIsMadeThenValidationFailsAndFixtureDontWorkMustThrowsExceptionFromSecondValidationCall()
    {
        // arrange
        $firstExceptionMessage = 'First exception';
        $firstExceptionFromValidation = new \Exception($firstExceptionMessage);
        $secondExceptionMessage = 'Second exception';
        $secondExceptionFromValidation = new \Exception($secondExceptionMessage);
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
        $this->assertContains($secondExceptionMessage, $exceptionResult->getMessage());
        $this->assertNotContains($firstExceptionMessage, $exceptionResult->getMessage());
        //$this->assertSame($secondExceptionFromValidation, $exceptionResult);
    }

    /**
     *
     */
    public function testValidationWithTwoStrategiesThrowingOneExceptionForEach()
    {
        // arrange

        $firstExceptionMessage = 'First exception';
        $secondExceptionMessage = 'Second exception';

        $firstException = new \Exception($firstExceptionMessage);
        $firstStrategy = $this->prepareValidationStrategyWithException($firstException);

        $secondException = new \Exception($secondExceptionMessage);
        $secondStrategy = $this->prepareValidationStrategyWithException($secondException);

        $subject = new TestEntity();
        $factory = new ValidationFactory();
        $exceptionResult = null;

        // act
        try {
            $factory->createFor($subject)
                ->validateWith($firstStrategy)
                ->validateWith($secondStrategy)
                ->run();
        } catch (\Exception $ex){
            $exceptionResult = $ex;
        }

        // assert
        $this->assertContains($firstExceptionMessage, $exceptionResult->getMessage());
        $this->assertContains($secondExceptionMessage, $exceptionResult->getMessage());
        var_dump($exceptionResult->getMessage());
    }

    public function testIfValidationFailsAnThrowsACustomExceptionPassedByThrowsMethod()
    {
        // arrange
        $firstExceptionMessage = 'Custom exception';

        $firstException = new MyExceptionClass($firstExceptionMessage);
        $firstStrategy = $this->prepareValidationStrategyWithException($firstException);

        $subject = new TestEntity();
        $factory = new ValidationFactory();
        $exceptionResult = null;

        // act
        try {
            $factory->createFor($subject)
                ->validateWith($firstStrategy)
                ->throws(MyExceptionClass::class)
                ->run();
        } catch (\Exception $ex){
            $exceptionResult = $ex;
        }

        // assert
        $this->assertContains($firstExceptionMessage, $exceptionResult->getMessage());
    }

    public function testIfValidateStartAndEndDateStockStrategyHasInvalidParametersThrowsInvalidArgumentException()
    {
        // arrange
        $this->expectException(\InvalidArgumentException::class);
        $subject = new TestEntity();
        $factory = new ValidationFactory();


        // act
        $factory->createFor($subject)
            ->validateStartEndEndDate('', 'endDate', 'Test Message')
            ->run();

        // assert
    }

    public function testIfValidateStartAndEndDateStockStrategyWithWrongDatesThrowsValidationException()
    {
        // arrange
        $this->expectException(ValidationException::class);
        $subject = new TestEntity();
        $factory = new ValidationFactory();

        $subject->setStartDate('2018-09-23 12:00:00');
        $subject->setEndDate('2018-09-23 11:00:00');

        // act
        $factory->createFor($subject)
            ->validateStartEndEndDate('startDate', 'endDate', 'Test Message')
            ->run();

        // assert
    }

    public function testIfValidateStartAndEndDateStockStrategyWithWrongDatesThrowsValidationExceptionWithCustomMessage()
    {
        // arrange
        $customMessage = 'Test Message';
        $subject = new TestEntity();
        $factory = new ValidationFactory();
        $exceptionResult = null;

        $subject->setStartDate('2018-09-23 12:00:00');
        $subject->setEndDate('2018-09-23 11:00:00');

        // act
        try {
            $factory->createFor($subject)
                ->validateStartEndEndDate('startDate', 'endDate', $customMessage)
                ->run();
        }
        catch (\Exception $ex) {
            $exceptionResult = $ex;
        }

        // assert
        $this->assertEquals($customMessage, $exceptionResult->getMessage());
    }

    public function testIfValidateStartAndEndDateStockStrategyWithWrongDatesThrowsValidationExceptionWithoutCustomMessage()
    {
        // arrange
        $customMessage = 'Test Message';
        $subject = new TestEntity();
        $factory = new ValidationFactory();
        $exceptionResult = null;

        $subject->setStartDate('2018-09-23 12:00:00');
        $subject->setEndDate('2018-09-23 11:00:00');

        // act
        try {
            $factory->createFor($subject)
                ->validateStartEndEndDate('startDate', 'endDate')
                ->run();
        }
        catch (\Exception $ex) {
            $exceptionResult = $ex;
        }

        // assert
        $this->assertNotEquals($customMessage, $exceptionResult->getMessage());
    }


    public function testIfValidateDateTimeWithInvalidParametersThrowsInvalidArgumentException()
    {
        // arrange
        $this->expectException(\InvalidArgumentException::class);
        $subject = new TestEntity();
        $factory = new ValidationFactory();


        // act
        $factory->createFor($subject)
            ->validateDateTime('', 'Test Message')
            ->run();

        // assert
    }

    /**
     * @throws \Exception
     */
    public function testIfValidateDateTimeWithInvalidDateThrowsValidationException()
    {
        // arrange
        $this->expectException(ValidationException::class);
        $subject = new TestEntity();
        $factory = new ValidationFactory();

        $subject->setStartDate('-09-23 12:00:00');

        // act
        $factory->createFor($subject)
            ->validateDateTime('startDate','Test Message')
            ->run();

        // assert
    }

    public function testIfValidateDateTimeWithWrongDateThrowsValidationExceptionWithCustomMessage()
    {
        // arrange
        $customMessage = 'Test Message';
        $subject = new TestEntity();
        $factory = new ValidationFactory();
        $exceptionResult = null;

        $subject->setStartDate('-09-23 12:00:00');

        // act
        try {
            $factory->createFor($subject)
                ->validateDateTime('startDate', $customMessage)
                ->run();
        }
        catch (\Exception $ex) {
            $exceptionResult = $ex;
        }

        // assert
        $this->assertEquals($customMessage, $exceptionResult->getMessage());
    }

    public function testIfValidateDateTimeWithWrongDateThrowsValidationExceptionWithoutCustomMessage()
    {
        // arrange
        $customMessage = 'Test Message';
        $subject = new TestEntity();
        $factory = new ValidationFactory();
        $exceptionResult = null;

        $subject->setStartDate('-23 12:00:00');

        // act
        try {
            $factory->createFor($subject)
                ->validateDateTime('startDate')
                ->run();
        }
        catch (\Exception $ex) {
            $exceptionResult = $ex;
        }

        // assert
        $this->assertNotEquals($customMessage, $exceptionResult->getMessage());
    }

    public function testIfValidateRangeWithInvalidPropertyParameterThrowsInvalidArgumentException()
    {
        // arrange
        $this->expectException(\InvalidArgumentException::class);
        $subject = new TestEntity();
        $factory = new ValidationFactory();


        // act
        $factory->createFor($subject)
            ->validateRange('', 0, 100, 'Test Message')
            ->run();

        // assert
    }

    public function testIfValidateRangeWithMinValueGraterThanMaxValueThrowsValidationException()
    {
        // arrange
        $this->expectException(ValidationException::class);
        $subject = new TestEntity();
        $factory = new ValidationFactory();

        // act
        $factory->createFor($subject)
            ->validateRange('someIntValue', 1000, 100, 'Test Message')
            ->run();

        // assert
    }

    public function testIfValidateMaxLengthWithInvalidPropertyParameterThrowsInvalidArgumentException()
    {
        // arrange
        $this->expectException(\InvalidArgumentException::class);
        $subject = new TestEntity();
        $factory = new ValidationFactory();

        // act
        $factory->createFor($subject)
            ->validateMaxLength('', 3)
            ->run();

        // assert
    }

    public function testIfValidateMaxLengthWithInvalidMaxLengthParameterThrowsInvalidArgumentException()
    {
        // arrange
        $this->expectException(\InvalidArgumentException::class);
        $subject = new TestEntity();
        $factory = new ValidationFactory();

        // act
        $factory->createFor($subject)
            ->validateMaxLength('name', 0)
            ->run();

        // assert
    }

    public function testIfValidateMaxLengthWithStringThatExceedsMaxLengthParameterThrowsValidationException()
    {
        // arrange
        $this->expectException(ValidationException::class);
        $subject = new TestEntity();
        $factory = new ValidationFactory();
        $subject->setName('Nome maior que 5');

        // act
        $factory->createFor($subject)
            ->validateMaxLength('name', 5)
            ->run();

        // assert
    }

    public function testIfValidateMinLengthWithInvalidPropertyParameterThrowsInvalidArgumentException()
    {
        // arrange
        $this->expectException(\InvalidArgumentException::class);
        $subject = new TestEntity();
        $factory = new ValidationFactory();

        // act
        $factory->createFor($subject)
            ->validateMinLength('', 3)
            ->run();

        // assert
    }

    public function testIfValidateMinLengthWithInvalidMaxLengthParameterThrowsInvalidArgumentException()
    {
        // arrange
        $this->expectException(\InvalidArgumentException::class);
        $subject = new TestEntity();
        $factory = new ValidationFactory();

        // act
        $factory->createFor($subject)
            ->validateMinLength('name', 0)
            ->run();

        // assert
    }

    public function testIfValidateMinLengthWithStringThatExceedsMaxLengthParameterThrowsValidationException()
    {
        // arrange
        $this->expectException(ValidationException::class);
        $subject = new TestEntity();
        $factory = new ValidationFactory();
        $subject->setName('Menor que 15');

        // act
        $factory->createFor($subject)
            ->validateMinLength('name', 15)
            ->run();

        // assert
    }

    public function testIfValidateIntTypeWithInvalidPropertyParameterThrowsInvalidArgumentException()
    {
        // arrange
        $this->expectException(\InvalidArgumentException::class);
        $subject = new TestEntity();
        $factory = new ValidationFactory();

        // act
        $factory->createFor($subject)
            ->validateIntType('')
            ->run();

        // assert
    }

    public function testIfValidateIntTypeWithInvalidValueThrowsValidationException()
    {
        // arrange
        $this->expectException(ValidationException::class);
        $subject = new TestEntity();
        $factory = new ValidationFactory();
        $subject->setId('a');

        // act
        $factory->createFor($subject)
            ->validateIntType('id')
            ->run();

        // assert
    }

    public function testIfValidateNotBlankWithInvalidPropertyParameterThrowsInvalidArgumentException()
    {
        // arrange
        $this->expectException(\InvalidArgumentException::class);
        $subject = new TestEntity();
        $factory = new ValidationFactory();

        // act
        $factory->createFor($subject)
            ->validateNotBlank('')
            ->run();

        // assert
    }

    public function testIfValidateNotBlankWithInvalidValueThrowsValidationException()
    {
        // arrange
        $this->expectException(ValidationException::class);
        $subject = new TestEntity();
        $factory = new ValidationFactory();

        // act
        $factory->createFor($subject)
            ->validateNotBlank('name')
            ->run();

        // assert
    }
}