<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters\OptValue;

use DeskPRO\Component\FilterQueryLanguage\Query\Opt\NoOpt;

class NoValue extends OptValue
{
    const OPT = NoOpt::OPT;

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return null;
    }
}
