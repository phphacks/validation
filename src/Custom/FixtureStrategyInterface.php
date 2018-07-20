<?php

namespace validation\Custom;


interface FixtureStrategyInterface
{
    public function setValue($value);
    public function fix();
}