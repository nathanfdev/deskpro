<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters\OptValue;

abstract class OptValue
{
    const OPT = '';

    /**
     * Returns the PHP value for this option.
     *
     * @return mixed
     */
    abstract public function getValue();

    public function getOptType()
    {
        return static::OPT;
    }
}
