<?php

namespace validation\stock\String;


use Respect\Validation\Validator;
use validation\stock\AbstractStockValidationStrategy;

final class MaxLengthStockStrategy extends AbstractStockValidationStrategy
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
    private $maxLength;

    public function __construct(string $property, int $maxLength, string $template = '')
    {
        $this->property = $property;
        $this->template= $template == '' ? "$property exceeds maximum length ($maxLength)." : $template;
        $this->maxLength = $maxLength;
    }

    public function validate()
    {
        Validator::attribute($this->property, Validator::length(null, $this->maxLength))
            ->setTemplate($this->template)
            ->assert($this->value);
    }
}