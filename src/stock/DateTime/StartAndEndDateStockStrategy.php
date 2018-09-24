<?php

namespace validation\stock\DateTime;


use Respect\Validation\Validator;
use validation\stock\AbstractStockValidationStrategy;

final class StartAndEndDateStockStrategy extends AbstractStockValidationStrategy
{
    /**
     * @var string
     */
    private $startDateProperty;
    /**
     * @var string
     */
    private $endDateProperty;

    /**
     * @var string
     */
    private $template;

    public function __construct(string $startDateProperty, string $endDateProperty, string $template = '')
    {
        $this->startDateProperty = $startDateProperty;
        $this->endDateProperty = $endDateProperty;
        $this->template = $template;
    }

    public function validate()
    {
        $getStartDateMethod = 'get' . $this->startDateProperty;
        $getEndDateMethod = 'get' . $this->endDateProperty;
        $startDate = new \DateTime($this->value->$getStartDateMethod());
        $endDate = new \DateTime($this->value->$getEndDateMethod());

        $templateToUse = $this->template == '' ? 'Start date greater than end date' : $this->template;

        Validator::trueVal()->setTemplate($templateToUse)
            ->assert($endDate > $startDate);
    }
}