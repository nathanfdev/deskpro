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
namespace DeskPRO\Bundle\SystemBundle\SystemAlerts;

use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Event;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\Incident;
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
    private $triggering_process;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * IncidentManager constructor.
     *
     * @param TriggeringProcess $triggering_process
     * @param EntityManager     $em
     */
    public function __construct(TriggeringProcess $triggering_process, EntityManager $em)
    {
        $this->triggering_process = $triggering_process;
        $this->em                 = $em;
    }

    /**
     * @param Incident $incident
     */
    public function dismiss(Incident $incident)
    {
        $incident->setDismissed(true);
        $this->em->persist($incident);
        $this->em->flush($incident);

        $triggers = $this->triggering_process->getTriggers();
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
        $this
            ->em
            ->createQuery('DELETE FROM '.Event::class.' e WHERE e.id IN(:ids)')
            ->execute(['ids' => $incident->getEventIds()]);

        $this->em->remove($incident);
        $this->em->flush($incident);
    }
}
