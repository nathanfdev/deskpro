<?php

namespace DeskPRO\Component\FilterQueryLanguage\Query\Val;

use DeskPRO\Component\FilterQueryLanguage\Query\Query;
use DeskPRO\Component\FilterQueryLanguage\Query\QueryPart;

abstract class Val extends QueryPart
{
    const VAL_TYPE = 'ABSTRACT_VAL';

    /**
     * @return array
     */
    abstract public function toArray();

    /**
     * @return string
     */
    public function getValueType()
    {
        return static::VAL_TYPE;
    }

    /**
     * @param array $props
     */
    public static function fromArray(array $props)
    {
        switch ($props['valueType']) {
            case Query::VAL_STRING:
                return StringVal::fromArray($props);

            case Query::VAL_NUMERIC:
                return NumericVal::fromArray($props);

            case Query::VAL_BOOLEAN:
                return BoolVal::fromArray($props);

            case Query::VAL_VAR:
                return VarVal::fromArray($props);

            case Query::VAL_FUNC:
                return FuncVal::fromArray($props);

            case Query::VAL_RELATIVE_TIME:
                return RelativeTimeVal::fromArray($props);

            default:
                throw new \InvalidArgumentException('Unknow valueType');
        }
    }
}
