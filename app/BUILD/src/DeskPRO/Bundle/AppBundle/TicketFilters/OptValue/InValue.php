<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters\OptValue;

use DeskPRO\Component\FilterQueryLanguage\Query\Opt\InOpt;

class InValue extends OptValue
{
    const OPT = InOpt::OPT;

    /**
     * @var array
     */
    public $values = [];

    /**
     * InValue constructor.
     *
     * @param array $values
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->values;
    }
}
