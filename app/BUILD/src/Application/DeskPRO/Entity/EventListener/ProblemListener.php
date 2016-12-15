<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace Application\DeskPRO\Entity\EventListener;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Problem;
use Application\DeskPRO\Entity\TicketFilter;
use Application\DeskPRO\Searcher\TicketSearch;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Orb\Util\DpStrings;
use Orb\Util\Strings;

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
    protected $queue = array();

    /**
     * @var \SplQueue
     */
    protected $inserts;

    public function __construct(DeskproContainer $container)
    {
        $this->inserts = new \SplQueue();
        $this->updates = new \SplQueue();
        $this->conn    = $container->getEm()->getConnection();
        $this->cont    = $container;
    }

    /**
     * @param Problem $problem
     */
    public function onPreUpdate(Problem $problem, PreUpdateEventArgs $event)
    {
        $this->updates->enqueue(
            array(
                'entity'    => $problem,
                'changeset' => $event->getEntityChangeSet(),
            )
        );
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

    /**
     *
     */
    public function onPostUpdate(Problem $problem, LifecycleEventArgs $event)
    {
        while (!$this->updates->isEmpty()) {
            $data = $this->updates->dequeue();
            /* @var Problem $p */
            $problem = $data['entity'];
            $filter  = $event->getEntityManager()->getRepository('DeskPRO:TicketFilter')->findOneBy(array(
                'sys_name' => Problem::FILTER_PREFIX.$problem->id,
            ));

            $this->queue[] = array(
                'channel'      => self::CHANNEL_UPDATE,
                'auth'         => DpStrings::random(15, Strings::CHARS_KEY),
                'date_created' => date('Y-m-d H:i:s'),
                'data'         => serialize(array(
                    'id'        => $problem->id,
                    'title'     => $problem->title,
                    'filter_id' => $filter ? $filter->id : 0,
                    'changeset' => $data['changeset'],
                )),
            );
        }

        $this->sendQueue();
    }

    /**
     * @param Problem $problem
     */
    public function onPostPersist(Problem $problem, LifecycleEventArgs $event)
    {
        while (!$this->inserts->isEmpty()) {
            /** @var Problem $problem */
            $problem = $this->inserts->dequeue();

            $filter           = new TicketFilter();
            $filter->title    = 'Problem #'.$problem->id;
            $filter->sys_name = Problem::FILTER_PREFIX.$problem->id;
            $filter->terms    = array(array(
                'type'    => TicketSearch::TERM_PROBLEMS,
                'op'      => 'is',
                'options' => array(
                    'problems' => array($problem->id),
                ),
            ));

            $filter->is_global  = true;
            $filter->is_enabled = true;
            $event->getEntityManager()->persist($filter);
            $event->getEntityManager()->flush($filter);

            $this->queue[] = array(
                'channel'      => self::CHANNEL_NEW,
                'auth'         => DpStrings::random(15, Strings::CHARS_KEY),
                'date_created' => date('Y-m-d H:i:s'),
                'data'         => serialize(array(
                    'id'        => $problem->id,
                    'title'     => $problem->title,
                    'filter_id' => $filter->id,
                )),
            );
        }

        $this->sendQueue();
    }

    /**
     * @return int
     */
    public function sendQueue()
    {
        if (!$this->queue) {
            return 0;
        }

        $q           = $this->queue;
        $this->queue = array();

        $this->conn->batchInsert('client_messages', $q);

        return count($q);
    }
}
