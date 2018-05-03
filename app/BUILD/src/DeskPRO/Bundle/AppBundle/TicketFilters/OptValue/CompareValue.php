<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters\OptValue;

use DeskPRO\Component\FilterQueryLanguage\Query\Opt\CompareOpt;

class CompareValue extends OptValue
{
    const OPT = CompareOpt::OPT;

    /**
     * @var scalar
     */
    public $value;

    /**
     * CompareValue constructor.
     *
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->value;
    }
}
