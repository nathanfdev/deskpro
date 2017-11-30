<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
