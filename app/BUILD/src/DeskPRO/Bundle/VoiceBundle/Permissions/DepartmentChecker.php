<?php

namespace DeskPRO\Bundle\VoiceBundle\Permissions;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\VoiceQueue;

/**
 * Class DepartmentChecker.
 */
class DepartmentChecker
{
    /**
     * @param VoiceQueue $queue
     * @param Person     $person
     *
     * @return bool
     */
    public function canBeMemberOfVoiceQueue(VoiceQueue $queue, Person $person)
    {
        $permissionsHelper          = $person->getHelper('AgentPermissions');
        $allowedTicketDepartmentIds = $permissionsHelper->getAllowedDepartments('tickets', false, 'full');

        return $queue->getDepartment() && in_array($queue->getDepartment()->getId(), $allowedTicketDepartmentIds);
    }
}
