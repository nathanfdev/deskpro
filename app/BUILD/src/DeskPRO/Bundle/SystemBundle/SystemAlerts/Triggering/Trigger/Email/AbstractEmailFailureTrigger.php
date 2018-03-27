<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\SystemBundle\SystemAlerts\Triggering\Trigger\Email;

use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\SuccessEvent;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\Incident;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\StatefulIncident;
use DeskPRO\Bundle\SystemBundle\SystemAlerts\Triggering\AbstractStatefulIncidentTrigger;

/**
 * Class AbstractEmailFailureTrigger.
 */
abstract class AbstractEmailFailureTrigger extends AbstractStatefulIncidentTrigger
{
    /**
     * @var int Trigger will raise an incident only after consistent failures for minutes (the default 0
     *          value means immediate rising)
     */
    private $silenceTime = 0;

    /**
     * @param int $silenceTime
     */
    public function setSilenceTime($silenceTime)
    {
        $this->silenceTime = $silenceTime;
    }

    /**
     * {@inheritdoc}
     */
    protected function isIncidentState(Incident $incident)
    {
        /** @var StatefulIncident $incident */
        if ($incident->getLastEvent() instanceof SuccessEvent) {
            return false;
        }

        $dateFirstFailure = $incident->getDateFirstFailure();
        $dateLastFailure  = $incident->getDateLastFailure();

        /** @var \DateInterval $diff */
        $diff    = $dateLastFailure->diff($dateFirstFailure);
        $minutes = $diff->days * 24 * 60 + $diff->h * 60 + $diff->i;

        return $minutes >= $this->silenceTime;
    }
}
