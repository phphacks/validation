<?php

namespace validation\Custom;


abstract class AbstractValidationStrategy implements ValidationStrategyInterface
{
    protected $value;

    public function setValue($value)
    {
        $this->value = $value;
    }

    public abstract function validate();

}