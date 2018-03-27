<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Actions\ActionDef;

use Application\DeskPRO\Entity\TicketActionDef;

abstract class AbstractActionDef
{
    /**
     * @var \Application\DeskPRO\Entity\TicketActionDef
     */
    private $action_def;

    /**
     * @param TicketActionDef $action_def
     */
    public function __construct(TicketActionDef $action_def)
    {
        $this->action_def = $action_def;
    }

    /**
     * @return TicketActionDef
     */
    public function getActionDef()
    {
        return $this->action_def;
    }

    /**
     * @return string
     */
    abstract public function getTitle();

    /**
     * Gets the classname for a macro action class.
     *
     * @return string|null
     */
    public function getTriggerActionClass()
    {
        return;
    }

    /**
     * Gets the classname for a macro action class.
     *
     * @return string|null
     */
    public function getMacroActionClass()
    {
        return;
    }

    /**
     * Get a string path to the option builder template.
     *
     * @return string
     */
    public function getActionBuilderTemplate()
    {
        return;
    }

    /**
     * Process the data returned from the action array.
     * $options will be whatever info was included in the form.
     *
     * Return null to cancel adding the action (eg its invalid)
     *
     * @param array $options
     *
     * @return array|null
     */
    public function processActionBuilderOptions(array $options)
    {
        return $options;
    }
}
