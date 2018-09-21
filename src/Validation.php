<?php

namespace validation;

use validation\Custom\AbstractFixtureStrategy;
use validation\Custom\AbstractValidationStrategy;
use validation\Custom\ValidationStrategyInterface;
use validation\Exceptions\InvalidValidationStrategyException;
use validation\Exceptions\ValidationException;

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