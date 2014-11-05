<?php

namespace Application\DeskPRO\NewSearch\Filter;

use Application\DeskPRO\Entity\Person;

abstract class AbstractFilter implements FilterInterface
{
	/** @var \Application\DeskPRO\Entity\Person */
    protected $person;

    public function __construct(Person $person)
    {
        $this->person = $person;
    }
} 