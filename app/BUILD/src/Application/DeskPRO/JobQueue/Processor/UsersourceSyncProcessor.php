<?php

namespace Application\DeskPRO\JobQueue\Processor;

use Application\DeskPRO\Entity\Job;
use Application\DeskPRO\Entity\UsersourceSyncLog;
use Application\DeskPRO\JobQueue\JobQueue;
use Application\DeskPRO\Usersource\Sync\SyncCursor;
use Application\DeskPRO\Usersource\Sync\SyncManager;
use Application\DeskPRO\Usersource\UsersourceManager;
use DeskPRO\Bundle\SystemBundle\SystemAlerts\EventLogger;
use Doctrine\DBAL\Connection;
use Orb\Log\Logger;
use Orb\Util\Env;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UsersourceSyncProcessor extends AbstractJobProcessor
{
    const JOB_TYPE                = 'usersource_sync';
    const ABORT_JOB_TMP_DATA_NAME = 'abort_usersource_sync';

    const MAX_TIME  = 20;
    const MAX_COUNT = 1000;
    public static $max_time;
    public static $count;
    public static $max_memory_usage;
    public static $aborted;

    /**
     * @var UsersourceManager
     */
    private $usersource_manager;

    /**
     * @var SyncManager
     */
    private $sync_manager;

    /**
     * @var JobQueue
     */
    protected $job_queue;

    /**
     * @var EventLogger
     */
    protected $logger;

    public function __construct(
        Connection $connection,
        JobQueue $job_queue,
        UsersourceManager $usersource_manager,
        SyncManager $sync_manager,
        EventLogger $logger
    ) {
        parent::__construct($connection);
        $this->usersource_manager = $usersource_manager;
        $this->sync_manager       = $sync_manager;
        $this->job_queue          = $job_queue;
        $this->logger             = $logger;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'original_start_timestamp' => time(),
            'sync_cursor_location'     => 1,
            'sync_cursor_counter'      => 0,
            'sync_cursor_phase'        => 1,
            'phase_2_count'            => 0,
            'phase_2_usersource'       => null,
            'current_usersource_id'    => null,
            'phase'                    => 1,
        ]);
    }

    public function process(array $data, array $job)
    {
        $this->sync_manager->getSyncHelper()->log(Logger::INFO, 'BOOTING up UsersourceSyncProcessor');
        try {
            static::$max_time = time() + static::MAX_TIME;
            static::$aborted  = false;
            static::$count    = 0;

            // set max memory for the job
            // we use 500MB as an absolute max base memory, and we only use up to 80% of that
            // if the php.ini is set to lower than 500MB, that is ok, we still only use 80% of that.
            $five_hundred_mb = 500 * 1024 * 1024;
            $max_memory      = Env::getMemoryLimit();
            if ($max_memory < 0) { // unlimited
                $max_memory = $five_hundred_mb;
            }
            static::$max_memory_usage = min($max_memory, $five_hundred_mb) * 0.8;

            $this->sync_manager->getSyncHelper()->log(Logger::INFO, 'Set max memory that can be used: '.static::$max_memory_usage);

            if (1 == $data['phase']) {
                $this->sync_manager->getSyncHelper()->log(Logger::INFO, 'Initiating PHASE 1 of the sync');
                $return = $this->runPhaseOne($data);

                return $return;
            } else {
                $this->sync_manager->getSyncHelper()->log(Logger::INFO, 'Initiating PHASE 2 of the sync');
                $return = $this->runPhaseTwo($data);

                return $return;
            }
        } catch (\Exception $e) {
            $this->sync_manager->getSyncHelper()->log(Logger::ERR, 'FATAL SYNC ERROR, aborting ('.get_class($e).' '.$e->getMessage().')');
            $this->abort(true);
            throw $e;
        }
    }

    public function pauseJobCondition(SyncCursor $cursor)
    {
        // we return true if we want to signal to the syncer to pause

        // condition 1: if we allocate 80% or greater of our max memory usage
        if (memory_get_usage() > self::$max_memory_usage) {
            $this->sync_manager->getSyncHelper()->log(Logger::INFO, 'MAX MEMORY HIT, PAUSING: '.memory_get_usage());

            return true;
        }

        // condition 2: if we go over x seconds
        if (time() > self::$max_time) {
            $this->sync_manager->getSyncHelper()->log(Logger::INFO, 'MAX TIME ELAPSED, PAUSING: current time is at ('.time().') but max is ('.self::$max_time.')');

            return true;
        }

        // every 100 iterations check to see if the admin cancelled the job or not
        if ($cursor->getLocation() % 100 === 0) {
            if ($this->sync_manager->isStopSignalPresent()) {
                $this->sync_manager->getSyncHelper()->log(Logger::INFO, 'admin aborted job, aborting');
                static::$aborted = true;
                $this->sync_manager->clearStopSignal();

                return true;
            }
        }

        return false;
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    protected function runPhaseOne(array $data)
    {
        $start_timestamp = $data['original_start_timestamp'];

        $skip_to_usersource_id = $data['current_usersource_id'];
        $cursor                = new SyncCursor($data['sync_cursor_location'], $data['sync_cursor_counter'], $data['sync_cursor_phase']);

        $last_processed_usersource_id = null;
        foreach ($this->getSyncEnabledUsersources() as $usersource) {
            if ($skip_to_usersource_id && $usersource->getId() != $skip_to_usersource_id) {
                // already dealt with this usersource, moving on to one that was paused
                continue;
            }
            $this->sync_manager->getSyncHelper()->log(Logger::INFO, 'processing phase 1, usersource='.$usersource->getId());

            // stop skipping now
            $skip_to_usersource_id        = false;
            $last_processed_usersource_id = $usersource->id;

            if (!$cursor) {
                $cursor = new SyncCursor();
            }

            $isStart = $cursor->getLocation() <= 1;

            // start or resume log
            // force a new log entry if this is not a resume
            $log = $this->sync_manager->getLogToUseDuringSync($usersource, $isStart);

            if ($isStart) {
                $log->startPhaseOne();
            }

            try {
                $this->sync_manager->refreshAll($usersource, $cursor, [$this, 'pauseJobCondition']);

                if (!$cursor->isCompleted() && !static::$aborted) {
                    // time to pause and re-run this phase at this usersource at the cursor location
                    $this->scheduleNextSync(
                        [
                            'phase'                    => 1,
                            'original_start_timestamp' => $start_timestamp,
                            'sync_cursor_location'     => $cursor->getLocation(),
                            'sync_cursor_counter'      => $cursor->getCounter(),
                            'sync_cursor_phase'        => $cursor->getPhase(),
                            'current_usersource_id'    => $last_processed_usersource_id,
                        ],
                        new \DateTime('now + 10 seconds')
                    );

                    // update the log before pausing job
                    $log_count = $cursor->getCounter();
                    if (!$log_count && $cursor->getLocation() > 0) {
                        // counter could be 0 for a long time until "location" is done
                        // this is because phase 1 does not "count"
                        // so if we don't have a count, but we do have a location
                        // we should report that
                        // it will count up until location is maxed, and then
                        // start again at 0 as we "import"
                        $log_count = $cursor->getLocation();
                    }
                    $log->setRecordCount($log_count);
                    $this->sync_manager->saveLog($log);

                    return true;
                }
            } catch (\Exception $e) {
                $this->sync_manager->getSyncHelper()->log(Logger::ERR, 'SYNC ERROR, marking sync as error ('.get_class($e).' '.$e->getMessage().')');
                // log the errors but continue on to the next usersource
                $this->logger->log($e);
                $log->markErrorStatus();
            }

            if (static::$aborted) {
                $this->abort();

                return false;
            }

            // end log
            $log->setRecordCount($cursor->getCounter());
            $log->endPhaseOne();
            $this->sync_manager->saveLog($log);

            $cursor = null;
        }

        // phase 1 is completed now
        // schedule phase 2 for immediate
        $this->scheduleNextSync(
            ['original_start_timestamp' => $start_timestamp, 'phase' => 2],
            new \DateTime('now + 10 seconds')
        );

        return true;
    }

    private function runPhaseTwo(array $data)
    {
        $original_start_timestamp = $data['original_start_timestamp'];
        $start_at_usersource_id   = $data['phase_2_usersource'];
        $count                    = $data['phase_2_count'];

        foreach ($this->getSyncEnabledUsersources() as $usersource) {
            if ($start_at_usersource_id && $usersource->getId() != $start_at_usersource_id) {
                continue;
            }
            $this->sync_manager->getSyncHelper()->log(Logger::INFO, 'processing phase 2, usersource='.$usersource->getId());
            // stop skip
            $start_at_usersource_id       = null;
            $last_processed_usersource_id = $usersource->getId();
            $this_usersource_errors       = 0;

            $associations = $this->usersource_manager->findAssociationsUpdatedBefore(
                $usersource,
                $ts = new \DateTime(sprintf('@%s', $original_start_timestamp))
            );

            $log                 = $this->sync_manager->getLogToUseDuringSync($usersource);
            $is_start_of_phase_2 = $count == 0;
            if ($is_start_of_phase_2) {
                $log->startPhaseTwo();
            }

            $had_to_break                 = false;
            $count_processed_associations = 0;
            $count_total_associations     = count($associations);
            foreach ($associations as $association) {
                /* @var \Application\DeskPRO\Entity\PersonUsersourceAssoc $association */
                $identity = $association->getIdentity();
                ++$count_processed_associations;
                try {
                    if ($this->sync_manager->refreshIdentity($association->getUsersource(), $identity)) {
                        ++$count;
                        $log->incrementRecordCount();
                    } else {
                        ++$count;
                        // there was a problem, but if we don't update the association it will
                        // loop the sync forever
                        $this->sync_manager->getSyncHelper()->log(Logger::INFO, 'failed to find "'.$identity.'" on remote usersource, updating association updatedAt time anyway so we do not loop the sync, usersource='.$usersource->getId());
                        $association->setDateUpdated(new \DateTime());
                        $this->sync_manager->getSyncHelper()->persistAndFlushEntity($association);
                    }
                } catch (\Exception $e) {
                    $this->sync_manager->getSyncHelper()->log(Logger::ERR, 'an exception was thrown when refresh "'.$identity.'" from remote usersource, usersource='.$usersource->getId());
                    // log the error, but continue processing
                    ++$this_usersource_errors;
                    $this->logger->log($e);
                    if ($this_usersource_errors > 10) {
                        $log->markErrorStatus();
                        $this->sync_manager->saveLog($log);
                        break;
                    }
                }

                if ($this->pauseJobCondition(new SyncCursor())) {
                    $had_to_break = true;
                    break;
                }
            }

            $this->sync_manager->getSyncHelper()->log(Logger::INFO, 'processed '.$count_processed_associations.' of '.$count_total_associations.' associations for usersource='.$usersource->getId());

            if ($had_to_break) {
                // phase 2 needs another

                if (static::$aborted) {
                    $this->abort();

                    return false;
                }

                $this->sync_manager->saveLog($log);

                $this->scheduleNextSync(
                    [
                        'phase'                    => 2,
                        'phase_2_count'            => $count,
                        'phase_2_usersource'       => $last_processed_usersource_id,
                        'original_start_timestamp' => $data['original_start_timestamp'],
                    ],
                    new \DateTime('now + 10 seconds')
                );

                return true;
            }

            $log->endPhaseTwo();
            if (UsersourceSyncLog::STATUS_ERROR != $log->getStatus()) {
                $log->markCompletedStatus();
            }
            $this->sync_manager->saveLog($log);
            $count = 0;
        }

        // phase 2 is complete
        // reschedule job one for 24 hours from now
        $this->scheduleNextSync(
            ['phase' => 1],
            new \DateTime('tomorrow 1am')
        );

        return true;
    }

    protected function scheduleNextSync(array $data, \DateTime $next_attempt = null)
    {
        $this->job_queue->addJob(
            new Job(self::JOB_TYPE, $data),
            $next_attempt
        );
    }

    /**
     * @return \Application\DeskPRO\Entity\Usersource[]|\Application\DeskPRO\Usersource\UsersourceCollection
     */
    protected function getSyncEnabledUsersources()
    {
        // must be an enabled usersource AND sync must be enabled as well
        return $this->usersource_manager->getAll()->mustBeEnabled()->mustHaveSyncEnabled();
    }

    protected function abort($error = false)
    {
        foreach ($this->getSyncEnabledUsersources() as $us) {
            $log = $this->sync_manager->getLogToUseDuringSync($us);
            if ($log->getId()) {
                if (!$log->getPhaseOneTimeInSeconds()) {
                    $log->endPhaseOne();
                }
                if (!$log->getPhaseTwoTimeInSeconds()) {
                    $log->endPhaseTwo();
                }
                if ($error) {
                    $log->markErrorStatus();
                } else {
                    $log->markCancelledStatus();
                }
                $this->sync_manager->saveLog($log);
            }
        }
    }
}
