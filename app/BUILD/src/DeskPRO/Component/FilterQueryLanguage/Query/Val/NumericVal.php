<?php

namespace DeskPRO\Component\FilterQueryLanguage\Query\Val;

use DeskPRO\Component\FilterQueryLanguage\Query\Query;

class NumericVal extends ScalarVal
{
    const VAL_TYPE = Query::VAL_NUMERIC;

    /**
     * @var number
     */
    public $value = 0;

    /**
     * @param number $value
     */
    public function __construct($value)
    {
        $this->value = $value + 0;
    }
}
