<?php

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\Mail\RawTransport;

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
     * {@inheritdoc}
     */
    public function sendRawMessage($from, array $tos, $raw_fp, array &$failed = null)
    {
        $sent = 0;

        if ($failed === null) {
            $failed = [];
        }

        try {
            $this->tr->start();
            if (!empty($tos)) {
                $sent += $this->_doMail($from, $tos, $raw_fp, $failed);
            }
        } catch (\Swift_TransportException $e) {
            $raw_e = new RawTransportException($e->getMessage(), $e->getCode(), $e);
            throw $raw_e;
        }

        return $sent;
    }

    /**
     * @param $from
     * @param array $to
     * @param $raw_fp
     * @param array $failed
     *
     * @return int
     */
    private function _doMail($from, array $to, $raw_fp, array &$failed)
    {
        $this->tr->executeCommand(sprintf("MAIL FROM: <%s>\r\n", $from), [250]);
        $sent = 0;

        foreach ($to as $addy) {
            try {
                $this->tr->executeCommand(sprintf("RCPT TO: <%s>\r\n", $addy), [250, 251, 252]);
                ++$sent;
            } catch (\Swift_TransportException $e) {
                $failed[] = $addy;
            }
        }

        if ($sent) {
            $this->tr->executeCommand("DATA\r\n", [354]);

            $buf = $this->tr->getBuffer();
            $buf->setWriteTranslations(["\r\n." => "\r\n.."]);

            rewind($raw_fp);
            while (!feof($raw_fp)) {
                $chunk = fread($raw_fp, 4096);

                if ($chunk !== false && $chunk !== '') {
                    $buf->write($chunk);
                }
            }

            $buf->flushBuffers();

            $buf->setWriteTranslations([]);
            $this->tr->executeCommand("\r\n.\r\n", [250]);
        } else {
            $this->tr->reset();

            return 0;
        }

        return $sent;
    }
}
