<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\People\PermissionChecker;

use Application\DeskPRO\Entity\TicketMacro;
use Application\DeskPRO\People\Helpers\AgentPermissions;

/**
 * Class TicketMacroChecker.
 */
class TicketMacroChecker extends AbstractChecker
{
    protected function init()
    {
        $this->person->loadHelper('AgentPermissions');
    }

    /**
     * @param \Application\DeskPRO\Entity\TicketMacro $macro
     *
     * @return bool
     */
    public function canEdit(TicketMacro $macro)
    {
        if ($macro->is_global) {
            return true;
        }

        if (
            $macro->person
            && $macro->person->id === $this->person->id) {
            return true;
        }

        if (
            $macro->department
            && $this->person->getHelper('AgentPermissions')->isDepartmentAllowed($macro->department, 'tickets')
        ) {
            return true;
        }

        return false;
    }
}
