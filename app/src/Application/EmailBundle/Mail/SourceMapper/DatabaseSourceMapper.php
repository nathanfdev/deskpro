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

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\DBAL\Connection;
use DeskPRO\Kernel\KernelErrorHandler;

class DatabaseSourceMapper implements SourceMapperInterface
{
    /**
     * @var \Application\DeskPRO\DBAL\Connection
     */
    private $db;

    /**
     * @var
     */
    private $bs;

    /**
     * @param Connection $db
     * @param DeskproBlobStorage $bs
     */
    public function __construct(Connection $db, DeskproBlobStorage $bs)
    {
        $this->db = $db;
        $this->bs = $bs;
    }

    /**
     * @param $source_id
     * @return mixed
     */
    public function getSource($source_id)
    {
        return $this->db->fetchAssoc("SELECT * FROM sendmail_sources WHERE id = ?", array($source_id));
    }

    /**
     * Get a resource for a source (the actual message data)
     *
     * @param array $source
     * @return resource
     */
    public function getRowBlobHandle(array $source)
    {

    }

    /**
     * @param \Swift_Mime_Message $message
     * @param $status
     * @param \DateTime $queue_date
     * @return array
     */
    public function createSourceForMessage(\Swift_Mime_Message $message, $status, \DateTime $queue_date = null)
    {
        $blob = $this->bs->createBlobRowFromString($message->toString(), 'out_email.eml', 'message/rfc822');

        $header_to_raw  = $message->getTo();
        $header_to      = array();
        if ($header_to_raw) {
            foreach ($header_to_raw as $email => $name) {
                if ($name) {
                    $header_to[] = "$name <$email>";
                } else {
                    $header_to[] = $email;
                }
            }
        }
        $header_to = implode(', ', $header_to);

        $header_subject_raw = $message->getHeaders()->get('Subject');
        $header_subject = '';
        if ($header_subject_raw) {
            $header_subject = $header_subject_raw;
        }

        $header_from_raw = $message->getHeaders()->get('From');
        $header_from = array();
        if ($header_from_raw) {
            foreach ($header_from_raw as $email => $name) {
                if ($name) {
                    $header_from[] = "$name <$email>";
                } else {
                    $header_from[] = $email;
                }
            }
        }
        $header_from = implode(', ', $header_from);

        $date = date('Y-m-d H:i:s');

        $record = array(
            'blob_id'        => $blob['id'],
            'headers'        => $message->getHeaders()->toString(),
            'header_to'      => $header_to,
            'header_from'    => $header_from,
            'header_subject' => $header_subject,
            'status'         => $status,
            'date_status'    => $date,
            'date_created'   => $date,
            'exec_count'     => 0
        );

        if ($status == 'pending') {
            if (!$queue_date) {
                $queue_date = new \DateTime();
            }

            $record['date_next_attempt'] = $queue_date->format('Y-m-d H:i:s');
        }

        if ($status == 'complete') {
            $record['exec_count'] = 1;
        }

        $this->db->insert('sendmail_sources', $record);
        $record['id'] = $this->db->lastInsertId();

        return $record;
    }

    /**
     * @param array $source
     * @param null $log_text
     * @return array
     */
    public function markSourceComplete(array $source, $log_text = null)
    {
        $new_source = $source;
        $new_source['status']            = 'complete';
        $new_source['date_next_attempt'] = null;
        $new_source['date_sent']         = date('Y-m-d H:i:s');
        $new_source['date_status']       = date('Y-m-d H:i:s');

        $this->appendLogText($new_source, $log_text);
        $this->updateSourceRow($source, $new_source);

        return $new_source;
    }

    /**
     * @param array $source
     * @param null $log_text
     * @return array
     */
    public function markSourceAborted(array $source, $log_text = null)
    {
        $new_source = $source;
        $new_source['status']            = 'aborted';
        $new_source['date_next_attempt'] = null;
        $new_source['date_status']       = date('Y-m-d H:i:s');

        $this->appendLogText($new_source, $log_text);
        $this->updateSourceRow($source, $new_source);

        return $new_source;
    }

    /**
     * @param array $source
     * @param null $log_text
     * @param \DateTime $next_date
     * @return mixed
     */
    public function markSourceRetry(array $source, $log_text = null, \DateTime $next_date = null)
    {
        $new_source = $source;
        $new_source['status'] = 'retry';

        if (!$next_date) {
            $next_date = new \DateTime();
        }

        $new_source['date_next_attempt'] = $next_date->format('Y-m-d H:i:s');
        $new_source['date_status']       = date('Y-m-d H:i:s');

        $this->appendLogText($new_source, $log_text);
        $this->updateSourceRow($source, $new_source);

        return $new_source;
    }

    /**
     * @param array $source
     * @param string $error_code
     * @param null $log_text
     * @return mixed
     */
    public function markSourceError(array $source, $error_code, $log_text = null)
    {
        $new_source = $source;
        $new_source['status']            = 'error';
        $new_source['error_code']        = $error_code;
        $new_source['date_next_attempt'] = null;
        $new_source['date_status']       = date('Y-m-d H:i:s');

        $this->appendLogText($new_source, $log_text);
        $this->updateSourceRow($source, $new_source);

        return $new_source;
    }

    /**
     * @param array $source
     * @param \DateTime $next_date
     * @return mixed
     */
    public function setSourcePending(array $source, \DateTime $next_date = null)
    {
        $new_source = $source;
        $new_source['status'] = 'pending';

        if (!$next_date) {
            $next_date = new \DateTime();
        }

        $new_source['date_next_attempt'] = $next_date->format('Y-m-d H:i:s');
        $new_source['date_status']       = date('Y-m-d H:i:s');

        $this->updateSourceRow($source, $new_source);

        return $new_source;
    }

    /**
     * @param array $source
     * @return mixed
     */
    public function setSourceProcessing(array $source)
    {
        $new_source = $source;
        $new_source['status']      = 'processing';
        $new_source['exec_count']  = (isset($source['exec_count']) ? $source['exec_count'] : 0) + 1;
        $new_source['date_status'] = date('Y-m-d H:i:s');

        $this->updateSourceRow($source, $new_source);

        return $new_source;
    }

    /**
     * @param array $source
     * @param string $log_text
     * @return void
     */
    private function appendLogText(&$source, $log_text)
    {
        if (!$log_text) {
            return;
        }

        $log_text = trim($log_text);

        if (!$log_text) {
            return;
        }

        $exist_log = "";
        $old_log_blob = null;
        if (isset($source['log_blob_id'])) {
            try {
                $old_log_blob = $this->db->fetchAssoc("SELECT * FROM blobs WHERE id = ?", array($source['log_blob_id']));
                $exist_log = $this->bs->copyBlobRowToString($old_log_blob);
            } catch (\Exception $e) {}

            if ($exist_log) {
                $log_text = $exist_log . "\n\n\n" . str_repeat('#', 72) . "\n\n\n" . $log_text;
            }
        }

        try {
            $new_log_blob = $this->bs->createBlobRowFromString($log_text, 'log.txt', 'text/plain');
        } catch (\Exception $e) {
            KernelErrorHandler::handleException($e);
            return;
        }

        if ($old_log_blob) {
            $this->bs->deleteBlobRow($old_log_blob);
        }

        $source['log_blob_id'] = $new_log_blob['id'];
    }

    /**
     * @param array $orig
     * @param array $new
     */
    private function updateSourceRow(array $orig, array $new)
    {
        $diff = array();

        foreach ($new as $k => $v) {
            if (!isset($orig[$k]) || $orig[$k] != $v) {
                $diff[$k] = $v;
            }
        }

        if ($diff) {
            $this->db->update('sendmail_sources', $diff, array('id' => $orig['id']));
        }
    }
}