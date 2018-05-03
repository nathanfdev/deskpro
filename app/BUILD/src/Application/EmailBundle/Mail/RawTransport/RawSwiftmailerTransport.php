<?php

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\Mail\RawTransport;

use Application\EmailBundle\Mail\RawMessage\RawMessageDecoderInterface;
use Application\EmailBundle\Mail\RawMessage\RawMessageUtil;

/**
 * This is a generic wrapper for any swiftmailer transport.
 * It works by decoding a raw message and re-creating a swiftmailer message
 * that we can send via the usual way with swiftmailer.
 */
class RawSwiftmailerTransport implements RawTransportInterface
{
    /**
     * @var \Swift_Transport
     */
    private $tr;

    /**
     * @var \Application\EmailBundle\Mail\RawMessage\RawMessageDecoderInterface
     */
    private $decoder;

    /**
     * @param \Swift_Transport                                                    $tr
     * @param \Application\EmailBundle\Mail\RawMessage\RawMessageDecoderInterface $decoder
     */
    public function __construct(\Swift_Transport $tr, RawMessageDecoderInterface $decoder)
    {
        $this->tr      = $tr;
        $this->decoder = $decoder;
    }

    /**
     * {@inheritdoc}
     */
    public function sendRawMessage($from, array $tos, $raw_fp, array &$failed = null)
    {
        $message = $this->recreateSwiftMessage($from, $tos, $raw_fp);

        try {
            $this->tr->start();
            $sent = $this->tr->send($message, $failed);
        } catch (\Swift_TransportException $e) {
            $rawException = new RawTransportException($e->getMessage(), $e->getCode(), $e);
            throw $rawException;
        }

        return $sent;
    }

    /**
     * @param string   $from
     * @param array    $send_tos
     * @param resource $raw_fp
     *
     * @return \Swift_Message
     */
    private function recreateSwiftMessage($from, array $send_tos = null, $raw_fp)
    {
        $rawMessage = $this->decoder->createRawMessage($raw_fp);
        $message    = \Swift_Message::newInstance();
        RawMessageUtil::applyRawToSwift($rawMessage, $message, $send_tos);

        //------------------------------
        // Done
        //------------------------------

        return $message;
    }
}
