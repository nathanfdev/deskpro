<?php

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\SwiftMailer\Message;

interface MessageOptionsInterface
{
    /**
     * Hint to use a specific account.
     */
    const OPT_ACCOUNT_ID = 'account_id';

    /**
     * Hint to use a specific from address (i.e., ignore the email address set on the outgoing account).
     */
    const OPT_USE_FROM = 'use_from';

    /**
     * @return \Orb\Util\OptionsArray
     */
    public function getMessageOptions();
}
