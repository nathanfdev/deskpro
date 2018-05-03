<?php

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\Queue;

use Application\DeskPRO\BlobStorage\BlobStorageException;
use Application\EmailBundle\Mail\RawTransport\RawTransportException;
use Application\EmailBundle\SourceMapper\SourceMapperInterface;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Email\OutgoingEmailFailureEvent;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Email\OutgoingEmailSuccessEvent;
use DeskPRO\Bundle\SystemBundle\SystemAlerts\EventLogger;
use Doctrine\Common\Util\Debug;
use Psr\Log\LoggerInterface;

class QueueProc
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EventLogger
     */
    private $event_logger;

    /**
     * @var SourceMapperInterface
     */
    private $source_mapper;

    /**
     * @var SourceSender
     */
    private $source_sender;

    /**
     * Used by the logger context to mark which log lines are for which messages.
     *
     * @var array
     *
     * @internal
     */
    public static $__dp_current_sendmail = null;

    /**
     * @param SourceMapperInterface $source_mapper
     * @param SourceSender          $source_sender
     * @param LoggerInterface       $logger
     * @param EventLogger           $event_logger
     */
    public function __construct(
        SourceMapperInterface $source_mapper,
        SourceSender $source_sender,
        LoggerInterface $logger,
        EventLogger $event_logger
    ) {
        $this->logger        = $logger;
        $this->source_mapper = $source_mapper;
        $this->source_sender = $source_sender;
        $this->event_logger  = $event_logger;
    }

    /**
     * @param array $r
     */
    public function process(array $r)
    {
        self::$__dp_current_sendmail = $r;
        try {
            $this->doProcess($r);
            self::$__dp_current_sendmail = null;
        } catch (\Exception $e) {
            self::$__dp_current_sendmail = null;
            throw $e;
        }
    }

    /**
     * @param array $r
     */
    private function doProcess(array $r)
    {
        $this->logger->info(sprintf('Processing %d -- %s', $r['id'], substr($r['header_subject'], 0, 85)));
        $this->logger->debug(sprintf('From: %s', $r['from_email'] ?: '<none>'));
        $this->logger->debug(sprintf('To: %s', $r['to_emails'] ?: '<none>'));
        if ($r['cc_emails']) {
            $this->logger->debug(sprintf('CC: %s', $r['cc_emails']));
        }
        if ($r['bcc_emails']) {
            $this->logger->debug(sprintf('BCC: %s', $r['bcc_emails']));
        }

        // Inc exec count
        ++$r['exec_count'];
        $this->source_mapper->setSourceProcessing($r);

        try {
            $sent = $this->source_sender->send($r);

            if ($sent) {
                $this->logger->info('Completed successfully');
                $this->source_mapper->markSourceComplete($r);
            } else {
                $this->logger->notice('Did not send any messages');
                $this->source_mapper->markSourceError($r, 'no_send');
            }

            $this->event_logger->log(new OutgoingEmailSuccessEvent($r['email_account_id'], $r['from_email']));
        } catch (RawTransportException $e) {
            $this->event_logger->log(new OutgoingEmailFailureEvent($r['email_account_id'], $r['from_email'], $e));

            $this->logger->notice('Send failed');
            $next = $this->getNextRetry($r);

            if ($next) {
                $this->logger->info(sprintf('Scheduling retry for %s', $next->format('Y-m-d H:i:s')));
                $this->source_mapper->markSourceRetry($r, null, $next);
            } else {
                $this->logger->notice('Marking as failed (retry count exceeded)');
                $this->source_mapper->markSourceError($r, 'failed');
            }
        } catch (BlobStorageException $e) {
            $this->logger->notice('Send failed due to blob storage problem: '.$e->getMessage());
            if ($e->getPrevious()) {
                $this->logger->notice('Previous exception: '.$e->getPrevious()->getMessage());
            }
            $next = $this->getNextRetry($r);

            if ($next) {
                $this->logger->info(sprintf('Scheduling retry for %s', $next->format('Y-m-d H:i:s')));
                $this->source_mapper->markSourceRetry($r, null, $next);
            } else {
                $this->logger->notice('Marking as failed (retry count exceeded)');
                $this->source_mapper->markSourceError($r, 'failed');
            }
        } catch (\Exception $e) {
            $this->logger->error(sprintf('Unexpected exception raised: %s [%s]: %s', get_class($e), $e->getCode(), $e->getMessage()));
            $this->source_mapper->markSourceError($r, 'failed');
        }
    }

    /**
     * @param array $sendmail
     *
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
                return;
        }

        return new \DateTime("+$time_offset seconds");
    }
}
