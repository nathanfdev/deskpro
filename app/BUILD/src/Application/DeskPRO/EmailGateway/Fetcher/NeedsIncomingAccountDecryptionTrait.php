<?php
/**
 * Copyright (c) DeskPRO.
 */

namespace Application\DeskPRO\EmailGateway\Fetcher;

use Application\DeskPRO\App;
use Application\DeskPRO\Email\EmailAccount\AccountConfigInterface;
use Application\DeskPRO\Email\EmailAccount\EmailAccountUtil;

/**
 * Trait NeedsIncomingAccountDecryptionTrait
 * @package Application\DeskPRO\EmailGateway\Fetcher
 */
trait NeedsIncomingAccountDecryptionTrait
{
    /**
     * Decrypt incoming account settings
     *
     * @return AccountConfigInterface
     * @throws \CannotPerformOperationException
     * @throws \InvalidCiphertextException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     */
    protected function decryptIncomingAccount()
    {
        // decrypt connection password
        return EmailAccountUtil::decryptIncomingAccount(
            $this->account->incoming_account,
            App::$container->get('dp_enc')
        );
    }    
}
