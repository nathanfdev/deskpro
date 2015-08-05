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
     * @var \SplQueue
     */
    protected $updates;

    /**
     * @var \SplQueue
     */
    protected $inserts;

    /**
     * @var array
     */
    protected $queue = array();

    public function __construct(DeskproContainer $container)
    {
        $this->updates = new \SplQueue();
        $this->inserts = new \SplQueue();
        $this->conn = $container->getEm()->getConnection();
    }

    /**
     * @param Problem $problem
     */
    public function onPreUpdate(Problem $problem, PreUpdateEventArgs $event)
    {
//        $this->queued_updates[spl_object_hash($problem)] = $problem;

//        foreach ($event->getEntityChangeSet() as $field => $change) {
//            $old[$field] = $change[0];
//        }
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
     * @param Problem $problem
     */
    public function onPostUpdate(Problem $problem)
    {
        while (!$this->updates->isEmpty()) {
            $p = $this->updates->dequeue();
            $this->queue[] = array(
                'channel' => self::CHANNEL_UPDATE,
                'auth' => DpStrings::random(15, Strings::CHARS_KEY),
                'date_created' => date('Y-m-d H:i:s'),
                'data' => serialize(
                    array(
                        'problem_id' => $problem->id,
                        'problem_title' => $problem->title,
                        'incidents' => $problem->tickets->count(),
                        'is_open' => $problem->is_open,
                    )
                )
            );
        }

        if ($this->updates->isEmpty() && $this->queue) {
            // todo?
        }
    }

    /**
     * @param Problem $problem
     */
    public function onPostPersist(Problem $problem)
    {
        while (!$this->inserts->isEmpty()) {
            $p = $this->inserts->dequeue();
            $this->queue[] = array(
                'channel' => self::CHANNEL_NEW,
                'auth' => DpStrings::random(15, Strings::CHARS_KEY),
                'date_created' => date('Y-m-d H:i:s'),
                'data' => serialize(
                    array(
                        'problem_id' => $problem->id,
                        'problem_title' => $problem->title,
                        'incidents' => $problem->tickets->count(),
                    )
                )
            );
        }

        if ($this->inserts->isEmpty() && $this->queue) {
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
