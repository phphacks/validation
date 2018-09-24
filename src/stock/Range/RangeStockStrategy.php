<?php

namespace validation\stock\Range;

use Respect\Validation\Validator;
use validation\stock\AbstractStockValidationStrategy;

final class RangeStockStrategy extends AbstractStockValidationStrategy
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
    private $min;
    /**
     * @var int
     */
    private $max;

    public function __construct(string $property, int $min, int $max, string $template = '')
    {
        $this->property = $property;
        $this->min = $min;
        $this->max = $max;
        $this->template= $template == '' ? "$property  must be between $min and $max" : $template;
    }

    public function validate()
    {
        Validator::attribute($this->property, Validator::between($this->min,$this->max))
            ->setTemplate($this->template)
            ->assert($this->value);
    }
}