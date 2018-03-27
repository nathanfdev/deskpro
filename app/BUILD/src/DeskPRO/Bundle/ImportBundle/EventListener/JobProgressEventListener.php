<?php

namespace DeskPRO\Bundle\ImportBundle\EventListener;

use Application\DeskPRO\Entity\Job;
use DeskPRO\Bundle\ImportBundle\Event\ProgressEvent;
use DeskPRO\Bundle\ImportBundle\Writer\EntityHandler\EntityHandlerRegistry;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class JobProgressEventListener.
 */
class JobProgressEventListener implements EventSubscriberInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var EntityHandlerRegistry
     */
    private $entityHandlerRegistry;

    /**
     * @var int
     */
    private $jobId;

    /**
     * @var array
     */
    private $importedModelCounts = [];

    /**
     * Constructor.
     *
     * @param EntityManager         $em
     * @param EntityHandlerRegistry $entityHandlerRegistry
     */
    public function __construct(EntityManager $em, EntityHandlerRegistry $entityHandlerRegistry)
    {
        $this->em                    = $em;
        $this->entityHandlerRegistry = $entityHandlerRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ProgressEvent::POST_MODEL_IMPORT => ['onPostModelImport'],
            ProgressEvent::POST_IMPORT       => ['onPostImport'],
            ProgressEvent::POST_BATCH_APPLY  => ['onPostBatchApply'],
            ProgressEvent::POST_APPLY        => ['onPostApply'],
            ProgressEvent::FINISH            => ['onFinish'],
            ProgressEvent::CLEAN             => ['onClean'],
        ];
    }

    /**
     * @param int $jobId
     */
    public function setJob($jobId)
    {
        $this->jobId = $jobId;
    }

    /**
     * @internal
     *
     * @param ProgressEvent $event
     */
    public function onPostModelImport(ProgressEvent $event)
    {
        if (!isset($this->importedModelCounts[$event->getModelClass()])) {
            $this->importedModelCounts[$event->getModelClass()] = 0;
        }

        ++$this->importedModelCounts[$event->getModelClass()];
        if ($this->importedModelCounts[$event->getModelClass()] % 100 === 0) {
            $this->flushImportedCounts($event->getModelClass());
        }
    }

    /**
     * @internal
     *
     * @param ProgressEvent $event
     */
    public function onPostImport(ProgressEvent $event)
    {
        $this->flushImportedCounts($event->getModelClass());
        if (!$job = $this->getJob()) {
            return;
        }

        $type     = $this->entityHandlerRegistry->getTypeByModelClass($event->getModelClass());
        $imported = $job->getDataKey('imported_steps', []);
        if (!in_array($type, $imported)) {
            $imported[] = $type;

            $job->setStatus(Job::STATUS_PROCESSING);
            $job->setDataKey('imported_steps', $imported);

            $this->em->persist($job);
            $this->em->flush();
        }
    }

    /**
     * @internal
     *
     * @param ProgressEvent $event
     */
    public function onPostBatchApply(ProgressEvent $event)
    {
        if (!$job = $this->getJob()) {
            return;
        }

        $type    = $this->entityHandlerRegistry->getTypeByModelClass($event->getModelClass());
        $counts  = $job->getDataKey('applied_counts', []);
        $options = $event->getOptions();

        if (!isset($options['count']) || $options['count'] < 1) {
            return;
        }
        if (!isset($counts[$type])) {
            $counts[$type] = 0;
        }

        $counts[$type] += $options['count'];

        $job->setStatus(Job::STATUS_PROCESSING);
        $job->setDataKey('applied_counts', $counts);

        $this->em->persist($job);
        $this->em->flush();
    }

    /**
     * @internal
     *
     * @param ProgressEvent $event
     */
    public function onPostApply(ProgressEvent $event)
    {
        if (!$job = $this->getJob()) {
            return;
        }

        $type    = $this->entityHandlerRegistry->getTypeByModelClass($event->getModelClass());
        $applied = $job->getDataKey('applied_steps', []);
        if (!in_array($type, $applied)) {
            $applied[] = $type;

            $job->setStatus(Job::STATUS_PROCESSING);
            $job->setDataKey('applied_steps', $applied);

            $this->em->persist($job);
            $this->em->flush();
        }
    }

    /**
     * @internal
     */
    public function onFinish()
    {
        if (!$job = $this->getJob()) {
            return;
        }

        $job->setStatus(Job::STATUS_COMPLETE);

        $this->em->persist($job);
        $this->em->flush();
    }

    /**
     * @internal
     */
    public function onClean()
    {
        if (!$job = $this->getJob()) {
            return;
        }

        $job->setDataKey('imported_steps', []);
        $job->setDataKey('imported_counts', []);
        $job->setDataKey('applied_steps', []);
        $job->setDataKey('applied_counts', []);
        $job->setLog('');
        $job->setDateCreated(new \DateTime());
        $job->setStatus(Job::STATUS_WAITING);

        $this->em->persist($job);
        $this->em->flush();
    }

    /**
     * @return Job|null
     */
    private function getJob()
    {
        $this->em->clear(Job::class);

        return $this->jobId ? $this->em->getRepository(Job::class)->find($this->jobId) : null;
    }

    /**
     * @param string $modelClass
     */
    private function flushImportedCounts($modelClass)
    {
        if (!$job = $this->getJob()) {
            return;
        }
        if (!isset($this->importedModelCounts[$modelClass]) || $this->importedModelCounts[$modelClass] < 1) {
            return;
        }

        $type   = $this->entityHandlerRegistry->getTypeByModelClass($modelClass);
        $counts = $job->getDataKey('imported_counts', []);

        if (!isset($counts[$type])) {
            $counts[$type] = 0;
        }

        $counts[$type] += $this->importedModelCounts[$modelClass];

        $this->importedModelCounts[$modelClass] = 0;

        $job->setStatus(Job::STATUS_PROCESSING);
        $job->setDataKey('imported_counts', $counts);

        $this->em->persist($job);
        $this->em->flush();
    }
}
