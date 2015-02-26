<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage EmailBundle
 */

namespace Application\EmailBundle\Mail\RawTransport;

/**
 * A raw transport takes params for a raw email message and sends it.
 *
 * Typically a raw transport wraps an SwiftMailer transport but is able to send
 * raw rfc288 messages.
 */
interface RawTransportInterface
{
    /**
     * Send a raw RFC2822 message.
     *
     * NOTE: Note that the $from and $tos address here are JUST addresses (e.g., no name parts).
     * These addresses describe mailboxes when talking to an SMTP server.
     *
     * The $tos parameter must be an array of ALL targets. This is a sum of tos, ccs and bccs.
     *
     * If you send a message to a person who is not actually listed in the headers of the email,
     * then that is essentially a BCC.
     *
     * @param string   $from          The account to send from (for use with SMTP 'MAIL FROM')
     * @param array    $tos           Array of email addresses to send to (for use with SMTP 'RCPT TO')
     * @param resource $raw_fp        A file pointer to the raw email source
     * @param array    $failed        Array of failed recipients, if any
     * @return int
     */
    public function sendRawMessage($from, array $tos, $raw_fp, array &$failed = null);
}