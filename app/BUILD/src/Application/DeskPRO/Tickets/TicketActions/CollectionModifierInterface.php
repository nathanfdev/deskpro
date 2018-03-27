<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Tickets\TicketActions;

interface CollectionModifierInterface
{
    /**
     * Inspect the collection and modify it.
     *
     * @param ActionsCollection $collection
     */
    public function modifyCollection(ActionsCollection $collection);

    /**
     * @return string
     */
    public function getDescription($as_html = true);
}
