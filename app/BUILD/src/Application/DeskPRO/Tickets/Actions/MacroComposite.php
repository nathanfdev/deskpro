<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;

class MacroComposite implements MacroActionInterface
{
    /**
     * @var MacroActionInterface[]
     */
    private $actions = [];

    /**
     * @param MacroActionInterface[] $actions
     */
    public function __construct(array $actions = [])
    {
        $this->setAll($actions);
    }

    /**
     * @param MacroActionInterface $term
     */
    public function add(MacroActionInterface $term)
    {
        $this->actions[] = $term;
    }

    /**
     * @param MacroActionInterface[] $actions
     */
    public function setAll(array $actions)
    {
        $this->actions = [];
        foreach ($actions as $t) {
            $this->add($t);
        }
    }

    /**
     * @return MacroActionInterface[]
     */
    public function getAll()
    {
        return $this->actions;
    }

    /**
     * {@inheritdoc}
     */
    public function getMacroPermissionErrors(Person $person, Ticket $ticket, ExecutorContextInterface $context)
    {
        $errors = [];
        foreach ($this->actions as $a) {
            $errors = array_merge($errors, $a->getMacroPermissionErrors($person, $ticket, $context));
        }

        return $errors;
    }

    /**
     * {@inheritdoc}
     */
    public function applyMacro(Person $person, Ticket $ticket, ExecutorContextInterface $context)
    {
        foreach ($this->actions as $a) {
            $a->applyMacro($person, $ticket, $context);
        }
    }
}
