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
use Application\EmailBundle\Mail\RawMessage\RawMessageDecoderInterface;

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
     * @param \Swift_Transport $tr
     * @param \Application\EmailBundle\Mail\RawMessage\RawMessageDecoderInterface $decoder
     */
    public function __construct(\Swift_Transport $tr, RawMessageDecoderInterface $decoder)
    {
        $this->tr = $tr;
        $this->decoder = $decoder;
    }

    /**
     * {@inheritDoc}
     */
    public function sendRawMessage($from, array $tos, $raw_fp, array &$failed = null)
    {
        $message = $this->recreateSwiftMessage($from, $tos, $raw_fp);
        return $this->tr->send($message, $failed);
    }

    /**
     * @param string $from
     * @param array $send_tos
     * @param resource $raw_fp
     * @return \Swift_Message
     */
    private function recreateSwiftMessage($from, array $send_tos = null, $raw_fp)
    {
        $raw_message = $this->decoder->createRawMessage($raw_fp);

        $message = \Swift_Message::newInstance();

        #------------------------------
        # From
        #------------------------------

        $from = $raw_message->getFrom();
        if ($from) {
            $message->setFrom($from['email'], $from['name']);
        }

        #------------------------------
        # Recipients
        #------------------------------

        $included_tos = array();

        foreach ($raw_message->getTos() as $to) {
            $message->addTo($to['email'], $to['name']);
            $included_tos[] = strtolower($to['email']);
        }
        foreach ($raw_message->getCcs() as $to) {
            $message->addCc($to['email'], $to['name']);
            $included_tos[] = strtolower($to['email']);
        }

        $bccs = array_diff($send_tos, $included_tos);
        if ($bccs) {
            foreach ($bccs as $bcc) {
                $message->addBcc($bcc);
            }
        }

        #------------------------------
        # Message
        #------------------------------

        $text_body = $raw_message->getTextPart();
        $html_body = $raw_message->getHtmlPart();

        // Empty body, default to just empty string so it'll send
        if ($text_body === null && $html_body === null) {
            $text_body = '';
        }

        if ($text_body !== null && $html_body !== null) {
            $message->setBody($text_body, 'text/plain');
            $message->addPart($html_body, 'text/html');
        } else {
            if ($text_body !== null) {
                $message->setBody($text_body, 'text/plain');
            } else {
                $message->setBody($html_body, 'text/html');
            }
        }

        #------------------------------
        # Attachments
        #------------------------------

        foreach ($raw_message->getAttachments() as $attach) {
            $message->attach(\Swift_Attachment::newInstance()
                ->setId($attach['cid'])
                ->setFilename($attach['filename'])
                ->setContentType($attach['type'])
                ->setBody($attach['bin_data'])
            );
        }

        #------------------------------
        # Headers
        #------------------------------

        $headers = $message->getHeaders();
        foreach ($raw_message->getHeaders() as $header_name => $header_values) {
            $headers->removeAll($header_name);
            foreach ($header_values as $v) {
                $headers->addTextHeader($header_name, $v);
            }
        }

        #------------------------------
        # Done
        #------------------------------

        return $message;
    }
}