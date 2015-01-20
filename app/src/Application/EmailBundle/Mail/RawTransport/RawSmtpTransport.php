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

namespace Application\EmailBundle\SwiftMailer\RawTransport;

/**
 * The RawSmtpTransport is an efficient wrapper around the SmtpTransport.
 * It is able to use the pre-computed raw message and send it directly over the
 * network. Other raw transports need to decode and re-create the message from scratch first.
 */
class RawSmtpTransport implements RawTransportInterface
{
    /**
     * @var \Swift_SmtpTransport
     */
    private $tr;

    /**
     * @param \Swift_SmtpTransport $tr
     */
    public function __construct(\Swift_SmtpTransport $tr)
    {
        $this->tr = $tr;
    }

    /**
     * @param string $from The account to send from (for use with SMTP 'MAIL FROM')
     * @param array $to Array of email addresses to send to (for use with SMTP 'RCPT TO')
     * @param array $cc Array of CC'd email addresses to send to (for use with SMTP 'RCPT TO')
     * @param array $bcc Array of BCC'd email addresses to send to (for use with SMTP 'RCPT TO')
     * @param resource $raw_fp A file pointer to the raw email source
     * @param array $failed Array of failed recipients, if any
     * @return int
     */
    public function sendRawMessage($from, array $to = null, array $cc = null, array $bcc = null, $raw_fp, array &$failed = null)
    {
        $sent = 0;

        if ($failed === null) {
            $failed = array();
        }

        $this->tr->start();

        if (!empty($to))  $sent += $this->_doMail($from, $to, $raw_fp, $failed);
        if (!empty($cc))  $sent += $this->_doMail($from, $cc, $raw_fp, $failed);
        if (!empty($bcc)) $sent += $this->_doMail($from, $bcc, $raw_fp, $failed);

        return $sent;
    }

    /**
     * @param $from
     * @param array $to
     * @param $raw_fp
     * @param array $failed
     * @return int
     */
    private function _doMail($from, array $to, $raw_fp, array &$failed)
    {
        $this->tr->executeCommand(sprintf("MAIL FROM: <%s>\r\n", $from), array(250));
        $sent = 0;

        foreach ($to as $addy) {
            try {
                $this->tr->executeCommand(sprintf("RCPT TO: <%s>\r\n", $addy), array(250, 251, 252));
                $sent++;
            } catch (\Swift_TransportException $e) {
                $failed[] = $addy;
            }
        }

        if ($sent) {
            $this->tr->executeCommand("DATA\r\n", array(354));

            $buf = $this->tr->getBuffer();
            $buf->setWriteTranslations(array("\r\n." => "\r\n.."));

            rewind($raw_fp);
            while (!feof($raw_fp)) {
                $buf->write(fread($raw_fp, 4096));
            }

            $buf->flushBuffers();

            $buf->setWriteTranslations(array());
            $this->tr->executeCommand("\r\n.\r\n", array(250));
        } else {
            $this->tr->reset();
            return 0;
        }

        return $sent;
    }
}