<?php

namespace DeskPRO\Bundle\VoiceBundle\Permissions;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\VoiceQueue;

/**
 * Class VoicePermissionsChecker.
 */
class VoicePermissionsChecker
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

        return $person->isActiveAgent()
            && $queue->getDepartment()
            && in_array($queue->getDepartment()->getId(), $allowedTicketDepartmentIds);
    }
}
