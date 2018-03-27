<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\DependencyInjection\DeskproContainerAwareInterface;

abstract class AbstractContainerAwareAction extends AbstractAction implements DeskproContainerAwareInterface
{
    /**
     * @var \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    private $container;

    /**
     * @param DeskproContainer $container
     */
    public function setContainer(DeskproContainer $container)
    {
        $this->container = $container;
    }

    /**
     * Gets the set container.
     *
     * @throws \RuntimeException When no container has been set yet
     *
     * @return DeskproContainer
     */
    protected function getContainer()
    {
        if (!$this->container) {
            throw new \RuntimeException('No container has been set');
        }

        return $this->container;
    }
}
