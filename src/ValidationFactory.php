<?php

namespace validation;

class ValidationFactory
{
    public function createFor($subject)
    {
        if (is_null($subject)){
            throw new \InvalidArgumentException('Parameter subject is required.');
        }

        return new Validation($subject);
    }
}