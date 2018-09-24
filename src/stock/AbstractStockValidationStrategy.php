<?php

namespace validation\stock;

use validation\Custom\ValidationStrategyInterface;

abstract class AbstractStockValidationStrategy implements ValidationStrategyInterface
{
    protected $value;

    public function setValue($value)
    {
        $this->value = $value;
    }

    public abstract function validate();
}