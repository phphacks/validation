<?php

namespace validation\stock\DateTime;


use Respect\Validation\Validator;
use validation\stock\AbstractStockValidationStrategy;

final class ValidDateTimeStockStrategy extends AbstractStockValidationStrategy
{

    /**
     * @var string
     */
    private $dateTimeProperty;
    /**
     * @var string
     */
    private $template;

    public function __construct(string $dateTimeProperty, string $template = '')
    {
        $this->dateTimeProperty = $dateTimeProperty;
        $this->template= $template == '' ? $dateTimeProperty . ' invalid.' : $template;
    }

    public function validate()
    {
        Validator::attribute($this->dateTimeProperty, Validator::date("Y-m-d H:i:s"))
            ->setTemplate($this->template)
            ->assert($this->value);
    }
}