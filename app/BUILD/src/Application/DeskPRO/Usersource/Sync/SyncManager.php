<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Usersource\Sync;

use Application\DeskPRO\Entity\Job;
use Application\DeskPRO\Entity\TmpData;
use Application\DeskPRO\Entity\Usersource;
use Application\DeskPRO\Entity\UsersourceSyncLog;
use Application\DeskPRO\EntityRepository\UsersourceSyncLog as UsersourceSyncLogRepo;
use Application\DeskPRO\JobQueue\JobQueue;
use Application\DeskPRO\JobQueue\Processor\UsersourceSyncProcessor;
use Application\DeskPRO\Usersource\UsersourceManager;
use Doctrine\ORM\EntityManager;

/**
 * The SyncManager is an aggregate of all of the syncers, but it is itself a "master" syncer.
 */
class SyncManager implements SyncerInterface
{
    /**
     * @var SyncerInterface[]
     */
    protected $syncers;

    /**
     * @var UsersourceSyncLogRepo
     */
    private $log_repo;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var JobQueue
     */
    private $queue;

    /**
     * @var UsersourceManager
     */
    private $um;

    /**
     * @var SyncerHelper
     */
    private $sync_helper;

    public function __construct(array $syncers, EntityManager $em, JobQueue $queue, UsersourceManager $um, SyncerHelper $sync_helper)
    {
        $this->syncers     = $syncers;
        $this->log_repo    = $em->getRepository('DeskPRO:UsersourceSyncLog');
        $this->em          = $em;
        $this->queue       = $queue;
        $this->um          = $um;
        $this->sync_helper = $sync_helper;
    }

    /**
     * @param Usersource $usersource
     *
     * @return SyncerInterface
     */
    public function getSyncerForUsersource(Usersource $usersource)
    {
        return $this->getSyncerForAdapterClass($usersource->getSourceType());
    }

    /**
     * @param string $usersource_adapter_class_name
     *
     * @return SyncerInterface
     */
    public function getSyncerForAdapterClass($usersource_adapter_class_name)
    {
        foreach ($this->syncers as $syncer) {
            if ($syncer->supportsUsersourceAdapter($usersource_adapter_class_name)) {
                return $syncer;
            }
        }
    }

    public function refreshIdentity(Usersource $usersource, $identity_or_email)
    {
        if ($this->isSyncable($usersource) && $syncer = $this->getSyncerForUsersource($usersource)) {
            return $syncer->refreshIdentity($usersource, $identity_or_email);
        }

        return false;
    }

    public function refreshAll(Usersource $usersource, SyncCursor $cursor, $pause_check)
    {
        if ($this->isSyncable($usersource) && $syncer = $this->getSyncerForUsersource($usersource)) {
            return $syncer->refreshAll($usersource, $cursor, $pause_check);
        }

        // not supported
        $cursor->markCompleted(); // mark it complete so that clients know to move on and not pause
        return;
    }

    public function supportsUsersourceAdapter($adapter_class)
    {
        foreach ($this->syncers as $syncer) {
            if ($syncer->supportsUsersourceAdapter($adapter_class)) {
                return true;
            }
        }

        return false;
    }

    public function supportsUsersource(Usersource $usersource)
    {
        if (!$this->isSyncable($usersource)) {
            return false;
        }

        foreach ($this->syncers as $syncer) {
            if ($syncer->supportsUsersource($usersource)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Usersource $usersource
     *
     * @return UsersourceSyncLog
     */
    public function getMostRecentLog(Usersource $usersource)
    {
        return $this->log_repo->getLastStartedLogForUsersource($usersource);
    }

    /**
     * @param Usersource $usersource
     *
     * @return UsersourceSyncLog|null
     */
    public function getLogToUseDuringSync(Usersource $usersource, $force_create = false)
    {
        if ($force_create) {
            return $this->log_repo->createNewLog($usersource);
        }

        return $this->log_repo->getOrCreateLogInProgressForUsersource($usersource);
    }

    public function saveLog(UsersourceSyncLog $log)
    {
        $this->log_repo->save($log);
    }

    public function signalJobToStop()
    {
        $tmp = new TmpData();
        // if any data with this name is in the tmp_data table, the job will stop next time it checks
        $tmp->name = UsersourceSyncProcessor::ABORT_JOB_TMP_DATA_NAME;
        $this->em->persist($tmp);
        $this->em->flush();
    }

    /**
     * If this returns true, the running sync job should stop immediately.
     *
     * @return bool
     */
    public function isStopSignalPresent()
    {
        $signal = $this->em->getRepository('DeskPRO:TmpData')->findBy(['name' => UsersourceSyncProcessor::ABORT_JOB_TMP_DATA_NAME]);

        return (bool) $signal;
    }

    public function clearStopSignal()
    {
        $this->em->createQuery('DELETE DeskPRO:TmpData d WHERE d.name = :dname')
            ->setParameter('dname', UsersourceSyncProcessor::ABORT_JOB_TMP_DATA_NAME)
            ->execute();
    }

    /**
     * @param Usersource $usersource
     *
     * @return bool
     */
    public function isSyncable(Usersource $usersource)
    {
        return $usersource->isEnabled() && $usersource->isSyncEnabled();
    }

    /**
     * This checks to see if there are any usersource sync jobs in a "running" state.
     *
     * @return \DateTime|null
     */
    public function getNextScheduledSyncDate()
    {
        if ($waiting = $this->getNextScheduledSyncJob()) {
            return $waiting->date_next_try;
        }

        return;
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return \Application\DeskPRO\Entity\Job
     */
    public function getNextScheduledSyncJob()
    {
        return $this->em->createQuery(
            '
            SELECT j FROM DeskPRO:Job j
            WHERE j.type = :sync
            AND j.status = :waiting
            ORDER BY j.date_next_try ASC
            '
        )->setMaxResults(1)
            ->setParameter('sync', UsersourceSyncProcessor::JOB_TYPE)
            ->setParameter('waiting', Job::STATUS_WAITING)
            ->getOneOrNullResult();
    }

    /**
     * This checks to see if there are any usersource sync jobs in a "running" state
     * or if it is scheduled to run soon.
     *
     * @return bool
     */
    public function isSyncRunning()
    {
        /** @var \Application\DeskPRO\Entity\Job $running */
        $running = $this->em->createQuery(
            '
            SELECT j FROM DeskPRO:Job j
            WHERE j.type = :sync
            AND j.status IN (:running)
            ORDER BY j.date_next_try ASC
            '
        )->setMaxResults(1)
            ->setParameter('sync', UsersourceSyncProcessor::JOB_TYPE)
            ->setParameter('running', [
            Job::STATUS_PROCESSING,
            Job::STATUS_RESERVED,
        ])->getOneOrNullResult();

        if ($running) {
            return true;
        }

        // if this next sync job starts within 90 seconds, consider it started
        // for all intents and purposes (UI reporting is the purpose of this method).
        $next = $this->getNextScheduledSyncJob();
        if ($next && $next->date_next_try->getTimestamp() < (time() + 90)) {
            return true;
        }

        return false;
    }

    public function abortSyncJobs()
    {
        $running_or_waiting_jobs = $this->em->createQuery(
            '
            SELECT j FROM DeskPRO:Job j
            WHERE j.type = :sync
            AND j.status IN (:running)
            ORDER BY j.date_next_try ASC
            '
        )->setMaxResults(1)
            ->setParameter('sync', UsersourceSyncProcessor::JOB_TYPE)
            ->setParameter('running', [
                Job::STATUS_WAITING,
                Job::STATUS_PROCESSING,
                Job::STATUS_RESERVED,
            ])->getResult();

        foreach ($running_or_waiting_jobs as $job_to_abort) {
            $this->queue->abort($job_to_abort);
        }

        foreach ($this->um->getAll()->mustBeEnabled()->mustHaveSyncEnabled() as $us) {
            $log = $this->getLogToUseDuringSync($us);
            if ($log->getId()) {
                if (!$log->getPhaseOneTimeInSeconds()) {
                    $log->endPhaseOne();
                }
                if (!$log->getPhaseTwoTimeInSeconds()) {
                    $log->endPhaseTwo();
                }
                $log->markCancelledStatus();
                $this->saveLog($log);
            }
        }
    }

    public function rescheduleSync(\DateTime $date)
    {
        if ($job = $this->getNextScheduledSyncJob()) {
            $job->date_next_try = new \DateTime();
            $this->em->flush();
        } else {
            $this->queue->add(UsersourceSyncProcessor::JOB_TYPE, []);
        }
    }

    /**
     * @return EntityManager
     */
    public function getEm()
    {
        return $this->em;
    }

    /**
     * @return SyncerHelper
     */
    public function getSyncHelper()
    {
        return $this->sync_helper;
    }
}
