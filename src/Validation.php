<?php

namespace validation;

use http\Exception\InvalidArgumentException;
use validation\Custom\AbstractFixtureStrategy;
use validation\Custom\AbstractValidationStrategy;
use validation\Custom\ValidationStrategyInterface;
use validation\Exceptions\InvalidValidationStrategyException;
use validation\Exceptions\ValidationException;
use validation\stock\DateTime\StartAndEndDateStockStrategy;
use validation\stock\DateTime\ValidDateTimeStockStrategy;
use validation\stock\Integer\IntTypeStockStrategy;
use validation\stock\Range\RangeStockStrategy;
use validation\stock\String\MaxLengthStockStrategy;
use validation\stock\String\MinLengthStockStrategy;

class Validation
{
    /**
     * @var AbstractValidationStrategy[]
     */
    private $strategyList = [];

    /**
     * @var AbstractFixtureStrategy[]
     */
    private $fixtureList = [];

    /**
     * @var mixed
     */
    private $subject;

    /**
     * @var \Exception
     */
    private $exception;

    /**
     * Validation constructor.
     * @param $subject
     */
    public function __construct($subject)
    {
        $this->subject = $subject;
    }

    public function throws($exception)
    {
        $this->exception = $exception;

        return $this;
    }

    /**
     * @param AbstractValidationStrategy $validationStrategy
     * @return Validation
     * @throws InvalidValidationStrategyException
     */
    public function validateWith(AbstractValidationStrategy $validationStrategy)
    {
        if (!in_array(ValidationStrategyInterface::class, class_implements($validationStrategy))){
            throw new InvalidValidationStrategyException('Validation strategy is null or invalid');
        }
        $this->strategyList[] = $validationStrategy;
        $this->fixtureList[] = null;

        return $this;
    }

    /**
     * @param string $startDateProperty
     * @param string $endDateProperty
     * @param string $template
     * @return $this
     */
    public function validateStartEndEndDate(string $startDateProperty, string $endDateProperty, string $template = '')
    {
        if (is_null($startDateProperty) || $startDateProperty == '' || is_null($endDateProperty) || $endDateProperty == ''){
            throw new \InvalidArgumentException('Invalid date properties');
        }

        $this->strategyList[] = new StartAndEndDateStockStrategy($startDateProperty, $endDateProperty, $template);
        $this->fixtureList[] = null;

        return $this;
    }

    /**
     * @param string $dateTimeProperty
     * @param string $template
     * @return $this
     */
    public function validateDateTime(string $dateTimeProperty, string $template = '')
    {
        if (is_null($dateTimeProperty) || $dateTimeProperty == ''){
            throw new \InvalidArgumentException('Invalid date property for ValidateDateTime');
        }

        $this->strategyList[] = new ValidDateTimeStockStrategy($dateTimeProperty, $template);
        $this->fixtureList[] = null;

        return $this;
    }

    public function validateRange(string $property, int $min, int $max, string $template = '')
    {
        if (is_null($property) || $property == ''){
            throw new \InvalidArgumentException('Invalid property for ValidateRange');
        }

        $this->strategyList[] = new RangeStockStrategy($property, $min, $max, $template);
        $this->fixtureList[] = null;

        return $this;
    }

    public function validateIntType(string $property, string $template = '')
    {
        if (is_null($property) || $property == ''){
            throw new \InvalidArgumentException('Invalid property for ValidateIntType');
        }

        $this->strategyList[] = new IntTypeStockStrategy($property, $template);
        $this->fixtureList[] = null;

        return $this;
    }

    public function validateMaxLength(string $property, int $maxLength, string $template = '')
    {
        if (is_null($property) || $property == ''){
            throw new \InvalidArgumentException('Invalid property for ValidateMaxLength');
        }

        if ($maxLength == 0){
            throw new \InvalidArgumentException('MaxLength must be grater than zero.');
        }

        $this->strategyList[] = new MaxLengthStockStrategy($property, $maxLength, $template);
        $this->fixtureList[] = null;

        return $this;
    }

    public function validateMinLength(string $property, int $minLength, string $template = '')
    {
        if (is_null($property) || $property == ''){
            throw new \InvalidArgumentException('Invalid property for ValidateMinLength');
        }

        if ($minLength == 0){
            throw new \InvalidArgumentException('MinLength must be grater than zero.');
        }

        $this->strategyList[] = new MinLengthStockStrategy($property, $minLength, $template);
        $this->fixtureList[] = null;

        return $this;
    }

    /**
     * @param AbstractFixtureStrategy $fixtureStrategy
     * @return $this
     */
    public function fixWith(AbstractFixtureStrategy $fixtureStrategy)
    {
        $length = count($this->fixtureList);
        $this->fixtureList[$length-1] = $fixtureStrategy;
        return $this;
    }

    /**
     * @throws \Exception
     */
    public function run()
    {
        $errorList = [];

        $index = 0;
        foreach ($this->strategyList as $strategy) {
            try {
                $this->doValidation($strategy);
            } catch (\Exception $exception) {
                try {
                    $this->applyFixtureStrategy($exception, $index);
                } catch (\Exception $afterFixtureException){
                    $errorList[] = $afterFixtureException->getMessage();
                }
            }
            $index++;
        }

        if (count($errorList) > 0){
            if (empty($this->exception)) {
                throw new ValidationException(implode("\n", $errorList));
            }
            else {
                throw new $this->exception(implode("\n", $errorList));
            }
        }
    }

    private function doValidation(ValidationStrategyInterface $validationStrategy): void
    {
        $validationStrategy->setValue($this->subject);
        $validationStrategy->validate();
    }

    /**
     * @param \Exception $validationException
     * @param int $index
     * @throws \Exception
     */
    private function applyFixtureStrategy(\Exception $validationException, int $index): void
    {
        if (!is_null($this->fixtureList[$index])) {
            $fixtureStrategy = $this->fixtureList[$index];
                $fixtureStrategy->setValue($this->subject);
                $fixtureStrategy->fix();

                $this->strategyList[$index]->validate();
        } else{
            throw $validationException;
        }
    }

}