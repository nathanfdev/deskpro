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
use Application\DeskPRO\JobQueue\JobQueue;
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

    const MAX_TIME = 20;
    const MAX_COUNT = 1000;
    public static $max_time;
    public static $count;
    public static $max_memory_usage;

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
                'sync_cursor_phase' => 1,
                'phase_2_location' => 1,
                'current_usersource_id' => null,
                'phase' => 1
            )
        );
    }

    /**
     * this is what needs to be implemented - this method will receive the payload and it needs to be dealt with
     *
     * @param  array $data validated data (the payload)
     * @param  array $job the full job db row array
     * @return bool  TRUE if successfully processed
     */
    public function process(array $data, array $job)
    {
        static::$max_time = time() + static::MAX_TIME;
        static::$count = 0;
        static::$max_memory_usage = min(max(Env::getMemoryLimit(), 500*1024*1024), 500*1024*1024) * 0.8;
        if (1 == $data['phase']) {
            return $this->runPhaseOne($data);
        } else {
            return $this->runPhaseTwo($data);
        }
    }

    public static function pauseJobCondition(SyncCursor $cursor)
    {
        // we return true if we want to signal to the syncer to pause

        // condition 1: if we allocate 80% or greater of our max memory usage
        if (memory_get_usage(true) > UsersourceSyncProcessor::$max_memory_usage) {
            return true;
        }

        // considtion 2: if we go over x seconds
        if (time() > UsersourceSyncProcessor::$max_time) {
            return true;
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
        $cursor = new SyncCursor($data['sync_cursor_location'], $data['sync_cursor_phase']);

        $last_processed_usersource_id = null;
        foreach ($this->usersource_manager->getAll() as $usersource) {
            if ($skip_to_usersource_id && $usersource->getId() != $skip_to_usersource_id) {
                continue;
            }
            $skip_to_usersource_id = false;

            $last_processed_usersource_id = $usersource->id;

            if (!$cursor) {
                $cursor = new SyncCursor();
            }

            try {
                $this->sync_manager->refreshAll($usersource, $cursor, array($this, 'pauseJobCondition'));

                if (!$cursor->isCompleted()) {
                    // time to pause and re-run this phase at this usersource at the cursor location
                    $this->scheduleNextSync(
                        array(
                            'phase' => 1,
                            'original_start_timestamp' => $start_timestamp,
                            'sync_cursor_location' => $cursor->getLocation(),
                            'sync_cursor_phase' => $cursor->getPhase(),
                            'current_usersource_id' => $last_processed_usersource_id
                        ),
                        new \DateTime('now')
                    );

                    return true;
                }
            } catch (\Exception $e) {
                // log the errors but continue on to the next usersource
                KernelErrorHandler::handleException($e, false);
            }

            $cursor = null;
        }

        // phase 1 is completed now
        // schedule phase 2 for immediate
        $this->scheduleNextSync(
            array('original_start_timestamp' => $start_timestamp, 'phase' => 2),
            new \DateTime('now')
        );

        return true;
    }

    private function runPhaseTwo(array $data)
    {
        $original_start_timestamp = $data['original_start_timestamp'];

        $associations = $this->usersource_manager->findAssociationsUpdatedBefore(
            $ts = new \DateTime(sprintf('@%s', $original_start_timestamp))
        );

        $start_at = $data['phase_2_location'];

        $i = 0;
        $had_to_break = false;
        try {
            // we aren't paginating here because this should be a small #
            // phase 1 should have updated most associations already
            foreach ($associations as $association) {
                $i++;
                if ($i >= $start_at) {
                    $identity = $association->getIdentity();

                    try {
                        $this->sync_manager->refreshIdentity($association->getUsersource(), $identity);
                    } catch (\Exception $e) {
                        // log the error, but continue processing
                        KernelErrorHandler::handleException($e, false);
                    }

                    if (static::pauseJobCondition(new SyncCursor())) {
                        $had_to_break = true;
                        break;
                    }
                }
            }
        } catch (SyncException $e) {
            // TODO: hmm, how to best report a sync error? I don't want to throw the job.
        }

        if ($had_to_break) {
            // phase 2 needs another go
            $this->scheduleNextSync(
                array(
                    'phase' => 2,
                    'phase_2_location' => $i,
                    'original_start_timestamp' => $data['original_start_timestamp'],
                ),
                new \DateTime('now + 1 minutes')
            );

            return true;
        }

        // phase 2 is complete
        // reschedule job one for 24 hours from now
        $this->scheduleNextSync(
            array('phase' => 1),
            new \DateTime('now + 1 minutes')
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
}
