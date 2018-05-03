<?php

namespace DeskPRO\Bundle\AppBundle\CountBadge;

interface CountTitleResolver
{
    /**
     * Get the titles for the provided values.
     *
     * Return titles mapped by id, and in the order you would
     * want them displayed.
     *
     * @param string $fieldId
     * @param array  $values
     *
     * @return string[]
     */
    public function getTitles($fieldId, array $values);
}
