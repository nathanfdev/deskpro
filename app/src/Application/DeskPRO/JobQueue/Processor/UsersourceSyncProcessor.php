<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace Application\DeskPRO\JobQueue\Processor;


use Application\DeskPRO\Entity\Job;
use Application\DeskPRO\Entity\UsersourceSyncLog;
use Application\DeskPRO\JobQueue\JobQueue;
use Application\DeskPRO\ORM\EntityManager;
use Application\DeskPRO\Usersource\Sync\SyncCursor;
use Application\DeskPRO\Usersource\Sync\SyncException;
use Application\DeskPRO\Usersource\Sync\SyncManager;
use Application\DeskPRO\Usersource\UsersourceManager;
use DeskPRO\Kernel\KernelErrorHandler;
use Doctrine\DBAL\Connection;
use Orb\Util\Env;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UsersourceSyncProcessor extends AbstractJobProcessor
{
    const JOB_TYPE = 'usersource_sync';
    const ABORT_JOB_TMP_DATA_NAME = 'abort_usersource_sync';

    const MAX_TIME = 20;
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

    public function __construct(
        Connection $connection,
        JobQueue $job_queue,
        UsersourceManager $usersource_manager,
        SyncManager $sync_manager
    )
    {
        parent::__construct($connection);
        $this->usersource_manager = $usersource_manager;
        $this->sync_manager = $sync_manager;
        $this->job_queue = $job_queue;
    }

    public function setDataOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'original_start_timestamp' => time(),
                'sync_cursor_location' => 1,
                'sync_cursor_counter' => 0,
                'sync_cursor_phase' => 1,
                'phase_2_count' => 0,
                'phase_2_usersource' => null,
                'current_usersource_id' => null,
                'phase' => 1
            )
        );
    }

    public function process(array $data, array $job)
    {
        try {
            static::$max_time = time() + static::MAX_TIME;
            static::$aborted = false;
            static::$count = 0;

            static::$max_memory_usage = min(Env::getMemoryLimit(), 500 * 1024 * 1024) * 0.8;
            if (1 == $data['phase']) {
                $return = $this->runPhaseOne($data);
                return $return;
            } else {
                $return = $this->runPhaseTwo($data);
                return $return;
            }
        } catch (\Exception $e) {
            $this->abort(true);
            throw $e;
        }
    }

    public function pauseJobCondition(SyncCursor $cursor)
    {
        // we return true if we want to signal to the syncer to pause

        // condition 1: if we allocate 80% or greater of our max memory usage
        if (memory_get_usage() > UsersourceSyncProcessor::$max_memory_usage) {
            return true;
        }

        // considtion 2: if we go over x seconds
        if (time() > UsersourceSyncProcessor::$max_time) {
            return true;
        }

        // every 100 iterations check to see if the admin cancelled the job or not
        if ($cursor->getLocation() % 100 === 0) {
            if ($this->sync_manager->isStopSignalPresent()) {
                static::$aborted = true;
                $this->sync_manager->clearStopSignal();
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $data
     * @return bool
     */
    protected function runPhaseOne(array $data)
    {
        $start_timestamp = $data['original_start_timestamp'];

        $skip_to_usersource_id = $data['current_usersource_id'];
        $cursor = new SyncCursor($data['sync_cursor_location'], $data['sync_cursor_counter'], $data['sync_cursor_phase']);

        $last_processed_usersource_id = null;
        foreach ($this->getSyncEnabledUsersources() as $usersource) {
            if ($skip_to_usersource_id && $usersource->getId() != $skip_to_usersource_id) {
                // already dealt with this usersource, moving on to one that was paused
                continue;
            }

            // stop skipping now
            $skip_to_usersource_id = false;
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
                $this->sync_manager->refreshAll($usersource, $cursor, array($this, 'pauseJobCondition'));

                if (!$cursor->isCompleted() && !static::$aborted) {
                    // time to pause and re-run this phase at this usersource at the cursor location
                    $this->scheduleNextSync(
                        array(
                            'phase' => 1,
                            'original_start_timestamp' => $start_timestamp,
                            'sync_cursor_location' => $cursor->getLocation(),
                            'sync_cursor_counter' => $cursor->getCounter(),
                            'sync_cursor_phase' => $cursor->getPhase(),
                            'current_usersource_id' => $last_processed_usersource_id
                        ),
                        new \DateTime('now + 20 seconds')
                    );

                    // update the log before pausing job
                    $log->setRecordCount($cursor->getCounter());
                    $this->sync_manager->saveLog($log);

                    return true;
                }
            } catch (\Exception $e) {

                // log the errors but continue on to the next usersource
                KernelErrorHandler::handleException($e, false);
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
            array('original_start_timestamp' => $start_timestamp, 'phase' => 2),
            new \DateTime('now + 20 seconds')
        );

        return true;
    }

    private function runPhaseTwo(array $data)
    {
        $original_start_timestamp = $data['original_start_timestamp'];
        $start_at_usersource_id = $data['phase_2_usersource'];
        $count = $data['phase_2_count'];

        foreach ($this->getSyncEnabledUsersources() as $usersource) {
            if ($start_at_usersource_id && $usersource->getId() != $start_at_usersource_id) {
                continue;
            }
            // stop skip
            $start_at_usersource_id = null;
            $last_processed_usersource_id = $usersource->getId();
            $this_usersource_errors = 0;
            
            $associations = $this->usersource_manager->findAssociationsUpdatedBefore(
                $usersource,
                $ts = new \DateTime(sprintf('@%s', $original_start_timestamp))
            );

            $log = $this->sync_manager->getLogToUseDuringSync($usersource);
            $is_start_of_phase_2 = $count == 0;
            if ($is_start_of_phase_2) {
                $log->startPhaseTwo();
            }

            $had_to_break = false;
            foreach ($associations as $association) {
                    $identity = $association->getIdentity();

                    try {
                        if ($this->sync_manager->refreshIdentity($association->getUsersource(), $identity)) {
                            $count++;
                            $log->incrementRecordCount();
                        }
                    } catch (\Exception $e) {
                        // log the error, but continue processing
                        $this_usersource_errors++;
                        KernelErrorHandler::handleException($e, false);
                        if ($this_usersource_errors > 10) {
                            $log->markErrorStatus();
                            $this->sync_manager->saveLog($log);
                            break;
                        }
                    }

                    if (static::pauseJobCondition(new SyncCursor())) {
                        $had_to_break = true;
                        break;
                    }

            }

            if ($had_to_break) {
                // phase 2 needs another

                if (static::$aborted) {
                    $this->abort();
                    return false;
                }

                $this->sync_manager->saveLog($log);

                $this->scheduleNextSync(
                    array(
                        'phase' => 2,
                        'phase_2_count' => $count,
                        'phase_2_usersource' => $last_processed_usersource_id,
                        'original_start_timestamp' => $data['original_start_timestamp'],
                    ),
                    new \DateTime('now + 20 seconds')
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
            array('phase' => 1),
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
