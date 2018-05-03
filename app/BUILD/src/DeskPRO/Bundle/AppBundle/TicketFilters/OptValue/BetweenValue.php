<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters\OptValue;

use DeskPRO\Component\FilterQueryLanguage\Query\Opt\BetweenOpt;

class BetweenValue extends OptValue
{
    const OPT = BetweenOpt::OPT;

    /**
     * @var scalar
     */
    public $value1;

    /**
     * @var scalar
     */
    public $value2;

    /**
     * BetweenValue constructor.
     *
     * @param mixed $value1
     * @param mixed $value2
     */
    public function __construct($value1, $value2)
    {
        $this->value1 = $value1;
        $this->value2 = $value2;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return [$this->value1, $this->value2];
    }
}
