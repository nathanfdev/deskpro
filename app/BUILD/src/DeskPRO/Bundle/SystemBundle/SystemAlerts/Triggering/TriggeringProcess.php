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
namespace DeskPRO\Bundle\SystemBundle\SystemAlerts\Triggering;

use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Event;
use Doctrine\ORM\EntityManager;

/**
 * Class TriggeringProcess.
 */
class TriggeringProcess
{
    /**
     * @var Trigger[]
     */
    private $triggers = [];

    /**
     * @var TriggeringProcessStateManager
     */
    private $state_manager;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * TriggeringProcess constructor.
     *
     * @param TriggeringProcessStateManager $state_manager
     * @param EntityManager                 $em
     */
    public function __construct(TriggeringProcessStateManager $state_manager, EntityManager $em)
    {
        $this->state_manager = $state_manager;
        $this->em            = $em;
    }

    /**
     * @param Trigger $trigger
     */
    public function addTrigger(Trigger $trigger)
    {
        $this->triggers[] = $trigger;
    }

    /**
     * @return Trigger[]
     */
    public function getTriggers()
    {
        return $this->triggers;
    }

    /**
     * Run Triggers over non processed events.
     */
    public function run()
    {
        $this->em->beginTransaction();

        $events = $this->selectEvents();
        $this->state_manager->provideState($this->triggers);

        foreach ($events as $event) {
            foreach ($this->triggers as $trigger) {
                if ($incident = $trigger->consume($event)) {
                    $this->em->persist($incident);
                }
            }
        }
        $this->em->flush();

        $this->state_manager->saveState($this->triggers);
        $this->setProcessed($events);

        $this->em->commit();
    }

    /**
     * @return Event[]
     */
    private function selectEvents()
    {
        return $this->em->getRepository(Event::class)->findBy(['processed' => false], ['id' => 'asc']);
    }

    /**
     * @param Event[] $events
     *
     * @return mixed
     */
    private function setProcessed($events)
    {
        $qb    = $this->em->createQueryBuilder();
        $query = $qb
            ->update(Event::class, 'e')
            ->set('e.processed', true)
            ->where('e IN (:events)')
            ->setParameters(compact('events'))
            ->getQuery();

        return $query->execute();
    }
}
