<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\DependencyInjection;

interface DeskproContainerAwareInterface
{
    /**
     * @param DeskproContainer $container
     */
    public function setContainer(DeskproContainer $container);
}
