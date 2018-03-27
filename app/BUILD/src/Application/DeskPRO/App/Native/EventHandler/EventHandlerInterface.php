<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\App\Native\EventHandler;

interface EventHandlerInterface
{
    /**
     * @param EventContext $context
     */
    public function handleEvent(EventContext $context);
}
