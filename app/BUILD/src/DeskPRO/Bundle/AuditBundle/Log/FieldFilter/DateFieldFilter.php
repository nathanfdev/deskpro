<?php

namespace DeskPRO\Bundle\AuditBundle\Log\FieldFilter;

/**
 * Class DateFieldFilter.
 */
class DateFieldFilter implements FieldFilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function filter($value, $format = 'Y-m-d H:i:s')
    {
        if ($value instanceof \DateTime) {
            return $value->format($format);
        }

        return $value;
    }
}
