<?php

namespace DeskPRO\Bundle\AppBundle\EventListener;

/**
 * Class PushOutgoingSqsEmailMessages
 *
 * @package DeskPRO\Bundle\AppBundle\EventListener
 */
class PushOutgoingSqsEmailMessages
{
    /**
     * Push outgoing SQS messages
     */
    public function pushMessages()
    {
        \DpShutdown::run('dp_push_outgoing_sqs_emails');
    }
}
