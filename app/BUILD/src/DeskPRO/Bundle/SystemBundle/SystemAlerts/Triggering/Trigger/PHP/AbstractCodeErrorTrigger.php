<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\SystemBundle\SystemAlerts\Triggering\Trigger\PHP;

use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\SuccessEvent;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\Incident;
use DeskPRO\Bundle\SystemBundle\SystemAlerts\Triggering\AbstractStatefulIncidentTrigger;

/**
 * Class AbstractCodeErrorTrigger.
 */
abstract class AbstractCodeErrorTrigger extends AbstractStatefulIncidentTrigger
{
    /**
     * @var int Trigger will analyse errors within the specified period
     */
    private $periodMinutes;

    /**
     * @var int Trigger will raise an incident once failures count within the reaches this number
     */
    private $incidentErrorsCount;

    /**
     * AbstractCodeErrorTrigger constructor.
     */
    public function __construct()
    {
        $this->periodMinutes       = 60 * 24 * 365;
        $this->incidentErrorsCount = 1;
    }

    /**
     * @param int $periodMinutes
     *
     * @throws \Exception
     */
    public function setPeriodMinutes($periodMinutes)
    {
        $this->periodMinutes = $periodMinutes;
    }

    /**
     * @param int $incidentErrorsCount
     */
    public function setIncidentErrorsCount($incidentErrorsCount)
    {
        $this->incidentErrorsCount = $incidentErrorsCount;
    }

    /**
     * {@inheritdoc}
     */
    protected function isIncidentState(Incident $incident)
    {
        // When event is already raised, it will remain raised unless there are new success event
        // so we can skip main algorithm which requires loading the whole events collection

        if ($incident->isRaised()) {
            $hasNewSuccessEvent = false;
            foreach ($incident->getNewEvents() as $event) {
                if ($event instanceof SuccessEvent) {
                    $hasNewSuccessEvent = true;
                    break;
                }
            }

            if (!$hasNewSuccessEvent) {
                return true;
            }
        }

        // Main algorithm checks number of errors within the period of time

        $events      = $incident->getEvents();
        $periodCount = 0;
        $now         = new \DateTime();

        foreach ($events as $event) {
            $date = clone $event->getDateCreated();
            $date = $date->modify("+{$this->periodMinutes} minutes");
            if ($date > $now) {
                ++$periodCount;

                if ($periodCount >= $this->incidentErrorsCount) {
                    return true;
                }
            }
        }

        return false;
    }
}
