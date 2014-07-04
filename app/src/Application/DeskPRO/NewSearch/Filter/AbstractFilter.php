<?php

namespace Application\DeskPRO\NewSearch\Filter;

use Application\DeskPRO\Entity\Person;

abstract class AbstractFilter
{
    protected $person;

    public function __construct(Person $person)
    {
        $this->person = $person;
    }

    abstract public function getFilter();
} 