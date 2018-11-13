<?php

/**
 * Copyright (c) DeskPRO.
 */

/**
 * Orb.
 */

namespace Orb\Log\Writer;

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\EmailAccount;
use DeskPRO\Bundle\AppBundle\Entity\EmailAccountLog;
use Doctrine\ORM\EntityManagerInterface;
use DpSys\LowError\SystemErrorHandler;
use Orb\Log\LogItem;

/**
 * Log writer to save all email gateway proceccing logs.
 *
 * Class EmailGatewayLogWriter
 */
class EmailGatewayLogWriter extends AbstractWriter
{
    /**
     * Entity Manager.
     *
     * @var EntityManagerInterface
     */
    protected $entityManager;

    protected $dpBlobStorage;

    /**
     *  Email account.
     *
     * @var EmailAccount
     */
    protected $emailAccount;

    /**
     * Logger entity.
     *
     * @var EmailAccountLog
     */
    protected $emailAccountLog;

    /**
     * Log messages.
     *
     * @var array
     */
    protected $messages = [];

    /**
     * Number of fetched sources.
     *
     * @var int
     */
    protected $totalfetchedSources = 0;

    /**
     * EmailGatewayLog constructor.
     *
     * @param EmailAccount           $emailAccount
     * @param EntityManagerInterface $entityManager
     * @param DeskproBlobStorage     $dpBlobStorage
     */
    public function __construct(
        EmailAccount $emailAccount,
        EntityManagerInterface $entityManager,
        DeskproBlobStorage $dpBlobStorage
    ) {
        // set email account
        $this->emailAccount = $emailAccount;

        // set entity manager
        $this->entityManager = $entityManager;

        // set blob storage
        $this->dpBlobStorage = $dpBlobStorage;

        // add simple line formatter
        $this->addFilter(new \Orb\Log\Filter\SimpleLineFormatter());
    }

    /**
     * Write a message to the log.
     *
     * @param LogItem $log_item
     */
    public function _write(LogItem $log_item)
    {
        $this->messages[] = $log_item[LogItem::MESSAGE_LINE];
    }

    /**
     * Get log writer entity.
     *
     * @throws \InvalidArgumentException
     *
     * @return EmailAccountLog
     */
    public function getLoggerEntity()
    {
        if (!$this->emailAccountLog instanceof EmailAccountLog) {
            $this->emailAccountLog = new EmailAccountLog(
                $this->emailAccount, $this->emailAccount->incoming_account->getType()
            );

            $this->entityManager->persist($this->getLoggerEntity());
            $this->entityManager->flush();
        }

        return $this->emailAccountLog;
    }

    /**
     * Set total fetched sources.
     *
     * @param int $totalFetchedSources
     *
     * @return EmailGatewayLogWriter
     */
    public function setTotalFetchedSources($totalFetchedSources = 0)
    {
        $this->totalfetchedSources = $totalFetchedSources;

        return $this;
    }

    /**
     * Increment total received emails counter.
     *
     * @return EmailGatewayLogWriter
     */
    public function incrementTotalFetchedSources()
    {
        $this->totalfetchedSources = $this->totalfetchedSources + 1;

        return $this;
    }

    /**
     * Can be called manually to flush any cached entries.
     */
    public function flush()
    {
        try {
            if (!$this->getLoggerEntity()->getBlob() instanceof Blob) {
                $blob = $this->dpBlobStorage->createBlobRecordFromString(
                    implode(PHP_EOL, $this->messages),
                    'email-gateway-runner.account-'.$this->emailAccount->getId().'.log',
                    'plain/text',
                    [
                        'prefer_gzipped' => true,
                        'tag'            => 'logs.email_gateway_runner_log',
                    ]
                );

                if ($blob instanceof Blob) {
                    $this->getLoggerEntity()->setBlob($blob);
                    $this->getLoggerEntity()->setNumEmails($this->totalfetchedSources);

                    $this->entityManager->persist($this->getLoggerEntity());
                    $this->entityManager->flush();
                }
            }
        } catch (\Exception $e) {
            SystemErrorHandler::logException($e);
        }
    }
}
