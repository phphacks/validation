<?php

namespace validation;


use validation\Custom\FixtureStrategyInterface;
use validation\Custom\ValidationStrategyInterface;
use validation\Exceptions\InvalidValidationStrategyException;

class Validation
{
    /**
     * @var mixed
     */
    private $subject;

    /**
     * @var ValidationStrategyInterface
     */
    private $validationStrategy;

    /**
     * @var FixtureStrategyInterface
     */
    private $fixtureStrategy;

    /**
     * Validation constructor.
     * @param $subject
     */
    public function __construct($subject)
    {
        $this->subject = $subject;
    }

    /**
     * @param ValidationStrategyInterface $validationStrategy
     * @return Validation
     * @throws InvalidValidationStrategyException
     */
    public function validateWith(ValidationStrategyInterface $validationStrategy)
    {
        if (!in_array(ValidationStrategyInterface::class, class_implements($validationStrategy))){
            throw new InvalidValidationStrategyException('Validation strategy is null or invalid');
        }
        $this->validationStrategy = $validationStrategy;
        return $this;
    }

    /**
     * @param FixtureStrategyInterface $fixtureStrategy
     * @return $this
     */
    public function fixWith(FixtureStrategyInterface $fixtureStrategy)
    {
        $this->fixtureStrategy = $fixtureStrategy;
        return $this;
    }

    /**
     * @throws \Exception
     */
    public function run()
    {
        try {
            $this->doValidation();
        } catch (\Exception $exception){
            $this->applyFixtureStrategy($exception);
        }
    }

    private function doValidation(): void
    {
        $this->validationStrategy->setValue($this->subject);
        $this->validationStrategy->validate();
    }

    /**
     * @param \Exception $validationException
     * @throws \Exception
     */
    private function applyFixtureStrategy(\Exception $validationException): void
    {
        if (!is_null($this->fixtureStrategy)) {

                $this->fixtureStrategy->setValue($this->subject);
                $this->fixtureStrategy->fix();

                $this->validationStrategy->validate();
        } else{
            throw $validationException;
        }
    }

}