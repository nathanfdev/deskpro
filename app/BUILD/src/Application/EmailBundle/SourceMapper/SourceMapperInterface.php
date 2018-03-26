<?php

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\SourceMapper;

/**
 * Handles saving/fetching sources. NOTE: mappers work on ARRAYs, not entities.
 */
interface SourceMapperInterface
{
    /**
     * @param $source_id
     *
     * @return array|null
     */
    public function getSource($source_id);

    /**
     * Get a resource for a source (the actual message data).
     *
     * @param array $source
     *
     * @return resource
     */
    public function getRowBlobHandle(array $source);

    /**
     * @param \Swift_Mime_Message $message
     * @param $status
     * @param \DateTime $queue_date
     *
     * @return array
     */
    public function createSourceForMessage(\Swift_Mime_Message $message, $status, \DateTime $queue_date = null);

    /**
     * @param array $source
     * @param null  $log_text
     *
     * @return mixed
     */
    public function markSourceComplete(array $source, $log_text = null);

    /**
     * @param array $source
     * @param null  $log_text
     *
     * @return mixed
     */
    public function markSourceAborted(array $source, $log_text = null);

    /**
     * @param array     $source
     * @param null      $log_text
     * @param \DateTime $next_date
     *
     * @return mixed
     */
    public function markSourceRetry(array $source, $log_text = null, \DateTime $next_date = null);

    /**
     * @param array  $source
     * @param string $error_code
     * @param null   $log_text
     *
     * @return mixed
     */
    public function markSourceError(array $source, $error_code, $log_text = null);

    /**
     * @param array     $source
     * @param \DateTime $next_date
     *
     * @return mixed
     */
    public function setSourcePending(array $source, \DateTime $next_date = null);

    /**
     * @param array $source
     *
     * @return mixed
     */
    public function setSourceProcessing(array $source);

    /**
     * @param array  $source
     * @param string $log_text
     *
     * @return array
     */
    public function setLogText(array $source, $log_text = '');
}
