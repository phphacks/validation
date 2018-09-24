<?php

namespace validation\stock\String;

use Respect\Validation\Validator;
use validation\stock\AbstractStockValidationStrategy;

final class MinLengthStockStrategy extends AbstractStockValidationStrategy
{
    /**
     * @var string
     */
    private $property;
    /**
     * @var string
     */
    private $template;
    /**
     * @var int
     */
    private $minLength;

    public function __construct(string $property, int $minLength, string $template = '')
    {
        $this->property = $property;
        $this->template= $template == '' ? "$property must have at least $minLength characters." : $template;
        $this->minLength = $minLength;
    }

    public function validate()
    {
        Validator::attribute($this->property, Validator::length([$this->minLength], []))
            ->setTemplate($this->template)
            ->assert($this->value);
    }
}