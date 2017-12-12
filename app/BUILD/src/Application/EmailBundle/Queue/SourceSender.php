<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\Queue;

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\Email\EmailAccount\EmailAccountManager;
use Application\EmailBundle\Mail\RawTransport\RawTransportException;
use Orb\Util\Arrays;
use Orb\Util\Util;
use Psr\Log\LoggerInterface;

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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param EmailAccountManager $email_accounts
     * @param DeskproBlobStorage  $bs
     * @param LoggerInterface     $logger
     */
    public function __construct(EmailAccountManager $email_accounts, DeskproBlobStorage $bs, LoggerInterface $logger)
    {
        $this->email_accounts = $email_accounts;
        $this->bs             = $bs;
        $this->logger         = $logger;
    }

    /**
     * An exception will normally result in the email being retried. However, retuning 0
     * here will usually mark the email as failed.
     *
     * So exceptions = unexpected failure.
     * Return 0 = error that is unlikely to be temporary
     *
     * @param array $sendmail
     *
     * @return int Number of emails sent
     */
    public function send(array $sendmail)
    {
        if (empty($sendmail['email_account_id']) || !$sendmail['email_account_id']) {
            $this->logger->error(sprintf('The email account that this email was sent with no longer exists'));

            return 0;
        }

        try {
            $account = $this->email_accounts->getActiveAccount($sendmail['email_account_id']);
            $this->logger->info(sprintf('Using account #%d %s', $account->getId(), $account->getUseEmailAddress()));
        } catch (\Exception $e) {
            $this->logger->error(sprintf('Email account %d does not exist or has been disabled', $sendmail['email_account_id']));

            return 0;
        }

        try {
            $raw_tr = $this->email_accounts->getTransportForAccount($account);
            $this->logger->debug(sprintf('Using transport type: %s', Util::getBaseClassname($raw_tr)));
        } catch (\Exception $e) {
            $this->logger->error(sprintf('Email account has no transport: %s', $e->getMessage()));

            return 0;
        }

        $fp = fopen('php://temp/maxmemory:10000000', 'rw');
        try {
            if (!fwrite($fp, $this->bs->copyBlobRowIdToString($sendmail['blob_id']))) {
                $this->logger->error(sprintf('Failed writing source blob'));
                throw new RawTransportException(sprintf('Failed writing source blob'));
            }
            rewind($fp);
        } catch (\InvalidArgumentException $e) {
            $this->logger->error(sprintf('Email source blob does not exist'));
            throw new \RuntimeException(sprintf('Email source blob does not exist'));
        }

        try {
            $failed = [];
            $sent   = $raw_tr->sendRawMessage(
                $sendmail['from_email'],
                Arrays::removeFalsey(array_merge(
                    explode(',', $sendmail['to_emails'] ?: ''),
                    explode(',', $sendmail['cc_emails'] ?: ''),
                    explode(',', $sendmail['bcc_emails'] ?: '')
                )),
                $fp,
                $failed
            );

            if ($failed) {
                $this->logger->notice(sprintf('NOTICE: Failed recipients: %s', implode(', ', $failed)));
            }

            $this->logger->info(sprintf('Sent %d messages', $sent));
        } catch (RawTransportException $e) {
            $this->logger->error(sprintf('Exception raised: %s [%s]: %s', get_class($e), $e->getCode(), $e->getMessage()));
            @fclose($fp);

            throw $e;
        } catch (\Exception $e) {
            @fclose($fp);
            throw $e;
        }

        return $sent;
    }
}
