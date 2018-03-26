<?php

namespace DeskPRO\Bundle\AuditBundle\Log\FieldFilter;

/**
 * Class MaskFieldFilter.
 */
class MaskFieldFilter implements FieldFilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function filter($value, $maskChar = '*')
    {
        $stringLength = mb_strlen($value);

        // mask 60% of string with *

        $start     = mb_substr($value, 0, ceil($stringLength / 5));
        $end       = mb_substr($value, -ceil($stringLength / 5));
        $fillCount = $stringLength - mb_strlen($start);

        return sprintf("%s%'{$maskChar}{$fillCount}s", $start, $end);
    }
}
