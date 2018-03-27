<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\DependencyInjection\DeskproContainerAwareInterface;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;

class ActionComposite implements ActionInterface, DeskproContainerAwareInterface, \Countable, \IteratorAggregate
{
    /**
     * @var ActionInterface[]
     */
    private $actions = [];

    /**
     * @var \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    private $container;

    /**
     * @param ActionInterface[] $actions
     */
    public function __construct(array $actions = [])
    {
        $this->setAll($actions);
    }

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

    /**
     * @param ActionInterface $term
     */
    public function add(ActionInterface $term)
    {
        $this->actions[] = $term;
    }

    /**
     * @param ActionInterface[] $actions
     */
    public function setAll(array $actions)
    {
        $this->actions = [];
        foreach ($actions as $t) {
            $this->add($t);
        }
    }

    /**
     * @return ActionInterface[]
     */
    public function getAll()
    {
        return $this->actions;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        foreach ($this->actions as $a) {
            if ($a instanceof DeskproContainerAwareInterface) {
                $a->setContainer($this->getContainer());
            }
            if ($a instanceof NoopableInterface) {
                if ($a->isNoop($ticket, $context)) {
                    continue;
                }
            }

            $a->applyAction($ticket, $context);
        }
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->actions);
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->actions);
    }
}
