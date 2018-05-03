<?php

namespace Application\DeskPRO\EmailGateway\Reader;

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\Email\EmailAccount\EmailAccountManager;
use DeskPRO\Bundle\AppBundle\AppEnv\AppEnv;

class EzcReaderFactory
{
    /**
     * EzcReaderFactory constructor.
     *
     * @param EmailAccountManager $emailAccountManager
     * @param DeskproBlobStorage  $blobStorage
     * @param AppEnv              $environment
     */
    public function __construct(EmailAccountManager $emailAccountManager, DeskproBlobStorage $blobStorage, AppEnv $environment)
    {
        $this->emailAccountManager = $emailAccountManager;
        $this->blobStorage         = $blobStorage;
        $this->environment         = $environment;
    }

    /**
     * @return EzcReader
     */
    public function create()
    {
        return new EzcReader(
            $this->emailAccountManager,
            $this->blobStorage,
            $this->environment
        );
    }
}
