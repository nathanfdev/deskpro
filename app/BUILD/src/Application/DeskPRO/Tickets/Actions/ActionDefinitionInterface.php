<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Actions;

interface ActionDefinitionInterface
{
    /**
     * Gets the type name of the criteria.
     *
     * @return string
     */
    public function getActionType();

    /**
     * Get's an array of options.
     *
     * @return array
     */
    public function getActionOptions();
}
