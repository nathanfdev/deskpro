<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\SystemBundle\SystemAlerts;

use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\Incident;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\StatefulIncident;
use DeskPRO\Bundle\SystemBundle\SystemAlerts\Triggering\StatefulIncidentTrigger;
use DeskPRO\Bundle\SystemBundle\SystemAlerts\Triggering\TriggeringProcess;
use Doctrine\ORM\EntityManager;

/**
 * Class IncidentManager.
 */
class IncidentManager
{
    /**
     * @var TriggeringProcess
     */
    private $triggeringProcess;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * IncidentManager constructor.
     *
     * @param TriggeringProcess $triggeringProcess
     * @param EntityManager     $em
     */
    public function __construct(TriggeringProcess $triggeringProcess, EntityManager $em)
    {
        $this->triggeringProcess = $triggeringProcess;
        $this->em                = $em;
    }

    /**
     * @param StatefulIncident $incident
     */
    public function dismiss(StatefulIncident $incident)
    {
        $incident->setDismissed(true);
        $this->em->persist($incident);
        $this->em->flush();

        $triggers = $this->triggeringProcess->getTriggers();
        foreach ($triggers as $trigger) {
            if ($trigger instanceof StatefulIncidentTrigger && $trigger->dismisses($incident)) {
                if ($callable = $trigger->getDismissedCallback()) {
                    call_user_func($callable, $incident);
                }
                if ($callable = $trigger->getClosedCallback()) {
                    call_user_func($callable, $incident);
                }
            }
        }
    }

    /**
     * @param Incident $incident
     */
    public function remove(Incident $incident)
    {
        $this->em->beginTransaction();

        foreach ($incident->getEvents() as $event) {
            $this->em->remove($event);
        }
        $this->em->remove($incident);
        $this->em->flush();

        $this->em->commit();
    }
}
