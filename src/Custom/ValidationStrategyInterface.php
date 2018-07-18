<?php

namespace validation\Custom;


interface ValidationStrategyInterface
{
    public function setValue($value);
    public function getValue();
    public function validate();
}