<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
     * @param \DateTime          $send_date When to send the message. If not specified, it will be sent the next time the processor is run.
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
