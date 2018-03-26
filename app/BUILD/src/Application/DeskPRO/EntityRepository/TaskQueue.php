<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

class TaskQueue extends AbstractEntityRepository
{
    public function getAllTasks($newest_first = true)
    {
        return $this->getEntityManager()->createQuery('
            SELECT tq
            FROM DeskPRO:TaskQueue tq
            ORDER BY tq.date_runnable '.($newest_first ? 'DESC' : 'ASC').'
        ')->execute();
    }

    public function getRunningTask()
    {
        return $this->getEntityManager()->createQuery("
            SELECT tq
            FROM DeskPRO:TaskQueue tq
            WHERE tq.status = 'running'
            ORDER BY tq.date_runnable
        ")->setMaxResults(1)->getOneOrNullResult();
    }

    public function getNextQueuedTask()
    {
        return $this->getEntityManager()->createQuery("
            SELECT tq
            FROM DeskPRO:TaskQueue tq
            WHERE tq.status = 'queued' AND tq.date_runnable < ?0
            ORDER BY tq.date_runnable
        ")->setMaxResults(1)->setParameters([date('Y-m-d H:i:s')])->getOneOrNullResult();
    }

    public function getRunnableTask()
    {
        $task = $this->getRunningTask();
        if ($task) {
            return $task;
        }

        $task = $this->getNextQueuedTask();
        if ($task) {
            return $task;
        }

        return;
    }

    public function countTasksBefore(\Application\DeskPRO\Entity\TaskQueue $task)
    {
        $count = $this->getEntityManager()->getConnection()->fetchColumn("
            SELECT COUNT(*)
            FROM task_queue
            WHERE status NOT IN ('completed', 'errored')
                AND date_runnable <= ?
        ", [$task->date_runnable->format('Y-m-d H:i:s')]);

        return $count - 1; // -1 takes out this one
    }

    public function getTasksInGroup($group, $include_ended = false)
    {
        return $this->getEntityManager()->createQuery('
            SELECT tq
            FROM DeskPRO:TaskQueue tq
            WHERE tq.task_group = ?0
                '.(!$include_ended ? "AND tq.status NOT IN ('completed', 'errored')" : '').'
            ORDER BY tq.date_runnable
        ')->execute([$group]);
    }

    public function getPendingTasks()
    {
        return $this->getEntityManager()->createQuery("
            SELECT tq
            FROM DeskPRO:TaskQueue tq
            WHERE tq.status NOT IN ('completed', 'errored')
            ORDER BY tq.date_runnable
        ")->execute();
    }

    public function enqueueTask($runner_class, array $data = [], $task_group = null)
    {
        $task               = new \Application\DeskPRO\Entity\TaskQueue();
        $task->runner_class = $runner_class;
        $task->task_data    = $data;
        $task->task_group   = $task_group;

        $em = $this->getEntityManager();
        $em->persist($task);
        $em->flush();

        return $task;
    }
}
