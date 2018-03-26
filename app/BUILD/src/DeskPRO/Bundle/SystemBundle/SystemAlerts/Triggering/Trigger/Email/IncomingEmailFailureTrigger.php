<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\SystemBundle\SystemAlerts\Triggering\Trigger\Email;

use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Email\IncomingEmailFailureEvent;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Email\IncomingEmailSuccessEvent;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Event;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\Email\IncomingEmailFailureIncident;

/**
 * Class IncomingEmailFailureTrigger.
 */
class IncomingEmailFailureTrigger extends AbstractEmailFailureTrigger
{
    /**
     * {@inheritdoc}
     */
    protected function supports(Event $event)
    {
        return ($event instanceof IncomingEmailFailureEvent) || ($event instanceof IncomingEmailSuccessEvent);
    }

    /**
     * {@inheritdoc}
     */
    public function getIncidentClass()
    {
        return IncomingEmailFailureIncident::class;
    }
}
