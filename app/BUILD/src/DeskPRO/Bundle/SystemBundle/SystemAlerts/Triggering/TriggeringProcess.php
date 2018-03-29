<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\SystemBundle\SystemAlerts\Triggering;

use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\AbstractEvent;
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
     * @var EntityManager
     */
    private $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
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
     * @return int
     */
    public function countIncidents()
    {
        return $this->em
                    ->getConnection()
                    ->executeQuery('SELECT COUNT(id) FROM system_alerts_incidents')
                    ->fetch(\PDO::FETCH_COLUMN);
    }

    /**
     * Run Triggers over non processed events.
     *
     * @param int $batchSize
     *
     * @return bool If some events were processed
     */
    public function run($batchSize = 100)
    {
        $this->provideExistingIncidents();

        $newIncidents     = [];
        $updatedIncidents = [];

        $this->em->beginTransaction();
        $events = $this->selectEvents($batchSize);
        foreach ($events as $event) {
            foreach ($this->triggers as $trigger) {
                if ($incident = $trigger->consume($event)) {
                    if ($incident->getId()) {
                        if (!in_array($incident, $updatedIncidents)) {
                            $updatedIncidents[] = $incident;
                        }
                    } else {
                        if (!in_array($incident, $newIncidents)) {
                            $newIncidents[] = $incident;
                        }
                    }
                    $this->em->persist($incident);
                }
            }
        }
        $this->em->flush();
        $this->setProcessed($events);
        $this->em->commit();

        return [
            'events'            => $events,
            'new_incidents'     => $newIncidents,
            'updated_incidents' => $updatedIncidents,
        ];
    }

    /**
     * Provides triggers with continuing incidents from DB.
     */
    private function provideExistingIncidents()
    {
        foreach ($this->triggers as $trigger) {
            if ($trigger instanceof StatefulIncidentTrigger) {
                $repository = $this->em->getRepository($trigger->getIncidentClass());
                $trigger->setContinuingIncidents($repository->findAll());
            }
        }
    }

    /**
     * @param int $limit
     *
     * @return Event[]
     */
    private function selectEvents($limit)
    {
        return $this->em->getRepository(AbstractEvent::class)->findBy(['processed' => false], ['id' => 'asc'], $limit);
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
            ->update(AbstractEvent::class, 'e')
            ->set('e.processed', true)
            ->where('e IN (:events)')
            ->setParameters(compact('events'))
            ->getQuery();

        return $query->execute();
    }
}
