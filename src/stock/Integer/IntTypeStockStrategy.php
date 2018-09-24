<?php

namespace validation\stock\Integer;


use Respect\Validation\Validator;
use validation\stock\AbstractStockValidationStrategy;

final class IntTypeStockStrategy extends AbstractStockValidationStrategy
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
        $this->template= $template == '' ? "$property is not integer." : $template;
    }

    public function validate()
    {
        Validator::attribute($this->property, Validator::intType())
            ->setTemplate($this->template)
            ->assert($this->value);
    }
}