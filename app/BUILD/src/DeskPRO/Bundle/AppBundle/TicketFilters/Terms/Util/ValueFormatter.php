<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Terms\Util;

use DeskPRO\Bundle\AppBundle\TicketFilters\OptValue\OptValue;
use DeskPRO\Component\Util\ListUtils;

/**
 * Class ValueFormatter.
 */
class ValueFormatter
{
    /**
     * @param mixed $checkValue
     *
     * @return array
     */
    public static function formatList($checkValue)
    {
        if ($checkValue === null) {
            $checkValue = [];
        }
        if ($checkValue === 0 || $checkValue === '0') {
            $checkValue = [];
        }
        if (!is_array($checkValue)) {
            $checkValue = [$checkValue];
        }

        $checkValue = ListUtils::flatten($checkValue);

        return $checkValue;
    }

    /**
     * @param mixed $checkValue
     *
     * @return array|bool
     */
    public static function formatRange($checkValue)
    {
        if (empty($checkValue[0]) || empty($checkValue[1])) {
            return false;
        }

        $checkValue = ListUtils::map($checkValue, function ($v) {
            if ($v instanceof \DateTime) {
                return $v->format('Y-m-d H:i:s');
            }

            return $v;
        });

        return $checkValue;
    }

    /**
     * @param mixed $checkValue
     *
     * @return mixed|string
     */
    public static function formatCheckValue($checkValue)
    {
        if ($checkValue instanceof OptValue) {
            $checkValue = $checkValue->getValue();
        }

        if ($checkValue instanceof \DateTime) {
            $tmp = clone $checkValue;
            $tmp->setTimezone(new \DateTimeZone('UTC'));
            $checkValue = $tmp->format('Y-m-d H:i:s');
        }

        return $checkValue;
    }
}
