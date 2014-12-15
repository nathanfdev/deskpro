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

use Application\EmailBundle\Mail\SourceMapper\SourceMapperInterface;
use Psr\Log\LoggerInterface;

class QueueProc
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SourceMapperInterface
     */
    private $source_mapper;

    /**
     * @var SourceSender
     */
    private $source_sender;

    /**
     * @param SourceMapperInterface $source_mapper
     * @param SourceSender $source_sender
     * @param LoggerInterface $logger
     */
    public function __construct(SourceMapperInterface $source_mapper, SourceSender $source_sender, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->source_mapper = $source_mapper;
        $this->source_sender = $source_sender;
    }

    /**
     * @param array $r
     */
    public function process(array $r)
    {
        $this->logger->info(sprintf("Processing %d -- %s", $r['id'], substr($r['header_subject'], 0, 85)));

        // Inc exec count
        $r['exec_count']++;
        $this->source_mapper->setSourceProcessing($r);

        try {
            $sent = $this->source_sender->send($r);

            foreach ($this->source_sender->getLastLogAsArray() as $l) $this->logger->debug('<Sender> ' . $l);

            if ($sent) {
                $this->logger->info("Completed successfully");
                $this->source_mapper->markSourceComplete($r, $this->source_sender->getLastLog());
            } else {
                $this->logger->notice("Did not send any messages");
                $this->source_mapper->markSourceError($r, 'no_send', $this->source_sender->getLastLog());
            }
        } catch (\Exception $e) {
            $next = $this->getNextRetry($r);

            foreach ($this->source_sender->getLastLogAsArray() as $l) $this->logger->debug('<Sender> ' . $l);

            $this->logger->warn("Failed: " . $e->getMessage());

            $log = $this->source_sender->getLastLog();
            $log = trim($log . "\n" . sprintf('[%s] Exception: %s %s', date('Y-m-d'), $e->getCode(), $e->getMessage()));

            if ($next) {
                $this->source_mapper->markSourceRetry($r, $log, $next);
            } else {
                $this->source_mapper->markSourceError($r, 'failed', $log);
            }
        }
    }


    /**
     * @param array $sendmail
     * @return \DateTime|null
     */
    private function getNextRetry(array $sendmail)
    {
        switch ($sendmail['exec_count']) {
            case 0:
            case 1:
                $time_offset = 180; // 3m
                break;

            case 2:
                $time_offset = 600; // 10m
                break;

            case 3:
                $time_offset = 1800; // 30m
                break;

            case 4:
                $time_offset = 5400; // 1.5h
                break;

            case 5:
                $time_offset = 10800; // 3h
                break;

            default:
                return null;
        }

        return new \DateTime("-$time_offset seconds");
    }
}