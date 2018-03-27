<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Tickets\TicketActions;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\People\PersonContextInterface;
use Application\DeskPRO\Tickets\TicketChangeTracker;
use DpSys\LowError\SystemErrorHandler;
use Orb\Util\Arrays;
use Orb\Util\Util;
use Psr\Log\LoggerInterface;

/**
 * A collection of ticket actions.
 */
class ActionsCollection
{
    /**
     * @var ActionInterface[]
     */
    protected $actions = [];

    /**
     * @var CollectionModifierInterface[]
     */
    protected $applied_modifiers = [];

    /**
     * Its possible a modifier of a single type to be added multiple times. This is
     * an array of unique names.
     *
     * @var array
     */
    protected $applied_modifier_types = [];

    /**
     * @var bool
     */
    protected $was_stopped = false;

    /**
     * True when actions broke the chain early.
     *
     * @return bool
     */
    public function isBroken()
    {
        return $this->was_stopped;
    }

    /**
     * @return ActionsCollection
     */
    public function getUpdateActionsCollection()
    {
        $collection = new self();
        foreach ($this->actions as $action) {
            if (!$action instanceof ReplyAction) {
                $collection->add($action);
            }
        }

        return $collection;
    }

    /**
     * @return ActionsCollection
     */
    public function getReplyActionsCollection()
    {
        $collection = new self();
        foreach ($this->actions as $action) {
            if ($action instanceof ReplyAction) {
                $collection->add($action);
            }
        }

        return $collection;
    }

    /**
     * @param mixed $action_or_modifier
     * @param array $metadata
     */
    public function add($action_or_modifier, array $metadata = [])
    {
        if ($action_or_modifier instanceof ActionInterface) {
            $this->addAction($action_or_modifier, $metadata);
        } elseif ($action_or_modifier instanceof CollectionModifierInterface) {
            $this->applyCollectionModifier($action_or_modifier);
        }
    }

    /**
     * @return int
     */
    public function countActions()
    {
        return count($this->actions);
    }

    /**
     * Add a new action.
     *
     * @param ActionInterface $action
     * @param array           $metadata
     * @param bool            $prepend
     */
    public function addAction(ActionInterface $action, array $metadata = [], $prepend = false)
    {
        $name = $action->getActionName();

        if (isset($this->actions[$name])) {
            $old_action = $this->actions[$name];
            $new_action = $old_action->merge($action);
            if ($new_action && $new_action instanceof ActionInterface) {
                $action = $new_action;
            }
        }

        $action->setMetaData($metadata);
        $this->actions[$name] = $action;

        if (!$prepend && $action->doPrepend()) {
            $prepend = true;
        }

        if ($prepend) {
            unset($this->actions[$name]);
            \Orb\Util\Arrays::unshiftAssoc($this->actions, $name, $action);
        }
    }

    /**
     * Apply a collection modifier.
     *
     * @param \Application\DeskPRO\Tickets\TicketActions\CollectionModifierInterface $modifier
     */
    public function applyCollectionModifier(CollectionModifierInterface $modifier)
    {
        $name                                = get_class($modifier);
        $this->applied_modifier_types[$name] = $name;
        $this->applied_modifiers[]           = $modifier;
    }

    /**
     * Applies all modifiers. Should be run when the collection is finalized
     * to ensure that the order of modifiers doesn't affect anything.
     */
    public function applyAllModifiers()
    {
        foreach ($this->applied_modifiers as $modifier) {
            $modifier->modifyCollection($this);
        }
    }

    /**
     * Check if a certain action type is set.
     *
     * @return bool
     */
    public function hasActionType($name)
    {
        if (strpos($name, '\\') === false) {
            $name = 'Application\\DeskPRO\\Tickets\\TicketActions\\'.$name.'Action';
        }

        return isset($this->actions[$name]);
    }

    /**
     * Check if a certain action type is set.
     *
     * @return bool
     */
    public function hasModifierType($name)
    {
        if (strpos($name, '\\') === false) {
            $name = 'Application\\DeskPRO\\Tickets\\TicketActions\\'.$name.'Modifier';
        }

        return isset($this->applied_modifier_types[$name]);
    }

    /**
     * Get a set action by name.
     *
     *
     * @param  $name
     *
     * @throws \InvalidArgumentException When the action doesnt exist
     *
     * @return \Application\DeskPRO\Tickets\TicketActions\ActionInterface
     */
    public function getActionType($name)
    {
        if (strpos($name, '\\') === false) {
            $name = 'Application\\DeskPRO\\Tickets\\TicketActions\\'.$name.'Action';
        }

        if (!$this->hasActionType($name)) {
            throw new \InvalidArgumentException("No action type `$name` exists");
        }

        return $this->actions[$name];
    }

    /**
     * Remove an action type from the collection, and return it.
     *
     *
     * @param string $name
     *
     * @throws \InvalidArgumentException When action doesnt exist
     *
     * @return \Application\DeskPRO\Tickets\TicketActions\ActionInterface
     */
    public function removeActionType($name)
    {
        if (strpos($name, '\\') === false) {
            $name = 'Application\\DeskPRO\\Tickets\\TicketActions\\'.$name.'Action';
        }

        if (!$this->hasActionType($name)) {
            throw new \InvalidArgumentException("No action type `$name` exists");
        }

        $action = $this->actions[$name];
        unset($this->actions[$name]);

        return $action;
    }

    /**
     * Get an array of set action types.
     *
     * @return array
     */
    public function getActionTypeNames()
    {
        return array_keys($this->actions);
    }

    /**
     * Get an array of ticket actions.
     *
     * @return array
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * Checks to see if the $person_context person can perform all of the actions in the collection.
     *
     * @param \Application\DeskPRO\Entity\Ticket $ticket
     * @param \Application\DeskPRO\Entity\Person $person_context
     *
     * @return bool
     */
    public function applyCheckPermission(Ticket $ticket, Person $person_context)
    {
        // Load up common helpers
        $person_context->loadHelper('Agent');
        $person_context->loadHelper('AgentTeam');
        $person_context->loadHelper('AgentPermissions');
        $person_context->loadHelper('PermissionsManager');
        $person_context->loadHelper('HelpMessages');
        $person_context->loadHelper('AgentPrefs');

        foreach ($this->actions as $action) {
            if ($action instanceof PersonContextInterface) {
                $action->setPersonContext($person_context);
            }

            if ($action instanceof PermissionableAction) {
                if (!$action->checkPermission($ticket, $person_context)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Apply actions in this collection to $ticket, using $person_context as
     * the context on actions that require it.
     *
     * @param \Application\DeskPRO\Tickets\TicketChangeTracker $ticket_tracker
     * @param \Application\DeskPRO\Entity\Ticket               $ticket
     * @param \Application\DeskPRO\Entity\Person               $person_context
     * @param LoggerInterface                                  $logger
     *
     * @throws \Exception
     */
    public function apply(TicketChangeTracker $ticket_tracker = null, Ticket $ticket, Person $person_context = null, $logger = null)
    {
        $this->was_stopped = false;

        $this->applyAllModifiers();

        foreach ($this->actions as $action) {
            if ($person_context && $action instanceof PersonContextInterface) {
                $action->setPersonContext($person_context);
            }

            $time = microtime(true);

            $metadata = $action->getMetaData();
            if ($ticket_tracker && isset($metadata['trigger'])) {
                $ticket_tracker->setApplyingTrigger($metadata['trigger']);
            }
            if ($ticket_tracker && isset($metadata['sla'])) {
                $sla_status = isset($metadata['sla_status']) ? $metadata['sla_status'] : null;
                $ticket_tracker->setApplyingSla($metadata['sla'], $sla_status);
            }

            try {
                $action->apply($ticket);

                if ($ticket_tracker && isset($metadata['trigger'])) {
                    $ticket_tracker->setApplyingTrigger(null);
                }
                if ($ticket_tracker && isset($metadata['sla'])) {
                    $ticket_tracker->setApplyingSla(null, null);
                }
            } catch (\Exception $e) {
                $einfo = SystemErrorHandler::getExceptionInfo($e);
                SystemErrorHandler::logErrorInfo($einfo);

                if ($logger) {
                    $name = Util::getBaseClassname($action);
                    $logger->log(sprintf("[$name] EXCEPTION (%s): %s %s", $einfo['session_name'], $einfo['exception_type'], $einfo['summary']), \Orb\Log\Logger::DEBUG);
                }

                throw $e;
            }

            if ($logger) {
                $name = Util::getBaseClassname($action);
                $logger->log(sprintf("[$name] Took %.4f seconds", microtime(true) - $time), \Orb\Log\Logger::DEBUG);
            }
        }
    }

    /**
     * Apply actions in this collection to a collection of $tickets, using $person_context
     * as the context on actions that require it.
     *
     * @param \Application\DeskPRO\Entity\Ticket[] $tickets
     * @param \Application\DeskPRO\Entity\Person   $person_context
     */
    public function applyToCollection(array $tickets, Person $person_context)
    {
        foreach ($tickets as $t) {
            $this->apply($t, $person_context);
        }
    }

    /**
     * Get an array of actions that would be performed on the ticket.
     *
     * @param \Application\DeskPRO\Entity\Ticket $ticket
     * @param \Application\DeskPRO\Entity\Person $person_context
     *
     * @return array
     */
    public function getApplyActions(Ticket $ticket, Person $person_context)
    {
        $actions = [];

        foreach ($this->actions as $action) {
            if ($action instanceof PersonContextInterface) {
                $action->setPersonContext($person_context);
            }

            $actions = array_merge($actions, $action->getApplyActions($ticket));
        }

        return $actions;
    }

    /**
     * @param array $order
     */
    public function sortActions(array $order)
    {
        if (!isset($order['default'])) {
            $order['default'] = 0;
        }

        uasort($this->actions, function (ActionInterface $a, ActionInterface $b) use ($order) {
            $a_name = Util::getBaseClassname($a);
            $b_name = Util::getBaseClassname($b);

            $a_default = $a->doPrepend() ? $order['prepend'] : $order['default'];
            $b_default = $b->doPrepend() ? $order['prepend'] : $order['default'];

            $a_order = isset($order[$a_name]) ? $order[$a_name] : $a_default;
            $b_order = isset($order[$b_name]) ? $order[$b_name] : $b_default;

            if ($a_order == $b_order) {
                return 0;
            }

            return $a_order < $b_order ? -1 : 1;
        });
    }

    /**
     * @param bool $as_html
     *
     * @return array
     */
    public function getDescriptions($as_html)
    {
        $desc = [];
        foreach ($this->actions as $action) {
            $desc[] = $action->getDescription($as_html);
        }

        foreach ($this->applied_modifiers as $mod) {
            $desc[] = $mod->getDescription($as_html);
        }

        $desc = Arrays::removeFalsey($desc);

        if ($as_html) {
            foreach ($desc as &$d) {
                $d = str_replace(
                    ['<error>', '</error>'],
                    ['<span class="term-error">', '</span>'],
                    $d
                );
            }
        }

        return $desc;
    }
}
