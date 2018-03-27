<?php

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
