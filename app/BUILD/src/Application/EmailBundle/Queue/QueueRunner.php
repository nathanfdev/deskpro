<?php

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\Queue;

use Application\DeskPRO\DBAL\Connection;
use Application\EmailBundle\SourceMapper\DatabaseSourceMapper;
use Application\EmailBundle\SourceMapper\SourceMapperInterface;
use Psr\Log\LoggerInterface;

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
     * @var array
     */
    private $done_ids = [];

    /**
     * @param Connection            $db
     * @param QueueProc             $queue_proc
     * @param SourceMapperInterface $source_mapper
     * @param SourceSender          $source_sender
     * @param LoggerInterface       $logger
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
     * @param int $proc_limit Max number of emails to send
     * @param int $time_limit Max time to spend sending
     */
    public function setLimits($proc_limit, $time_limit)
    {
        $this->proc_limit      = $proc_limit;
        $this->proc_time_limit = $time_limit;
    }

    /**
     * Timeout sources that have been marked as processing too long.
     *
     * @throws \Exception
     *
     * @return int
     */
    public function detectProblems()
    {
        $count = 0;

        do {
            $did = false;

            //----
            // Retry state
            //----

            // Finds emails that have been 'processing' too long and mark for retry
            $batch = $this->db->fetchAllKeyed("
                SELECT * FROM sendmail_sources
                WHERE status = 'processing' AND date_status < ? AND exec_count <= 3
                LIMIT 250
            ", [date('Y-m-d H:i:s', time() - 1200)]);

            $count += count($batch);

            // Appends to log file about the timeout
            foreach ($batch as $r) {
                $d   = \DateTime::createFromFormat('Y-m-d H:i:s', $r['date_status']);
                $msg = sprintf(
                    '[%s] RETRY: Detected process timeout. Stuck at %s since %s (%s mins). Retrying.',
                    date('Y-m-d H:i:s'),
                    $r['status'],
                    $r['date_status'],
                    ceil((time() - $d->getTimestamp()) / 60)
                );
                $this->source_mapper->markSourceRetry($r, $msg, new \DateTime('+30 minutes'));

                $did = true;
            }

            //----
            // Error state
            //----

            // Remining ones are ones we should mark for failure
            $batch = $this->db->fetchAllKeyed("
                SELECT * FROM sendmail_sources
                WHERE status IN ('inserted', 'processing') AND date_status < ?
                LIMIT 250
            ", [date('Y-m-d H:i:s', time() - 1200)]);

            $count += count($batch);

            $this->db->executeUpdate("
                UPDATE sendmail_sources
                SET status = 'error', error_code = 'timeout'
                WHERE id IN (?)
            ", [array_keys($batch)], [Connection::PARAM_INT_ARRAY]);

            // Appends to log file about the timeout
            foreach ($batch as $r) {
                $d   = \DateTime::createFromFormat('Y-m-d H:i:s', $r['date_status']);
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

            //----
            // Detect messages that did not queue in an external service properly
            //----

            if (!($this->source_mapper instanceof DatabaseSourceMapper)) {
                $batch = $this->db->fetchAllKeyed("
                    SELECT * FROM sendmail_sources
                    WHERE status IN ('pending', 'retry') AND error_code = 'enqueue_failed'
                    LIMIT 250
                "); // 1 hrs

                foreach ($batch as $r) {
                    $this->source_mapper->setSourcePending($r, new \DateTime('-1 seconds'));
                    $did = true;
                }
            }

            //----
            // Detect messages that are pending too long
            //----

            // - If not using the standard database source mapper,
            // means we are using some other source mapper which might
            // use a queue.
            // - So we sholud detect messages that are still 'pending' after
            // a long time and re-mark them as pending (which should hopefully
            // re-queue the message in the queue).

            if (!($this->source_mapper instanceof DatabaseSourceMapper)) {
                $batch = $this->db->fetchAllKeyed("
                    SELECT * FROM sendmail_sources
                    WHERE status IN ('pending') AND date_status < ?
                    LIMIT 250
                ", [date('Y-m-d H:i:s', time() - 1800)]); // 30m

                foreach ($batch as $r) {
                    $this->source_mapper->setSourcePending($r, new \DateTime('-1 seconds'));
                    $did = true;
                }

                // retry statuses
                $batch = $this->db->fetchAllKeyed("
                    SELECT * FROM sendmail_sources
                    WHERE status IN ('retry') AND date_next_attempt < ?
                    LIMIT 250
                ", [date('Y-m-d H:i:s', time())]);

                foreach ($batch as $r) {
                    $this->source_mapper->setSourcePending($r, new \DateTime('-1 seconds'));
                    $did = true;
                }
            }
        } while ($did);

        return $count;
    }

    /**
     * Runs through the queue.
     *
     * @return int
     */
    public function run()
    {
        $time_start = time();
        $count      = 0;

        $this->logger->info(sprintf('Starting -- Limit: %d -- Max Time: %ds', $this->proc_limit, $this->proc_time_limit));

        $did_early_break = false;

        while (true) {
            $did_break   = false;
            $batch_count = 0;
            $batch       = $this->reserveBatch();
            $this->logger->info(sprintf('Reserved %d records', count($batch)));

            // FIXME
            // dp_sys.alerts.event_logger should be injected
            $proc = new QueueProc($this->source_mapper, $this->source_sender, $this->logger, \Application\DeskPRO\App::$container->get('dp_sys.alerts.event_logger'));

            if ($batch) {
                while ($r = array_shift($batch)) {
                    $proc->process($r);
                    ++$count;
                    ++$batch_count;

                    if ($count >= $this->proc_limit) {
                        $this->logger->info('Reached limit, breaking');
                        $did_break       = true;
                        $did_early_break = true;
                        break;
                    }

                    if ((time() - $time_start) > $this->proc_time_limit) {
                        $this->logger->info('Reached time limit, breaking');
                        $did_break       = true;
                        $did_early_break = true;
                        break;
                    }
                }
            }

            if ($batch) {
                $did_early_break = true;
                $this->logger->info(sprintf('Releasing remainder %d reserved records back into queue', count($batch)));
                $this->releaseRemaining($batch);
            }

            if ($did_break || !$batch_count) {
                break;
            }

            if ((time() - $time_start) > $this->proc_time_limit) {
                $did_early_break = true;
                $this->logger->info('Reached time limit, breaking (outer)');
                break;
            }
        }

        $time_end = time();
        $this->logger->info(sprintf('Processed %d records in %ds', $count, $time_end - $time_start));

        // If we broke early then we may have messages stuck in the 'pending' state
        // we should re-queue the messages so they enter into the queue again and
        // (in case of our cloud) re-spawn the exec command
        if ($did_early_break && !($this->source_mapper instanceof DatabaseSourceMapper)) {
            $this->db->beginTransaction();

            $batch = $this->db->fetchAllKeyed("
                SELECT * FROM sendmail_sources
                WHERE status IN ('pending')
                LIMIT 250
                FOR UPDATE
            ");

            foreach ($batch as $r) {
                $done_ids[] = $r['id'];
                $this->source_mapper->setSourcePending($r, new \DateTime('-1 seconds'));
            }

            $this->db->commit();

            $this->logger->info(sprintf('Touched %d records for processing in another run', count($batch)));
        }

        return $count;
    }

    /**
     * @throws \Exception
     *
     * @return array Array of id=>status of records to process
     */
    private function reserveBatch()
    {
        $this->db->beginTransaction();

        if (!$this->done_ids) {
            $this->done_ids = [0];
        }

        $batch = $this->db->fetchAll("
            SELECT *
            FROM sendmail_sources
            WHERE
              status IN ('pending', 'retry')
              AND (date_next_attempt <= ? OR date_next_attempt IS NULL)
              AND id NOT IN (?)
            ORDER BY status ASC, id ASC
            LIMIT {$this->per_batch}
        ", [date('Y-m-d H:i:s', time() + 5 /* +4 sec to account for time drift */), $this->done_ids], [\PDO::PARAM_STR, \Doctrine\DBAL\Connection::PARAM_INT_ARRAY]);

        if ($batch) {
            $batch_ids = array_map(function ($r) {
                return $r['id'];
            }, $batch);
            $this->done_ids = array_merge($this->done_ids, $batch_ids);
            $this->db->executeUpdate("
                UPDATE sendmail_sources
                SET status = 'processing', date_status = ?
                WHERE id IN (?)
            ", [date('Y-m-d H:i:s'), $batch_ids], [\PDO::PARAM_STR, Connection::PARAM_INT_ARRAY]);
        }

        $this->db->commit();

        return $batch;
    }

    /**
     * Given a batch of records that we didnt get to (e.g., timeout happened first), release them back
     * to their original status so they can be run next time.
     *
     * @param array $batch
     *
     * @throws \Exception
     *
     * @return int
     */
    private function releaseRemaining(array $batch)
    {
        if (!$batch) {
            return;
        }

        $this->db->beginTransaction();

        $as_pending = [];
        $as_retry   = [];

        foreach ($batch as $info) {
            switch ($info['status']) {
                case 'pending': $as_pending[] = $info['id']; break;
                case 'retry':   $as_retry[]   = $info['id']; break;
            }
        }

        if ($as_pending) {
            $this->db->executeUpdate("
                UPDATE sendmail_sources
                SET status = 'pending'
                WHERE id IN (?)
            ", [$as_pending], [Connection::PARAM_INT_ARRAY]);
        }
        if ($as_retry) {
            $this->db->executeUpdate("
                UPDATE sendmail_sources
                SET status = 'retry'
                WHERE id IN (?)
            ", [$as_retry], [Connection::PARAM_INT_ARRAY]);
        }

        $this->db->commit();
    }
}
