<?php

namespace Application\DeskPRO\CustomFields\Form;

class StringObject
{
    /** @var string */
    private $value;

    public function __construct($value)
    {
        $this->value = (string) $value;
    }

    public function __toString()
    {
        return $this->value;
    }
}
