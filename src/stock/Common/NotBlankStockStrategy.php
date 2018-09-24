<?php

namespace validation\stock\Common;


use Respect\Validation\Validator;
use validation\stock\AbstractStockValidationStrategy;

class NotBlankStockStrategy extends AbstractStockValidationStrategy
{

    /**
     * @var string
     */
    private $property;

    /**
     * @var string
     */
    private $template;

    public function __construct(string $property, string $template = '')
    {
        $this->property = $property;
        $this->template= $template == '' ? "$property cannot be empty" : $template;
    }

    public function validate()
    {
        Validator::attribute($this->property, Validator::notBlank())
            ->setTemplate($this->template)
            ->assert($this->value);
    }
}