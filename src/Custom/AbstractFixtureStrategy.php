<?php

namespace validation\Custom;


abstract class AbstractFixtureStrategy implements FixtureStrategyInterface
{
    protected $value;

    public function setValue($value)
    {
        $this->value = $value;
    }

    public abstract function fix();
}