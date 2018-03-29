<?php

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\SourceMapper;

use Application\EmailBundle\Entity\SendmailSource;
use Application\EmailBundle\SourceMapper\PendingQueuer\PendingQueuerInterface;

class ExternalPendingQueue implements SourceMapperInterface
{
    /**
     * @var SourceMapperInterface
     */
    private $source_mapper;

    /**
     * @var PendingQueuerInterface
     */
    private $queuer;

    /**
     * @param SourceMapperInterface  $source_mapper
     * @param PendingQueuerInterface $queuer
     */
    public function __construct(SourceMapperInterface $source_mapper, PendingQueuerInterface $queuer)
    {
        $this->source_mapper = $source_mapper;
        $this->queuer        = $queuer;
    }

    /**
     * @param $source_id
     *
     * @return array|null
     */
    public function getSource($source_id)
    {
        return $this->source_mapper->getSource($source_id);
    }

    /**
     * Get a resource for a source (the actual message data).
     *
     * @param array $source
     *
     * @return resource
     */
    public function getRowBlobHandle(array $source)
    {
        return $this->source_mapper->getRowBlobHandle($source);
    }

    /**
     * @param \Swift_Mime_Message $message
     * @param $status
     * @param \DateTime $queue_date
     *
     * @return array
     */
    public function createSourceForMessage(\Swift_Mime_Message $message, $status, \DateTime $queue_date = null)
    {
        $r = $this->source_mapper->createSourceForMessage($message, $status, $queue_date);
        $r = $this->enqueueMessage($r);

        return $r;
    }

    /**
     * @param array $source
     * @param null  $log_text
     *
     * @return mixed
     */
    public function markSourceComplete(array $source, $log_text = null)
    {
        return $this->source_mapper->markSourceComplete($source, $log_text);
    }

    /**
     * @param array $source
     * @param null  $log_text
     *
     * @return mixed
     */
    public function markSourceAborted(array $source, $log_text = null)
    {
        return $this->source_mapper->markSourceAborted($source, $log_text);
    }

    /**
     * @param array     $source
     * @param null      $log_text
     * @param \DateTime $next_date
     *
     * @return mixed
     */
    public function markSourceRetry(array $source, $log_text = null, \DateTime $next_date = null)
    {
        $r = $this->source_mapper->markSourceRetry($source, $log_text, $next_date);
        $r = $this->enqueueMessage($r);

        return $r;
    }

    /**
     * @param array  $source
     * @param string $error_code
     * @param null   $log_text
     *
     * @return mixed
     */
    public function markSourceError(array $source, $error_code, $log_text = null)
    {
        return $this->source_mapper->markSourceError($source, $error_code, $log_text);
    }

    /**
     * @param array     $source
     * @param \DateTime $next_date
     *
     * @return mixed
     */
    public function setSourcePending(array $source, \DateTime $next_date = null)
    {
        $r = $this->source_mapper->setSourcePending($source, $next_date);
        $r = $this->enqueueMessage($r);

        return $r;
    }

    /**
     * @param array $source
     *
     * @return mixed
     */
    public function setSourceProcessing(array $source)
    {
        return $this->source_mapper->setSourceProcessing($source);
    }

    /**
     * @param array  $source
     * @param string $log_text
     *
     * @return array
     */
    public function setLogText(array $source, $log_text = '')
    {
        return $this->source_mapper->setLogText($source, $log_text);
    }

    private function enqueueMessage(array $r)
    {
        if ($r['status'] == SendmailSource::STATUS_PENDING || $r['status'] == SendmailSource::STATUS_RETRY) {
            try {
                $this->queuer->queueMessageSource($r);
            } catch (\Exception $e) {
                $status['error_code'] = SendmailSource::ERR_ENQUEUE_FAILED;
                $r                    = $this->source_mapper->markSourceRetry($r, 'Failed to queue message: '.$e->getMessage());
            }
        }

        return $r;
    }
}
