<?php

/**
 * DeskPRO.
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
     * @param string   $from   The account to send from (for use with SMTP 'MAIL FROM')
     * @param array    $tos    Array of email addresses to send to (for use with SMTP 'RCPT TO')
     * @param resource $raw_fp A file pointer to the raw email source
     * @param array    $failed Array of failed recipients, if any
     *
     * @return int
     */
    public function sendRawMessage($from, array $tos, $raw_fp, array &$failed = null);
}
