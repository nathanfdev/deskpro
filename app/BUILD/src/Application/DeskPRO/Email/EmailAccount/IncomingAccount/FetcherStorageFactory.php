<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Email\EmailAccount\IncomingAccount;

use Application\DeskPRO\Email\EmailAccount\AccountConfigInterface;
use Application\DeskPRO\EmailGateway\FetcherStorage\FetcherStorageInterface;
use Application\DeskPRO\EmailGateway\FetcherStorage\Pop3Storage;

//TODO this is not actually used in the Runner
class FetcherStorageFactory
{
    /**
     * @param AccountConfigInterface $config
     *
     * @throws \InvalidArgumentException
     *
     * @return FetcherStorageInterface
     */
    public function createFetcherStorage(AccountConfigInterface $config)
    {
        switch ($config->getType()) {
            case 'pop3':  return $this->createPop3Fetcher($config);
            case 'gmail': return $this->createGmailFetcherStorage($config);
            case 'office365': return $this->createOffice365FetcherStorage($config);
            default:
                throw new \InvalidArgumentException("Unknown incoming account type: {$config->getType()}");
        }
    }

    /**
     * @param Pop3Config $config
     *
     * @return Pop3Storage
     */
    public function createPop3FetcherStorage(Pop3Config $config)
    {
        return new Pop3Storage(
            $config->host,
            $config->port,
            $config->user,
            $config->password,
            $config->secure_mode
        );
    }

    /**
     * @param GmailConfig $config
     *
     * @return Pop3Storage
     */
    public function createGmailFetcherStorage(GmailConfig $config)
    {
        return new Pop3Storage(
            'pop.gmail.com',
            995,
            $config->user,
            $config->password,
            'ssl'
        );
    }

    /**
     * @param Office365Config $config
     *
     * @return Pop3Storage
     */
    public function createOffice365FetcherStorage(Office365Config $config)
    {
        return new Pop3Storage(
            'outlook.office365.com',
            995,
            $config->user,
            $config->password,
            'ssl'
        );
    }
}
