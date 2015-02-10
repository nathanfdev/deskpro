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
 * @subpackage EmailBundle
 */

namespace Application\EmailBundle\Queue;

use Application\DeskPRO\DBAL\Connection;
use Application\EmailBundle\SourceMapper\SourceMapperInterface;
use Psr\Log\LoggerInterface;
use Monolog;

class QueueRunner
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Connection
     */
    private $db;

    /**
     * @var QueueProc
     */
    private $queue_proc;

    /**
     * @var SourceMapperInterface
     */
    private $source_mapper;

    /**
     * @var SourceSender
     */
    private $source_sender;

    /**
     * @var int
     */
    private $per_batch = 20;

    /**
     * @var int
     */
    private $proc_limit = 200;

    /**
     * @var int
     */
    private $proc_time_limit = 180;

    /**
     * @param Connection $db
     * @param QueueProc $queue_proc
     * @param SourceMapperInterface $source_mapper
     * @param SourceSender $source_sender
     * @param LoggerInterface $logger
     */
    public function __construct(Connection $db, QueueProc $queue_proc, SourceMapperInterface $source_mapper, SourceSender $source_sender, LoggerInterface $logger)
    {
        $this->db            = $db;
        $this->queue_proc    = $queue_proc;
        $this->source_mapper = $source_mapper;
        $this->source_sender = $source_sender;
        $this->logger        = $logger;
    }

    /**
     * @param int $proc_limit  Max number of emails to send
     * @param int $time_limit  Max time to spend sending
     */
    public function setLimits($proc_limit, $time_limit)
    {
        $this->proc_limit      = $proc_limit;
        $this->proc_time_limit = $time_limit;
    }

    /**
     * Timeout sources that have been marked as processing too long.
     *
     * @return int
     * @throws \Exception
     */
    public function detectProblems()
    {
        $count = 0;

        do {
            $did = false;

            #----
            # Retry state
            #----

            // Finds emails that have been 'processing' too long and mark for retry
            $batch = $this->db->fetchAllKeyed("
                SELECT * FROM sendmail_sources
                WHERE status = 'processing' AND date_status < ? AND exec_count <= 3
                LIMIT 100
            ", array(date('Y-m-d H:i:s', time() -  1200)));

            $count += count($batch);

            // Appends to log file about the timeout
            foreach ($batch as $r) {
                $d = \DateTime::createFromFormat('Y-m-d H:i:s', $r['date_status']);
                $msg = sprintf(
                    '[%s] RETRY: Detected process timeout. Stuck at %s since %s (%s mins). Retrying.',
                    date('Y-m-d H:i:s'),
                    $r['status'],
                    $r['date_status'],
                    ceil((time() - $d->getTimestamp()) / 60)
                );
                $this->source_mapper->markSourceRetry($r, $msg, new \DateTime("+30 minutes"));

                $did = true;
            }

            #----
            # Error state
            #----

            // Remining ones are ones we should mark for failure
            $batch = $this->db->fetchAllKeyed("
                SELECT * FROM sendmail_sources
                WHERE status IN ('inserted', 'processing') AND date_status < ?
                LIMIT 100
            ", array(date('Y-m-d H:i:s', time() -  1200)));

            $count += count($batch);

            $this->db->executeUpdate("
                UPDATE sendmail_sources
                SET status = 'error', error_code = 'timeout'
                WHERE id IN (?)
            ", array(array_keys($batch)), array(Connection::PARAM_INT_ARRAY));

            // Appends to log file about the timeout
            foreach ($batch as $r) {
                $d = \DateTime::createFromFormat('Y-m-d H:i:s', $r['date_status']);
                $msg = sprintf(
                    '[%s] ERROR: Detected timeout. Stuck at %s since %s (%s mins)',
                    date('Y-m-d H:i:s'),
                    $r['status'],
                    $r['date_status'],
                    ceil((time() - $d->getTimestamp()) / 60)
                );
                $this->source_mapper->markSourceError($r, 'timeout', $msg);

                $did = true;
            }

        } while ($did);

        return $count;
    }


    /**
     * Runs through the queue
     *
     * @return int
     */
    public function run()
    {
        $time_start = time();
        $count = 0;

        $this->logger->info(sprintf('Starting -- Limit: %d -- Max Time: %ds', $this->proc_limit, $this->proc_time_limit));

        $batch = $this->reserveBatch();
        $this->logger->info(sprintf('Reserved %d records', count($batch)));

        $proc = new QueueProc($this->source_mapper, $this->source_sender, $this->logger);

        if ($batch) {
            while ($r = array_shift($batch)) {
                $proc->process($r);
                $count++;

                if ($count >= $this->proc_limit) {
                    $this->logger->info("Reached limit, breaking");
                    break;
                }

                if ((time() - $time_start) > $this->proc_time_limit) {
                    $this->logger->info("Reached time limit, breaking");
                    break;
                }
            }
        }

        if ($batch) {
            $this->logger->info(sprintf('Releasing remainder %d reserved records back into queue', count($batch)));
            $this->releaseRemaining($batch);
        }

        $time_end = time();
        $this->logger->info(sprintf('Processed %d records in %ds', $count, $time_end - $time_start));

        return $count;
    }

    /**
     * @return array Array of id=>status of records to process
     * @throws \Exception
     */
    private function reserveBatch()
    {
        $this->db->beginTransaction();

        $batch = $this->db->fetchAll("
            SELECT *
            FROM sendmail_sources
            WHERE status IN ('pending', 'retry') AND (date_next_attempt < ? OR date_next_attempt IS NULL)
            ORDER BY status ASC, id ASC
            LIMIT {$this->per_batch}
        ", array(date('Y-m-d H:i:s', time())));

        if ($batch) {
            $batch_ids = array_map(function($r) { return $r['id']; }, $batch);
            $this->db->executeUpdate("
                UPDATE sendmail_sources
                SET status = 'processing', date_status = ?
                WHERE id IN (?)
            ", array(date('Y-m-d H:i:s'), $batch_ids), array(\PDO::PARAM_STR, Connection::PARAM_INT_ARRAY));
        }

        $this->db->commit();

        return $batch;
    }


    /**
     * Given a batch of records that we didnt get to (e.g., timeout happened first), release them back
     * to their original status so they can be run next time.
     *
     * @param array $batch
     * @throws \Exception
     */
    private function releaseRemaining(array $batch)
    {
        if (!$batch) {
            return;
        }

        $this->db->beginTransaction();

        $as_pending = array();
        $as_retry = array();

        foreach ($batch as $info) {
            switch ($info['status']) {
                case 'pending': $as_pending[] = $info['id']; break;
                case 'retry':   $as_retry[] = $info['id']; break;
            }
        }

        if ($as_pending) {
            $this->db->executeUpdate("
                UPDATE sendmail_sources
                SET status = 'pending'
                WHERE id IN (?)
            ", array($as_pending), array(Connection::PARAM_INT_ARRAY));
        }
        if ($as_retry) {
            $this->db->executeUpdate("
                UPDATE sendmail_sources
                SET status = 'retry'
                WHERE id IN (?)
            ", array($as_retry), array(Connection::PARAM_INT_ARRAY));
        }

        $this->db->commit();
    }
}