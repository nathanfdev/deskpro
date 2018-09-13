<?php

namespace DeskPRO\Bundle\VoiceBundle\TaskRouter\StorageAdapter;

use DeskPRO\Bundle\VoiceBundle\Entity\Task as TaskEntity;
use DeskPRO\Bundle\VoiceBundle\Entity\TaskQueue as TaskQueueEntity;
use DeskPRO\Bundle\VoiceBundle\Entity\Worker as WorkerEntity;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Task;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\TaskQueue;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Worker;
use Doctrine\ORM\EntityManager;

/**
 * Class DbAdapter.
 */
class DbAdapter implements StorageAdapterInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveTasks()
    {
        $entities = $this->em->getRepository(TaskEntity::class)->findBy([
            'status' => Task::STATUS_PENDING,
        ]);

        $tasks = [];
        foreach ($entities as $entity) {
            $tasks[] = $this->transformToTaskModel($entity);
        }

        return $tasks;
    }

    /**
     * {@inheritdoc}
     */
    public function getTask($id)
    {
        $entity = $this->em->getRepository(TaskEntity::class)->find($id);
        if ($entity) {
            return $this->transformToTaskModel($entity);
        }

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function saveTask(Task $task)
    {
        $entity = null;
        if ($task->getId()) {
            $entity = $this->em->getRepository(TaskEntity::class)->find($task->getId());
        }

        if (!$entity) {
            $entity = new TaskEntity();
        }

        $entity
            ->setChannel($task->getChannel())
            ->setPriority($task->getPriority())
            ->setWorkers($task->getWorkerIds())
            ->setAcceptedWorker($task->getAcceptedWorkerId())
            ->setTimeout($task->getTimeout())
            ->setStatus($task->getStatus())
            ->setStatusReason($task->getStatusReason())
            ->setRejectedBy($task->getRejectedBy())
            ->setDateCreated($task->getDateCreated())
            ->setAttributes($task->getAttributes())
        ;

        $this->em->persist($entity);
        $this->em->flush();

        $task->setId($entity->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function getOnlineWorkersByType($type)
    {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('w')
            ->from(WorkerEntity::class, 'w')
            ->where(
                'w.activity = :activity',
                'w.type = :type',
                'w.dateLastActive >= :date_last_active'
            )
            ->setParameter('activity', Worker::ACTIVITY_IDLE)
            ->setParameter('type', $type)
            ->setParameter('date_last_active', new \DateTime('-10 seconds'))
        ;

        $entities = $qb->getQuery()->getResult();
        $workers  = [];
        foreach ($entities as $entity) {
            $workers[] = $this->transformToWorkerModel($entity);
        }

        return $workers;
    }

    /**
     * {@inheritdoc}
     */
    public function getWorkerByType($type, $typeId)
    {
        $entity = $this->em->getRepository(WorkerEntity::class)->findOneBy([
            'type'   => $type,
            'typeId' => $typeId,
        ]);
        if ($entity) {
            return $this->transformToWorkerModel($entity);
        }

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function getWorkersByType($type, array $typeIds)
    {
        $entities = $this->em->getRepository(WorkerEntity::class)->findBy([
            'type'   => $type,
            'typeId' => $typeIds,
        ]);

        $workers = [];
        foreach ($entities as $entity) {
            $workers[] = $this->transformToWorkerModel($entity);
        }

        return $workers;
    }

    /**
     * {@inheritdoc}
     */
    public function getWorker($id)
    {
        $entity = $this->em->getRepository(WorkerEntity::class)->find($id);
        if ($entity) {
            return $this->transformToWorkerModel($entity);
        }

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function getWorkers(array $ids)
    {
        $entities = $this->em->getRepository(WorkerEntity::class)->findBy(['id' => $ids]);
        $workers  = [];
        foreach ($entities as $entity) {
            $workers[] = $this->transformToWorkerModel($entity);
        }

        return $workers;
    }

    /**
     * {@inheritdoc}
     */
    public function saveWorker(Worker $worker)
    {
        $entity = null;
        if ($worker->getId()) {
            $entity = $this->em->getRepository(WorkerEntity::class)->find($worker->getId());
        }

        if (!$entity) {
            $entity = new WorkerEntity();
        }

        $entity
            ->setType($worker->getType())
            ->setTypeId($worker->getTypeId())
            ->setActivity($worker->getActivity())
            ->setDateLastActive($worker->getDateLastActive())
            ->setAttributes($worker->getAttributes())
        ;

        $this->em->persist($entity);
        $this->em->flush();

        $worker->setId($entity->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function removeWorker($type, $typeId)
    {
        $entity = $this->getWorkerByType($type, $typeId);
        if (!$entity) {
            return;
        }

        $this->em->remove($entity);
        $this->em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getTaskQueue($type, $typeId)
    {
        $entity = $this->em->getRepository(TaskQueueEntity::class)->findOneBy([
            'type'   => $type,
            'typeId' => $typeId,
        ]);

        if ($entity) {
            return $this->transformToTaskQueueModel($entity);
        }

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function saveTaskQueue(TaskQueue $taskQueue)
    {
        $entity = null;
        if ($taskQueue->getId()) {
            $entity = $this->em->getRepository(TaskQueueEntity::class)->find($taskQueue->getId());
        }

        if (!$entity) {
            $entity = new TaskQueueEntity();
        }

        $entity->setType($taskQueue->getType());
        $entity->setTypeId($taskQueue->getTypeId());
        $entity->setAttributes($taskQueue->getAttributes());

        $this->em->persist($entity);
        $this->em->flush();

        $taskQueue->setId($entity->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function removeTaskQueue($type, $typeId)
    {
        $entity = $this->getTaskQueue($type, $typeId);
        if (!$entity) {
            return;
        }

        $this->em->remove($entity);
        $this->em->flush();
    }

    /**
     * @param TaskEntity $entity
     *
     * @return Task
     */
    private function transformToTaskModel(TaskEntity $entity)
    {
        $task = new Task();
        $task
            ->setId($entity->getId())
            ->setChannel($entity->getChannel())
            ->setPriority($entity->getPriority())
            ->setWorkersIds($entity->getWorkers())
            ->setAcceptedWorkerId($entity->getAcceptedWorker())
            ->setTimeout($entity->getTimeout())
            ->setStatus($entity->getStatus())
            ->setStatusReason($entity->getStatusReason())
            ->setRejectedBy($entity->getRejectedBy())
            ->setDateCreated($entity->getDateCreated())
            ->setAttributes($entity->getAttributes())
        ;

        return $task;
    }

    /**
     * @param WorkerEntity $entity
     *
     * @return Worker
     */
    private function transformToWorkerModel(WorkerEntity $entity)
    {
        $worker = new Worker();
        $worker
            ->setId($entity->getId())
            ->setType($entity->getType())
            ->setTypeId($entity->getTypeId())
            ->setActivity($entity->getActivity())
            ->setDateLastActive($entity->getDateLastActive())
            ->setAttributes($entity->getAttributes())
        ;

        return $worker;
    }

    /**
     * @param TaskQueueEntity $entity
     *
     * @return TaskQueue
     */
    private function transformToTaskQueueModel(TaskQueueEntity $entity)
    {
        $taskQueue = new TaskQueue();
        $taskQueue
            ->setId($entity->getId())
            ->setType($entity->getType())
            ->setTypeId($entity->getTypeId())
            ->setAttributes($entity->getAttributes())
        ;

        return $taskQueue;
    }
}
