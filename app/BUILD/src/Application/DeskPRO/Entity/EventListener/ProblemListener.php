<?php

namespace Application\DeskPRO\Entity\EventListener;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\LegacyTicketFilter;
use Application\DeskPRO\Entity\Problem;
use Application\DeskPRO\Searcher\TicketSearch;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class ProblemListener
{
    const CHANNEL_NEW    = 'agent.problems-created';
    const CHANNEL_UPDATE = 'agent.problems-updated';

    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $conn;

    /**
     * @var DeskproContainer
     */
    protected $cont;

    /**
     * @var \SplQueue
     */
    protected $updates;

    /**
     * @var array
     */
    protected $queue = [];

    /**
     * @var \SplQueue
     */
    protected $inserts;

    public function __construct(DeskproContainer $container)
    {
        $this->inserts         = new \SplQueue();
        $this->updates         = new \SplQueue();
        $this->conn            = $container->getEm()->getConnection();
        $this->cont            = $container;
        $this->eventDispatcher = $container->get('event_dispatcher');
    }

    /**
     * @param Problem $problem
     */
    public function onPreUpdate(Problem $problem, PreUpdateEventArgs $event)
    {
        $this->updates->enqueue([
            'entity'    => $problem,
            'changeset' => $event->getEntityChangeSet(),
        ]);
    }

    /**
     * @param Problem $problem
     */
    public function onPrePersist(Problem $problem)
    {
        if (!$problem->id) {
            $this->inserts->enqueue($problem);
        }
    }

    public function onPostUpdate(Problem $problem, LifecycleEventArgs $event)
    {
        while (!$this->updates->isEmpty()) {
            $data = $this->updates->dequeue();
            /* @var Problem $problem */
            $problem = $data['entity'];
            $filter  = $event->getEntityManager()->getRepository(LegacyTicketFilter::class)->findOneBy([
                'sys_name' => Problem::FILTER_PREFIX.$problem->getId(),
            ]);

            $this->eventDispatcher->dispatch(LegacySystemEvent::EVENT_NAME, new LegacySystemEvent(
                self::CHANNEL_UPDATE,
                [
                    'id'        => $problem->getId(),
                    'title'     => $problem->getTitle(),
                    'filter_id' => $filter ? $filter->getId() : 0,
                    'changeset' => $data['changeset'],
                ]
            ));
        }
    }

    /**
     * @param Problem $problem
     */
    public function onPostPersist(Problem $problem, LifecycleEventArgs $event)
    {
        while (!$this->inserts->isEmpty()) {
            /** @var Problem $problem */
            $problem  = $this->inserts->dequeue();
            $filterId = $this->createFilter($event->getEntityManager(), $problem);

            $this->eventDispatcher->dispatch(LegacySystemEvent::EVENT_NAME, new LegacySystemEvent(
                self::CHANNEL_NEW,
                [
                    'id'        => $problem->getId(),
                    'title'     => $problem->getTitle(),
                    'filter_id' => $filterId,
                ]
            ));
        }
    }

    /**
     * Save filter into the DB.
     *
     * Saves a filter w/o entity manager flush() as:
     * > EntityManager#flush() can NOT be called safely inside its listeners.
     * http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/events.html#postflush
     *
     * @param EntityManager $em
     * @param Problem       $problem
     *
     * @return int
     */
    private function createFilter(EntityManager $em, Problem $problem)
    {
        $connection = $em->getConnection();
        $id         = $problem->getId();
        $connection->insert($em->getClassMetadata(LegacyTicketFilter::class)->getTableName(), [
            'title'      => 'Problem #'.$id,
            'sys_name'   => Problem::FILTER_PREFIX.$id,
            'is_global'  => 1,
            'is_enabled' => 1,
            'terms'      => '[{"type":"'.TicketSearch::TERM_PROBLEMS.'","op":"is","options":{"problems":['.$id.']}}]',
        ]);

        return $connection->lastInsertId();
    }
}
