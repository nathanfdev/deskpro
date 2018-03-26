<?php

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\SwiftMailer\Message;

interface MessageFactoryInterface
{
    /**
     * @param string $type
     *
     * @return \Swift_Message
     */
    public function createMessage($type);
}
