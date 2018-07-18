<?php

namespace validation\Custom;


interface FixtureStrategyInterface
{
    public function setValue($value);
    public function getValue();
    public function fix();
}