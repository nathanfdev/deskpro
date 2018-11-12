<?php

/**
 * DeskPRO.
 *
 * @category EmailGateway
 */

namespace Application\DeskPRO\EmailGateway\TicketGateway;

use Application\DeskPRO\EmailGateway\InlineImageTokens;
use Application\DeskPRO\Entity\Ticket;
use Orb\Input\Cleaner\Cleaner;
use Orb\Log\Logger;
use Orb\Util\Strings;

class TicketIncomingEmailMessageV3 extends TicketIncomingEmailMessage
{
    /**
     * @var string
     */
    public $subject;

    /**
     * @var string
     */
    public $body;

    /**
     * @var bool
     */
    public $body_is_html;

    /**
     * @var string
     */
    public $body_raw;

    /**
     * @var string
     */
    public $body_full;

    /**
     * @var string
     */
    public $generic_cut;

    /**
     * @var bool
     */
    public $found_top_marker;

    /**
     * @var string
     */
    public $charset_error;

    /**
     * @var bool
     */
    public $agent_reply_as_note = true;

    /**
     * @param Ticket              $ticket
     * @param TicketIncomingEmail $ticket_email
     * @param Cleaner             $cleaner
     * @param null                $token_replace_callback
     * @param null                $process_blobs_callback
     * @param Logger              $logger
     */
    public function __construct(
        Ticket $ticket = null,
        TicketIncomingEmail $ticket_email,
        Cleaner $cleaner,
        $token_replace_callback = null,
        $process_blobs_callback = null,
        Logger $logger = null)
    {
        if ($logger) {
            $this->setLogger($logger);
        }

        $reader        = $ticket_email->reader;
        $this->subject = $reader->getSubject()->getSubjectUtf8();
        if (!$this->subject && $reader->getSubject()->getSubject()) {
            $this->subject = $reader->getSubject()->getSubject();
        }

        $this->body_is_html = false;

        $inline_images = new InlineImageTokens($reader);

        $this->logMessage('[TicketIncomingEmailMessageV3] Processing DP3 reply text');

        if ($ticket_email->email_body_text) {
            $this->logMessage('[TicketIncomingEmailMessageV3] read text email');
            $txt = $ticket_email->email_body_text;
            if (!$txt && $ticket_email->email_body_text) {
                $txt                 = $ticket_email->email_body_text;
                $this->charset_error = $reader->getBodyText()->getOriginalCharset();
            }

            $this->body = $txt;
        } else {
            $this->logMessage('[TicketIncomingEmailMessageV3] read HTML email');
            $this->body = $ticket_email->email_body_html;
            if (!$this->body) {
                $this->body          = strip_tags($ticket_email->email_body_html);
                $this->charset_error = $reader->getBodyHtml()->getOriginalCharset();
            }

            // Replace inline image tags with tokens
            $this->body = $inline_images->processTokens($this->body);

            if ($process_blobs_callback) {
                // We need to call ProcessAbstract::processBlobs
                // between InlineImageTokens::processTokens and ProcessAbstract::replaceInlineAttachTokens
                // so, call sequence: InlineImageTokens::processTokens -> ProcessAbstract::processBlobs -> ProcessAbstract::replaceInlineAttachTokens
                // because
                // .. `processTokens` mark reader->attachments inline/not inline
                // .. depending from this `processBlobs` creates attachments tagged as `ticket_attachment` or not
                // .. `replaceInlineAttachTokens` need data from `processBlobs`
                call_user_func($process_blobs_callback);
            }
        }

        $this->body_raw = $this->body;

        $agent_pos_1 = strpos($this->body, '=== Enter your reply below this line ===');
        $agent_pos_2 = strpos($this->body, '=== Enter your reply above this line ===');

        //------------------------------
        // Agent markers
        //------------------------------

        if ($agent_pos_1 !== false && $agent_pos_2 !== false) {
            $this->logMessage('[TicketIncomingEmailMessageV3] read agent markers');
            $this->body = Strings::getBetweenBoundary(
                $this->body,
                '=== Enter your reply below this line ===',
                '=== Enter your reply above this line ==='
            );

        //------------------------------
        // User email
        //------------------------------
        } else {
            $this->logMessage('[TicketIncomingEmailMessageV3] no agent markers, must be a user email');

            // Old DP3 bug had 'ABOVE above'
            $this->body = str_replace('Please enter your reply ABOVE above this line', 'Please enter your reply ABOVE this line', $this->body);

            $user_pos_1 = strpos($this->body, '========= Please enter your reply ABOVE this line =========');
            if ($user_pos_1 !== false) {
                $this->logMessage('[TicketIncomingEmailMessageV3] read user markers');
                $this->body = Strings::getAboveBoundary(
                    $this->body,
                    '========= Please enter your reply ABOVE this line ========='
                );
            }
        }

        $cut        = new \Application\DeskPRO\EmailGateway\Cutter\Def\Generic();
        $this->body = $cut->cutQuoteBlock($this->body, $this->body_is_html);

        $this->body = trim($this->body, " >\n\r");

        $this->body = Strings::text2html($this->body, 'plaintext-email');

        if ($token_replace_callback) {
            $this->body = call_user_func($token_replace_callback, $this->body, $inline_images);
        }

        $this->body_full = $this->body;
    }
}
