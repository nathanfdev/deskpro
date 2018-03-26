<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\ServerCron;

use Application\DeskPRO\App;
use Application\DeskPRO\Domain\DomainObject;
use Doctrine\ORM\EntityManager;
use Orb\Util\Dates;

class ServerCron
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var int
     */
    protected $per_page = 100;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @return array
     */
    public function getAllForApi()
    {
        $jobs = $this->em->getRepository('DeskPRO:WorkerJob')->getAll();

        $resData = [];

        foreach ($jobs as $key => $job) {
            if ($job instanceof DomainObject) {
                $resData[$key]                      = $job->toApiData(false, true);
                $resData[$key]['interval_readable'] = $job->getIntervalReadable();
                $resData[$key]['next_run_time']     = $job->getNextRunRelativeTime();
            }
        }

        return $resData;
    }

    /**
     * @return array
     */
    public function getTimes()
    {
        $last_start = App::getContainer()->getSetting('core.last_cron_start');

        if (!$last_start) {
            $last_start = 0;
        }

        $time_since_start = time() - $last_start;

        $last_run = App::getContainer()->getSetting('core.last_cron_run');

        if (!$last_run) {
            $last_run = 0;
        }

        $time_since_run = time() - $last_run;

        return [
            'last_run'                  => (int) $last_run,
            'last_run_ms'               => (int) $last_run * 1000,
            'time_since_run'            => (int) $time_since_run,
            'time_since_run_readable'   => Dates::secsToReadable($time_since_run),
            'last_start'                => (int) $last_start,
            'last_start_ms'             => (int) $last_start * 1000,
            'time_since_start'          => (int) $time_since_start,
            'time_since_start_readable' => Dates::secsToReadable($time_since_start),
        ];
    }

    /**
     * @param string $job_id
     * @param int    $priority
     * @param int    $page
     *
     * @return array
     */
    public function getLogs($job_id = null, $priority = null, $page = 1)
    {
        $params = $this->initializeParams($job_id, $priority);
        $from   = ($page - 1) * $this->per_page;

        return $this->em->getRepository('DeskPRO:LogItem')->getCronLogs(
            $params['job_id'],
            $params['priority'],
            $from,
            $this->per_page
        );
    }

    /**
     * @param string $job_id
     * @param int    $priority
     *
     * @return int
     */
    public function getPagesCount($job_id = null, $priority = null)
    {
        $params = $this->initializeParams($job_id, $priority);

        return $this->em->getRepository('DeskPRO:LogItem')->getCronPagesCount(
            $params['job_id'],
            $params['priority'],
            $this->per_page
        );
    }

    /**
     * @return bool
     */
    public function clearAllLogs()
    {
        $this->em->getRepository('DeskPRO:WorkerJob')->clearAllLogs();

        return true;
    }

    /**
     * @param string $job_id
     * @param int    $priority
     *
     * @return array
     */
    protected function initializeParams($job_id = null, $priority = null)
    {
        if (!$job_id) {
            $job_id = 'worker_job.%';
        } else {
            $job_id = 'worker_job.'.$job_id;
        }

        if (!$priority) {
            $priority = 10;
        }

        return [
            'job_id'   => $job_id,
            'priority' => $priority,
        ];
    }
}
