<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at https://www.deskpro.com/eula/                            |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

namespace Application\DeskPRO\Entity\EventListener;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Problem;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketFilter;
use Application\DeskPRO\People\PermissionChecker\TicketChecker;
use Application\DeskPRO\Searcher\TicketSearch;
use Application\DeskPRO\UI\RuleBuilder;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Orb\Util\DpStrings;
use Orb\Util\Strings;

class ProblemListener
{
    const CHANNEL_NEW = 'agent.problems-created';
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
     * @var int
     */
    protected $inserts = 0;

    /**
     * @var array
     */
    protected $queue = array();

    /**
     * @var \SplQueue
     */
    protected $new_problems;

    public function __construct(DeskproContainer $container)
    {
        $this->new_problems = new \SplQueue();
        $this->updates = new \SplQueue();
        $this->conn = $container->getEm()->getConnection();
        $this->cont = $container;
    }

    /**
     * @param Problem $problem
     */
    public function onPreUpdate(Problem $problem, PreUpdateEventArgs $event)
    {
        $this->updates->enqueue(
            array(
                'entity' => $problem,
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
            $this->inserts++;
        }

        if (!$problem->id) {
            $this->new_problems->enqueue($problem);
        }
    }

    /**
     *
     */
    public function onPostUpdate()
    {
        while (!$this->updates->isEmpty()) {
            $data = $this->updates->dequeue();
            /** @var Problem $p */
            $p = $data['entity'];

            foreach ($this->cont->getAgentData()->getOnlineAgents() as $agent) {

                $incidents = 0;
                /** @var TicketChecker $checker */
                $checker = $agent->PermissionsManager->TicketChecker;

                foreach ($p->tickets as $ticket) {
                    if (!$checker->canView($ticket)) {
                        continue;
                    }

                    if (Ticket::HIDDEN_STATUS_DELETED === $ticket->hidden_status || Ticket::HIDDEN_STATUS_SPAM === $ticket->hidden_status) {
                        continue;
                    }

                    $incidents++;
                }

                $this->queue[] = array(
                    'channel' => self::CHANNEL_UPDATE,
                    'auth' => DpStrings::random(15, Strings::CHARS_KEY),
                    'for_person_id' => $agent->id,
                    'date_created' => date('Y-m-d H:i:s'),
                    'data' => serialize(
                        array(
                            'id' => $p->id,
                            'title' => $p->title,
                            'incidents' => $incidents,
                            'changeset' => $data['changeset'],
                        )
                    )
                );
            }
        }

        if ($this->updates->isEmpty() && $this->queue) {
            $this->sendQueue();
        }
    }

    /**
     * @param Problem $problem
     */
    public function onPostPersist(Problem $problem, LifecycleEventArgs $event)
    {
        foreach ($this->cont->getAgentData()->getOnlineAgents() as $agent) {

            $incidents = 0;
            /** @var TicketChecker $checker */
            $checker = $agent->PermissionsManager->TicketChecker;

            foreach ($problem->tickets as $ticket) {
                if (!$checker->canView($ticket)) {
                    continue;
                }

                if (Ticket::HIDDEN_STATUS_DELETED === $ticket->hidden_status || Ticket::HIDDEN_STATUS_SPAM === $ticket->hidden_status) {
                    continue;
                }

                $incidents++;
            }

            $this->queue[] = array(
                'channel' => self::CHANNEL_NEW,
                'auth' => DpStrings::random(15, Strings::CHARS_KEY),
                'for_person_id' => $agent->id,
                'date_created' => date('Y-m-d H:i:s'),
                'data' => serialize(
                    array(
                        'id' => $problem->id,
                        'title' => $problem->title,
                        'incidents' => $incidents,
                    )
                )
            );
        }

        while (!$this->new_problems->isEmpty()) {
            /** @var Problem $p */
            $p = $this->new_problems->dequeue();
            $filter = new TicketFilter();
            $filter->title = 'Problem #' . $p->id;
            $filter->sys_name = 'problem_' . $p->id;
            $filter->terms = array(
                'type' => TicketSearch::TERM_PROBLEMS,
                'op' => 'is',
                'options' => array(
                    'problems' => array($p->id),
                ),
            );

            $filter->is_global = true;
            $filter->is_enabled = true;
            $event->getEntityManager()->persist($filter);
            $event->getEntityManager()->flush($filter);
        }

        $this->inserts--;

        if (0 === $this->inserts) {
            $this->sendQueue();
        }
    }

    /**
     * @return int
     */
    public function sendQueue()
    {
        if (!$this->queue) {
            return 0;
        }

        $q = $this->queue;
        $this->queue = array();

        $this->conn->batchInsert('client_messages', $q);

        return count($q);
    }
}
