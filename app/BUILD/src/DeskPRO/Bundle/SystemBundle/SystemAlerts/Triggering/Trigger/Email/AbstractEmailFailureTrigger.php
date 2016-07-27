<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
