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

namespace Application\EmailBundle\SourceMapper;

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Email\EmailAccount\EmailAccountManager;
use Application\EmailBundle\Log\LogCollectorInterface;
use DeskPRO\Kernel\KernelErrorHandler;
use Orb\Util\Numbers;
use Orb\Util\Strings;

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
     * @var LogCollectorInterface|null
     */
    private $log_collector;

    /**
     * @var EmailAccountManager
     */
    private $email_accounts;

    /**
     * @param Connection $db
     * @param DeskproBlobStorage $bs
     * @param EmailAccountManager $email_accounts
     * @param LogCollectorInterface $log_collector
     */
    public function __construct(Connection $db, DeskproBlobStorage $bs, EmailAccountManager $email_accounts, LogCollectorInterface $log_collector = null)
    {
        $this->db = $db;
        $this->bs = $bs;
        $this->log_collector = $log_collector;
        $this->email_accounts = $email_accounts;
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

        $header_cc_raw  = $message->getCc();
        $header_bcc_raw = $message->getBcc();

        $tos  = array();
        $ccs  = array();
        $bccs = array();

        if ($header_to_raw) {
            foreach ($header_to_raw as $email => $name) {
                $tos[] = $email;
                if ($name) {
                    $header_to[] = "$name <$email>";
                } else {
                    $header_to[] = $email;
                }
            }
        }
        $header_to = implode(', ', $header_to);

        if ($header_cc_raw) {
            foreach ($header_cc_raw as $email => $x) {
                $ccs[] = $email;
            }
        }
        if ($header_bcc_raw) {
            foreach ($header_cc_raw as $email) {
                $bccs[] = $email;
            }
        }

        $header_subject = $message->getSubject() ?: '';

        $header_from_raw = $message->getFrom();
        $header_from = array();
        $header_from_email = '';

        $account_id = null;

        if ($header_from_raw) {
            foreach ($header_from_raw as $email => $name) {
                if ($name) {
                    $header_from[] = "$name <$email>";
                } else {
                    $header_from[] = $email;
                }

                if ($email && !$account_id) {
                    $acc = $this->email_accounts->findAccountForEmailAddress($email, 'with_transport');
                    if ($acc) {
                        $account_id = $acc->id;
                        $header_from_email = $acc->getUseEmailAddress();
                    }
                }
            }
        }
        $header_from = implode(', ', $header_from);

        $date = date('Y-m-d H:i:s');

        $ref_header = $message->getHeaders()->get('X-DeskPRO-MessageRef');
        $ref = null;
        if ($ref_header) {
            $ref = $ref_header->getFieldBody();
        }

        // Should aready be set via Mailer so this is a fallback
        if (!$ref) {
            $ref = Numbers::roundToMultiple(time(), 5) . '-' . Strings::random(40, Strings::CHARS_ALPHANUM_IU);
            $message->getHeaders()->addTextHeader('X-DeskPRO-MessageRef', $ref);
            $message->setId($ref . '@deskpro-message');
        }

        if ($status == 'processing') {
            $exec_count = 1;
        } else {
            $exec_count = 0;
        }

        $record = array(
            'blob_id'          => $blob['id'],
            'ref'              => $ref,
            'email_account_id' => $account_id,
            'headers'          => $message->getHeaders()->toString(),
            'header_to'        => $header_to,
            'header_from'      => $header_from,
            'header_subject'   => $header_subject,
            'from_email'       => $header_from_email,
            'to_emails'        => implode(',', $tos) ?: null,
            'cc_emails'        => implode(',', $ccs) ?: null,
            'bcc_emails'       => implode(',', $bccs) ?: null,
            'status'           => $status,
            'date_status'      => $date,
            'date_created'     => $date,
            'exec_count'       => $exec_count
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
        $new_source['error_code']        = '';
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
     * @return array
     */
    public function setLogText(array $source, $log_text = '')
    {
        $new_source = $source;

        if ($this->appendLogText($new_source, $log_text)) {
            $this->updateSourceRow($source, $new_source);
        }

        return $new_source;
    }

    /**
     * @param array $source
     * @param string $log_text
     * @return void
     */
    private function appendLogText(&$source, $log_text)
    {
        $log_text = trim($log_text);

        if ($this->log_collector && !empty($source['id']) && $source['id']) {
            $log_text = trim($this->log_collector->getLogForMessage($source['id']) . "\n" . $log_text);
        }

        if (!$log_text) {
            return false;
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
            $new_log_blob = $this->bs->createBlobRowFromString($log_text, 'log.txt', 'text/plain', array('tag' => 'logs.sendmail_source_log'));
        } catch (\Exception $e) {
            KernelErrorHandler::handleException($e);
            return;
        }

        if ($old_log_blob) {
            $this->bs->deleteBlobRow($old_log_blob);
        }

        $source['log_blob_id'] = $new_log_blob['id'];

        return true;
    }

    /**
     * @param array $orig
     * @param array $new
     */
    private function updateSourceRow(array $orig, array $new)
    {
        $diff = array();

        static $valid_keys = array(
            'id' => true,
            'blob_id' => true,
            'email_account_id' => true,
            'log_blob_id' => true,
            'ref' => true,
            'context_type' => true,
            'context_id' => true,
            'context_info' => true,
            'headers' => true,
            'header_to' => true,
            'header_from' => true,
            'header_subject' => true,
            'from_email' => true,
            'to_emails' => true,
            'cc_emails' => true,
            'bcc_emails' => true,
            'status' => true,
            'date_status' => true,
            'date_sent' => true,
            'date_next_attempt' => true,
            'error_code' => true,
            'date_created' => true,
            'exec_count' => true,
        );

        static $always_save = array(
            'log_blob_id' => true,
            'status' => true,
            'date_status' => true,
            'date_sent' => true,
            'date_next_attempt' => true,
            'error_code' => true,
            'exec_count' => true,
        );

        foreach ($new as $k => $v) {
            if (!isset($valid_keys[$k])) continue;
            if (isset($always_save[$k])) {
                $diff[$k] = $v;
            } else if (!isset($orig[$k]) || $orig[$k] != $v) {
                $diff[$k] = $v;
            }
        }

        if ($diff) {
            $this->db->update('sendmail_sources', $diff, array('id' => $orig['id']));
        }
    }
}