<?php

namespace DeskPRO\Component\FilterQueryLanguage\Query\Val;

use DeskPRO\Component\FilterQueryLanguage\Query\Query;

class StringVal extends ScalarVal
{
    const VAL_TYPE = Query::VAL_STRING;

    /**
     * @var string
     */
    public $value = '';

    /**
     * @param string $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }
}
