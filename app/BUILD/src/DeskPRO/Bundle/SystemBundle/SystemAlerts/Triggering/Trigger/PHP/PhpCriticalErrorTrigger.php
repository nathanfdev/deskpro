<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\SystemBundle\SystemAlerts\Triggering\Trigger\PHP;

use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Event;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\PHP\ErrorEvent;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\PHP\PhpCriticalErrorIncident;

/**
 * Class PhpCriticalErrorTrigger.
 */
class PhpCriticalErrorTrigger extends AbstractCodeErrorTrigger
{
    /**
     * {@inheritdoc}
     */
    public function getIncidentClass()
    {
        return PhpCriticalErrorIncident::class;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports(Event $event)
    {
        return $event instanceof ErrorEvent && $event->isCritical();
    }
}
