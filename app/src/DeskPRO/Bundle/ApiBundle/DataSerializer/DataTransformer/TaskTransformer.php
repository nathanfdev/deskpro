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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\ApiBundle\DataSerializer\DataTransformer;

use DeskPRO\Bundle\ApiBundle\DataSerializer\DataTransformerRequest;
use DeskPRO\Bundle\ApiBundle\DataSerializer\PropertyTransformer\Callback\CallbackDeferredProperty;
use Doctrine\DBAL\Connection;

/**
 * Class TaskTransformer.
 */
class TaskTransformer extends AbstractDataSerializerTransformer
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var int[]
     */
    private $count_ids;

    /**
     * @var null
     */
    private $commentCounts;

    /**
     * @var null
     */
    private $subtaskCounts;

    /**
     * Constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection    = $connection;
        $this->count_ids     = [];
        $this->commentCounts = null;
        $this->subtaskCounts = null;
    }

    /**
     * {@inheritdoc}
     */
    public function getAutomaticProperties(DataTransformerRequest $transformation_request)
    {
        return [
            'id',
            'title',
            'is_done',
            'percent_complete',
            'date_created',
            'task_type',
            'date_due',
            'date_event_start',
            'date_event_end',
            'creator',
            'visibility',
            'project',
            'list',
            'urgency',
            'linked_items',
            'date_done',
            'display_order',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomProperties(DataTransformerRequest $transformation_request)
    {
        /** @var \DeskPRO\Bundle\AppBundle\Entity\Task $data */
        $data = $transformation_request->getDataToBeTransformed();

        $labels = [];

        if (!empty($data->getLabels())) {
            foreach ($data->getLabels() as $label) {
                $labels[] = $label->getLabel();
            }
        }

        $assignees = $data->getAssigned();

        $grouped = [
            'departments' => [],
            'teams'       => [],
            'agents'      => [],
        ];

        if (!empty($assignees)) {
            foreach ($assignees as $assigned) {
                if (!empty($assigned->getDepartment())) {
                    $grouped['departments'][] = $assigned->getDepartment()->getId();
                } elseif (!empty($assigned->getTeam())) {
                    $grouped['teams'][] = $assigned->getTeam()->getId();
                } else {
                    $grouped['agents'][] = $assigned->getPerson()->getId();
                }
            }
        }

        $id                = $data->getId();
        $this->count_ids[] = $id;

        return [
            'departments'    => $grouped['departments'],
            'teams'          => $grouped['teams'],
            'agents'         => $grouped['agents'],
            'labels'         => $labels,
            'linked_tickets' => $this->getLinkedTickets($data),
            'comment_count'  => new CallbackDeferredProperty(
                [$this, 'getCommentCount'],
                [$id]
            ),
            'subtasks_total' => new CallbackDeferredProperty(
                [$this, 'getSubtasksCount'],
                [$id]
            ),
            'subtasks_done' => new CallbackDeferredProperty(
                [$this, 'getSubtasksDone'],
                [$id]
            ),
        ];
    }

    /**
     * Get the count of the number of comments.
     *
     * @param int $taskId
     *
     * @return int|void
     */
    public function getCommentCount($taskId)
    {
        // Make sure we only execute the query once
        if ($this->commentCounts === null) {
            $this->commentCounts = [];
            $statement           = $this->connection->prepare('SELECT task_id, COUNT(*) AS total
                    FROM task_comments_new
                    WHERE task_id IN (:task_ids)
                    GROUP BY task_id');

            $taskIds = implode(',', $this->count_ids);
            $statement->bindValue('task_ids', $taskIds);

            $statement->execute();

            $result = $statement->fetchAll();

            foreach ($result as $row) {
                $this->commentCounts[$row['task_id']] = (int) $row['total'];
            }
        }

        // the callbacks won't be called until after all of thee "CallbackDeferredProperty" are set
        // which means we now have an array of all of the IDs we will want in $ths->count_ids

        return array_key_exists($taskId, $this->commentCounts) ? $this->commentCounts[$taskId] : 0;
    }

    /**
     * Get the count of subtasks.
     *
     * @param $taskId
     *
     * @return int
     */
    public function getSubtasksCount($taskId)
    {
        $this->querySubtasks();

        return array_key_exists($taskId, $this->subtaskCounts) ? $this->subtaskCounts[$taskId]['total'] : 0;
    }

    /**
     * Get the number of subtasks marked as done.
     *
     * @param $taskId
     *
     * @return int
     */
    public function getSubtasksDone($taskId)
    {
        $this->querySubtasks();

        return array_key_exists($taskId, $this->subtaskCounts) ? $this->subtaskCounts[$taskId]['done'] : 0;
    }

    /**
     * Run the query to retrieve subtasks.
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    private function querySubtasks()
    {
        if ($this->subtaskCounts === null) {
            $this->subtaskCounts = [];
            $statement           = $this->connection->prepare('SELECT task_id, count(task_id) AS total, sum(is_done) AS done
                    FROM task_subtask
                    WHERE task_id IN (:task_ids)
                    GROUP BY task_id');

            $taskIds = implode(',', $this->count_ids);
            $statement->bindValue('task_ids', $taskIds);

            $statement->execute();

            $result = $statement->fetchAll();

            foreach ($result as $row) {
                $this->subtaskCounts[$row['task_id']] = [
                    'done'  => (int) $row['done'],
                    'total' => (int) $row['total'],
                ];
            }
        }
    }

    /**
     * Retrieve the IDs of linked tickets.
     *
     * @param \DeskPRO\Bundle\AppBundle\Entity\Task $data The task
     *
     * @return array
     */
    private function getLinkedTickets($data)
    {
        $tickets = [];

        $linkedItems = $data->getLinkedItems();

        // NB The below may be overkill as we're unlikely to have more than one
        // ticket assigned to a task, and there should be no duplicate links
        if ($linkedItems) {
            foreach ($linkedItems as $linkedItem) {
                $ticket = $linkedItem->getTicket();
                if ($ticket && !in_array($ticket->getId(), $tickets)) {
                    $tickets[] = $ticket->getId();
                }
            }
        }

        return $tickets;
    }
}
