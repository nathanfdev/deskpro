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

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\Email\EmailAccount\EmailAccountManager;
use Application\EmailBundle\Mail\RawMessage\Rfc2822Decoder;
use Application\EmailBundle\Mail\RawTransport\RawSmtpTransport;
use Application\EmailBundle\Mail\RawTransport\RawSwiftmailerTransport;
use Monolog;
use Orb\Util\Arrays;

class SourceSender
{
    /**
     * @var EmailAccountManager
     */
    private $email_accounts;

    /**
     * @var DeskproBlobStorage
     */
    private $bs;

    /**
     * @var string[]
     */
    private $last_log = array();

    /**
     * @param EmailAccountManager $email_accounts
     * @param DeskproBlobStorage $bs
     */
    public function __construct(EmailAccountManager $email_accounts, DeskproBlobStorage $bs)
    {
        $this->email_accounts = $email_accounts;
        $this->bs = $bs;
    }

    /**
     * An exception will normally result in the email being retried. However, retuning 0
     * here will usually mark the email as failed.
     *
     * So exceptions = unexpected failure.
     * Return 0 = error that is unlikely to be temporary
     *
     * @param array $sendmail
     * @return int Number of emails sent
     */
    public function send(array $sendmail)
    {
        $this->last_log = array();

        if (empty($sendmail['email_account_id']) || !$sendmail['email_account_id']) {
            $this->addLogMessage(sprintf("The email account that this email was sent with no longer exists"));
            return 0;
        }

        try {
            $account = $this->email_accounts->getActiveAccount($sendmail['email_account_id']);
        } catch (\Exception $e) {
            $this->addLogMessage(sprintf("Email account %d does not exist or has been disabled", $sendmail['email_account_id']));
            return 0;
        }

        try {
            $tr = $this->email_accounts->getTransportForAccount($account);
        } catch (\Exception $e) {
            $this->addLogMessage(sprintf("Email account has no transport: %s", $e->getMessage()));
            return 0;
        }

        if ($tr instanceof \Swift_SmtpTransport) {
            $raw_tr = new RawSmtpTransport($tr);
        } else if ($tr instanceof \Swift_Transport) {
            $raw_tr = new RawSwiftmailerTransport($tr, new Rfc2822Decoder());
        } else {
            $this->addLogMessage(sprintf("Transport type does not support retries: %s", get_class($tr)));
            return 0;
        }

        $fp = fopen('php://temp/maxmemory:10000000', 'rw');
        try {
            if (!fwrite($fp, $this->bs->copyBlobRowIdToString($sendmail['blob_id']))) {
                $this->addLogMessage(sprintf("Failed writing source blob"));
                throw new \RuntimeException(sprintf("Failed writing source blob"));
            }
        } catch (\InvalidArgumentException $e) {
            $this->addLogMessage(sprintf("Email source blob does not exist"));
        }

        try {
            $log_messages = array();
            $failed = array();
            $sent = $raw_tr->sendRawMessage(
                $sendmail['from_email'],
                Arrays::removeFalsey(array_merge(
                    explode(',', $sendmail['to_emails'] ?: ''),
                    explode(',', $sendmail['cc_emails'] ?: ''),
                    explode(',', $sendmail['bcc_emails'] ?: '')
                )),
                $fp,
                $failed,
                $log_messages
            );
            if ($log_messages) $this->addTrLogMessages($log_messages);

            if ($failed) {
                $this->addLogMessage(sprintf('NOTICE: Failed recipients: %s', implode(', ', $failed)));
            }

        } catch (\Exception $e) {
            if ($log_messages) $this->addTrLogMessages($log_messages);
            @fclose($fp);
            throw $e;
        }

        return $sent;
    }

    /**
     * @param string $message
     */
    private function addLogMessage($message)
    {
        $this->last_log[] = sprintf('[%s] %s', date('Y-m-d H:i:s'), $message);
    }

    /**
     * @param array $lines
     */
    private function addTrLogMessages(array $lines)
    {
        foreach ($lines as $l) $this->last_log[] = $l;
    }

    /**
     * @return string
     */
    public function getLastLog()
    {
        return implode("\n", $this->last_log);
    }

    /**
     * @return \string[]
     */
    public function getLastLogAsArray()
    {
        return $this->last_log;
    }
}