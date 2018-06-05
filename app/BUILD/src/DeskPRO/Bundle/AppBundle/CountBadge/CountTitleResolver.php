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

    /**
     * Get a hierarchy map for the provided values.
     *
     * The map should be keyed by an ID to an array of information:
     *
     * <code>
     * id => [456, 123] // an array of all parents, ordered from top to btm
     * </code>
     *
     * If the field is not hierarchical (or the values arent), you can return null.
     *
     * @param array $fieldId
     * @param array $values
     *
     * @return array|null
     */
    public function getHierarchyMap($fieldId, array $values);
}
