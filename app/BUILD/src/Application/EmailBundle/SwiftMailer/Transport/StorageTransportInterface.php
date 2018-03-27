<?php

/**
 * Created by IntelliJ IDEA.
 * User: chroder
 * Date: 16/01/15
 * Time: 16:01.
 */

namespace Application\EmailBundle\SwiftMailer\Transport;

use Swift_Mime_Message;

interface StorageTransportInterface
{
    /**
     * Queue the message so it is sent by the queue processor.
     *
     * @param Swift_Mime_Message $message
     * @param \DateTime          $send_date When to send the message. If not specified, it will be sent the next time the processor is run
     *
     * @return int
     */
    public function queueMessage(Swift_Mime_Message $message, \DateTime $send_date = null);

    /**
     * Save the message to storage as 'inserted' statge.
     *
     * @param Swift_Mime_Message $message
     *
     * @return int
     */
    public function insertMessage(Swift_Mime_Message $message);
}
