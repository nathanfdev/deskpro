<?php

namespace DeskPRO\Bundle\AuditBundle\Log\FieldFilter;

/**
 * Interface FieldFilterInterface.
 */
interface FieldFilterInterface
{
    /**
     * @param mixed  $value
     * @param string $option
     *
     * @return mixed
     */
    public function filter($value, $option = '');
}
