<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\SystemBundle\SystemAlerts\Triggering\Trigger\Email;

use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Email\OutgoingEmailFailureEvent;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Email\OutgoingEmailSuccessEvent;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Event;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\Email\OutgoingEmailFailureIncident;

/**
 * Class OutgoingEmailFailureTrigger.
 */
class OutgoingEmailFailureTrigger extends AbstractEmailFailureTrigger
{
    /**
     * {@inheritdoc}
     */
    protected function supports(Event $event)
    {
        return ($event instanceof OutgoingEmailFailureEvent) || ($event instanceof OutgoingEmailSuccessEvent);
    }

    /**
     * {@inheritdoc}
     */
    public function getIncidentClass()
    {
        return OutgoingEmailFailureIncident::class;
    }
}
