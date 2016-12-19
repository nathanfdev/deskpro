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

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\Tasks;

use DeskPRO\Bundle\AppBundle\Entity\Task as TaskEntity;
use DeskPRO\Bundle\AppBundle\Serializer\Deferred\CallbackDeferredProperty;
use DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\AbstractEntityHandler;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Tasks\Task as TaskModel;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use Doctrine\DBAL\Connection;

/**
 * Class TaskHandler.
 */
class TaskHandler extends AbstractEntityHandler
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var int[]
     */
    private $ids;

    /**
     * @var array
     */
    private $commentCounts;

    /**
     * @var array
     */
    private $subtaskCounts;

    /**
     * Constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     *
     * @param TaskEntity $entity
     */
    protected function createModel($entity, SideloadSerializationContext $context)
    {
        $id             = $entity->getId();
        $this->ids[]    = $id;
        $comment_count  = new CallbackDeferredProperty([$this, 'getCommentCount'], [$id]);
        $subtasks_total = new CallbackDeferredProperty([$this, 'getSubtasksCount'], [$id]);
        $subtasks_done  = new CallbackDeferredProperty([$this, 'getSubtasksDone'], [$id]);

        return new TaskModel($entity, $comment_count, $subtasks_total, $subtasks_done);
    }

    /**
     * {@inheritdoc}
     */
    public static function getClassNames()
    {
        return TaskEntity::class;
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
            $statement           = $this->connection->prepare(
                'SELECT task_id, COUNT(*) AS total
                    FROM task_comments_new
                    WHERE task_id IN (:task_ids)
                    GROUP BY task_id'
            );

            $taskIds = implode(',', $this->ids);
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
            $statement           = $this->connection->prepare(
                'SELECT task_id, count(task_id) AS total, sum(is_done) AS done
                    FROM task_subtask
                    WHERE task_id IN (:task_ids)
                    GROUP BY task_id'
            );

            $taskIds = implode(',', $this->ids);
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
}
