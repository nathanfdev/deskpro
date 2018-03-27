<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\SystemBundle\SystemAlerts\Triggering\Trigger\Exception;

use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Event;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Exception\ExceptionEvent;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\Exception\ExceptionIncident;
use DeskPRO\Bundle\SystemBundle\SystemAlerts\Triggering\Trigger\PHP\AbstractCodeErrorTrigger;

/**
 * Class ExceptionTrigger.
 *
 * Unhandled exception incident trigger
 */
class ExceptionTrigger extends AbstractCodeErrorTrigger
{
    /**
     * {@inheritdoc}
     */
    public function getIncidentClass()
    {
        return ExceptionIncident::class;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports(Event $event)
    {
        return $event instanceof ExceptionEvent;
    }
}
