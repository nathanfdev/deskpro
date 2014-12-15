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

namespace Application\EmailBundle\Mail\SourceMapper;
use Application\DeskPRO\Entity\EmailAccount;

/**
 * Handles saving/fetching sources. NOTE: mappers work on ARRAYs, not entities.
 */
interface SourceMapperInterface
{
    /**
     * @param $source_id
     * @return array|null
     */
    public function getSource($source_id);

    /**
     * Get a resource for a source (the actual message data)
     *
     * @param array $source
     * @return resource
     */
    public function getRowBlobHandle(array $source);

    /**
     * @param \Swift_Mime_Message $message
     * @param $status
     * @param \DateTime $queue_date
     * @return array
     */
    public function createSourceForMessage(\Swift_Mime_Message $message, $status, \DateTime $queue_date = null);

    /**
     * @param array $source
     * @param null $log_text
     * @return mixed
     */
    public function markSourceComplete(array $source, $log_text = null);

    /**
     * @param array $source
     * @param null $log_text
     * @return mixed
     */
    public function markSourceAborted(array $source, $log_text = null);

    /**
     * @param array $source
     * @param null $log_text
     * @param \DateTime $next_date
     * @return mixed
     */
    public function markSourceRetry(array $source, $log_text = null, \DateTime $next_date = null);

    /**
     * @param array $source
     * @param string $error_code
     * @param null $log_text
     * @return mixed
     */
    public function markSourceError(array $source, $error_code, $log_text = null);

    /**
     * @param array $source
     * @param \DateTime $next_date
     * @return mixed
     */
    public function setSourcePending(array $source, \DateTime $next_date = null);

    /**
     * @param array $source
     * @return mixed
     */
    public function setSourceProcessing(array $source);
}