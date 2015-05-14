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
use Application\DeskPRO\Usersource\Sync\SyncManager;
use Application\DeskPRO\Usersource\UsersourceManager;
use Doctrine\DBAL\Connection;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UsersourceSyncProcessor extends AbstractJobProcessor
{
    const JOB_TYPE = 'usersource_sync';

    const MAX_TIME = 20;
    const MAX_COUNT = 1000;
    public static $max_time;
    public static $count;

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
                'original_start_timestamp' => null,
                'sync_cursor_location' => null,
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

        if (1 == $data['phase']) {
            return $this->runPhaseOne($data);
        } else {
            return $this->runPhaseTwo($data);
        }
    }

    /**
     * @param array $data
     * @return bool
     */
    protected function runPhaseOne(array $data)
    {
        $start_timestamp = $data['original_start_timestamp'] ?: time();

        $skip_to_usersource_id = $data['current_usersource_id'];
        $cursor = null;
        if ($data['sync_cursor_location']) {
            $cursor = new SyncCursor($data['sync_cursor_location']);
        }

        $last_processed_usersource_id = null;
        foreach ($this->usersource_manager->getAll() as $usersource) {
            if ($skip_to_usersource_id && $usersource->getId() != $skip_to_usersource_id) {
                continue;
            }

            $last_processed_usersource_id = $usersource->id;

            if (!$cursor) {
                $cursor = new SyncCursor();
            }

            $this->sync_manager->refreshAll($usersource, $cursor, function (SyncCursor $cursor) {
                // we return true if we want to signal to the syncer to pause
                UsersourceSyncProcessor::$count++;
                if (UsersourceSyncProcessor::$count > UsersourceSyncProcessor::MAX_COUNT) {
                    return true;
                }
                if (time() > UsersourceSyncProcessor::$max_time) {
                    return true;
                }

                return false;
            });

            if (!$cursor->isCompleted()) {
                // time to pause and schedule the next job

                // last processed id = $last_processed_usersource_id
                // cursor location = $cursor->getLocation()
                // lines below are temporary, it will
                $this->scheduleNextSync(
                    array('original_start_timestamp' => $start_timestamp, 'phase' => 2),
                    new \DateTime('now')
                );

                return true;
            }

            $cursor = null;
        }

        // schedule the Job 2 for immediate
        $this->scheduleNextSync(
            array('original_start_timestamp' => $start_timestamp, 'phase' => 2),
            new \DateTime('now')
        );

        return true;
    }

    private function runPhaseTwo(array $data)
    {
        // process

        // reschedule job one for 24 hours from now
        $this->scheduleNextSync(
            array('phase' => 1),
            new \DateTime('now + 5 minutes')
        );

        return true;
    }

    protected function scheduleNextSync(array $data, \DateTime $next_attempt = null)
    {
        $this->getJobQueue()->addJob(
            new Job('usersource_sync', $data),
            $next_attempt
        );
    }
}
