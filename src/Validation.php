<?php

namespace validation;


use validation\Custom\FixtureStrategyInterface;
use validation\Custom\ValidationStrategyInterface;

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
     */
    public function validateWith(ValidationStrategyInterface $validationStrategy)
    {
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
            $this->applyFixtureStrategy();
        }
    }

    private function doValidation(): void
    {
        $this->validationStrategy->setValue($this->subject);
        $this->validationStrategy->validate();
    }

    /**
     * @throws \Exception
     */
    private function applyFixtureStrategy(): void
    {
        if (!is_null($this->fixtureStrategy)) {
            try {
                $this->fixtureStrategy->setValue($this->subject);
                $this->fixtureStrategy->fix();
            } catch (\Exception $exception) {
                throw $exception;
            }
        }
    }

}