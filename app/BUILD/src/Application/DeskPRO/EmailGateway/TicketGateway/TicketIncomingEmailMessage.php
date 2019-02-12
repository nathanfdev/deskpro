<?php

/**
 * DeskPRO.
 *
 * @category EmailGateway
 */

namespace Application\DeskPRO\EmailGateway\TicketGateway;

use Application\DeskPRO\App;
use Application\DeskPRO\Email\EmailAccount\EmailAccountManager;
use Application\DeskPRO\EmailGateway\InlineImageTokens;
use Application\DeskPRO\EmailGateway\LinkedImages;
use Application\DeskPRO\Entity\Ticket;
use Orb\Input\Cleaner\Cleaner;
use Orb\Log\Logger;
use Orb\Util\Arrays;
use Orb\Util\Strings;

class TicketIncomingEmailMessage
{
    const MODE_NEWTICKET = 'newticket';
    const MODE_NEWREPLY  = 'newreply';

    /**
     * @var string
     */
    public $subject;

    /**
     * @var bool
     */
    public $is_no_subject = false;

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
     * @var \Orb\Log\Logger
     */
    private $logger;

    /**
     * @var EmailAccountManager
     */
    private $email_accounts;

    /**
     * @var string
     */
    private $mode;

    /**
     * @var bool
     */
    public $agent_reply_as_note = true;

    /**
     * @var bool
     */
    public $agent_reply_mode_foundflag = false;

    /**
     * @param                     $mode
     * @param Ticket              $ticket
     * @param TicketIncomingEmail $ticket_email
     * @param Cleaner             $cleaner
     * @param EmailAccountManager $email_accounts
     * @param null                $token_replace_callback
     * @param null                $process_blobs_callback
     * @param Logger              $logger
     */
    public function __construct(
        $mode,
        Ticket $ticket = null,
        TicketIncomingEmail $ticket_email,
        Cleaner $cleaner,
        EmailAccountManager $email_accounts,
        $token_replace_callback = null,
        $process_blobs_callback = null,
        Logger $logger = null)
    {
        if ($logger) {
            $this->setLogger($logger);
        }

        $this->mode = $mode;
        $this->logMessage('[TicketIncomingEmailMessage] mode = '.$mode);

        $this->email_accounts = $email_accounts;

        $reader = $ticket_email->reader;

        $this->subject = $reader->getSubject()->getSubjectUtf8();
        if (!$this->subject && $reader->getSubject()->getSubject()) {
            $this->subject = $reader->getSubject()->getSubject();
        }

        if (!$this->subject) {
            $this->is_no_subject = true;
            $this->subject       = '(No Subject)';
        }

        $inline_images  = new InlineImageTokens($reader);
        $inline_images2 = new InlineImageTokens($reader);

        if ($ticket_email->force_reply_cutter) {
            $this->logMessage('[TicketIncomingEmailMessage] do_cut=true because force_reply_cutter is on');
            $do_cut = true;
        } elseif ($ticket_email->force_no_reply_cutter) {
            $this->logMessage('[TicketIncomingEmailMessage] do_cut=false because force_no_reply_cutter is on');
            $do_cut = false;
        } elseif ($mode == self::MODE_NEWREPLY) {
            $this->logMessage('[TicketIncomingEmailMessage] do_cut=true because mode = newreply');
            $do_cut = true;
        } else {
            $this->logMessage('[TicketIncomingEmailMessage] do_cut=false');
            $do_cut = false;
        }

        if ($do_cut && $ticket_email->force_no_pattern_cutter) {
            $this->logMessage('[TicketIncomingEmailMessage] Note: Cutter is enabled but the pattern cutters are disabled');
        }

        $orig_text     = $ticket_email->email_body_text;
        $did_html_trim = false;
        $is_text       = false;
        $has_text_cut  = false;
        $has_cut       = false;

        $cutters_require_from = Arrays::flatten(array_map(function ($e) {
            return [$e->address, $e->other_addresses];
        }, $this->email_accounts->getAllAccounts()));

        $precut_do_plaintext = false;

        if ($ticket_email->email_body_html) {
            $this->logMessage('[TicketIncomingEmailMessage] read HTML email');
            $this->body = $ticket_email->email_body_html;
            if (!$this->body) {
                $this->body          = $ticket_email->email_body_html;
                $this->charset_error = $reader->getBodyHtml()->getOriginalCharset();
            }
            $this->body_is_html = true;

            // Sent from a DeskPRO instance, we should get the specific message by looking for our delims
            // But dont do this cut if its an auto-reply, we want the real message in those cases. The actual notifs we sent
            // are silenced in those cases anyway so the auto-replies are handled like other robot replies
            // !! - TODO this needs to check for delims that aren't preceded by any other reply.
            //      Otherwise you could have a case of DeskPRO -> User -> Reply to other DeskPRO.
            //      The users reply would reach other DeskPRO and we would throw it away because we see the DP_MESSAGE_BEGIN tags
            //      but not the users reply above it.
            /*
            if (
                $reader->getHeader('X-DeskPRO-Build') && $reader->getHeader('X-DeskPRO-Build')->getHeader()
                && !($reader->getHeader('X-DeskPRO-Auto') && $reader->getHeader('X-DeskPRO-Auto')->getHeader())
            ) {
                $body = trim(Strings::extractRegexMatch('#<!\-\- DP_MESSAGE_BEGIN \-\->(.*?)<!\-\- DP_MESSAGE_END \-\->#s', $this->body, 1));
                if ($body) {
                    $this->body = $body;
                }
            }
            */

            $body_raw = $this->body;

            // If the document is too complex then htmlpurifier can crash.
            // We'll try to find a cut-mark now and trim the document down to see if we can still use it
            // (We dont always cut first because we want an in-tact 'full body' if possible)
            if (substr_count($this->body, '>') > 15000) {
                if ($do_cut) {
                    $this->logMessage('[TicketIncomingEmailMessage] Document too complex, pre-cut');

                    $cut = new \Application\DeskPRO\EmailGateway\Cutter\Def\Generic();
                    if ($ticket) {
                        $generic_cut = $cut->cutQuoteBlock($this->body, $this->body_is_html);
                    } else {
                        $generic_cut = $this->body;
                    }

                    // If we had no successful cut or the body is still too complex, use the plaintext version
                    if ($this->body == $generic_cut || substr_count($this->body, '>') > 15000) {
                        $this->logMessage('[TicketIncomingEmailMessage] Cut document still too complex, using plaintext');

                        $this->body = $ticket_email->email_body_text;
                        if ($this->body) {
                            $this->body = Strings::text2html($this->body, 'plaintext-email');
                        } else {
                            $this->body                    = Strings::html2Text($ticket_email->email_body_html);
                            $ticket_email->email_body_text = $this->body;
                            $this->body                    = nl2br($this->body);
                        }
                        $this->body_is_html = false;

                        $precut_do_plaintext = true;

                    // The trimmed document is short enough to use
                    } else {
                        $this->logMessage('[TicketIncomingEmailMessage] Using cut-trimmed document');

                        $did_html_trim      = true;
                        $this->body         = $generic_cut;
                        $this->body_is_html = true;
                    }
                } else {
                    $this->logMessage('[TicketIncomingEmailMessage] Cut document too complex, using plaintext');

                    $this->body = $ticket_email->email_body_text;
                    if ($this->body) {
                        $this->body = Strings::text2html($this->body, 'plaintext-email');
                    } else {
                        $this->body = strip_tags($ticket_email->email_body_html);
                        $this->body = Strings::text2html($this->body, 'plaintext-email');
                    }
                    $this->body_is_html = false;

                    $precut_do_plaintext = true;
                }
            }
        }

        if ($precut_do_plaintext || !$ticket_email->email_body_html) {
            $is_text = true;

            $this->logMessage('[TicketIncomingEmailMessage] read text email');
            $txt = $ticket_email->email_body_text;
            if (!$txt && $ticket_email->email_body_text) {
                $txt                 = $ticket_email->email_body_text;
                $this->charset_error = $reader->getBodyText()->getOriginalCharset();
            }

            $body_raw = @htmlspecialchars($txt, \ENT_QUOTES, 'UTF-8');

            if (strlen($txt) > 25000) {
                $this->logMessage('[TicketIncomingEmailMessage] Message too long, trimming');
                $did_html_trim = true;
                $txt           = substr($txt, 0, 25000);
            }

            $has_text_cut      = true;
            $this->body_raw    = $txt;
            $this->generic_cut = $txt;
            $this->body        = $txt;
            $this->body_full   = $txt;

            // Always generic cut from the DP_TOP_MARK position first
            // The PatternCutter will trim off the remaining quoted headers
            if ($do_cut) {
                $cut         = new \Application\DeskPRO\EmailGateway\Cutter\Def\Generic();
                $generic_cut = $cut->cutQuoteBlock($this->body, false);
                if ($this->body != $generic_cut) {
                    $this->logMessage('Generic cutter matched');
                    $this->body             = $generic_cut;
                    $this->generic_cut      = $generic_cut;
                    $this->found_top_marker = true;
                    $has_cut                = true;
                } else {
                    $this->logMessage('Generic cutter did not match');
                    $this->found_top_marker = false;
                }

                if (!$ticket_email->force_no_pattern_cutter) {
                    $cutter         = new \Application\DeskPRO\EmailGateway\Cutter\TextPatternCutter();
                    $pattern_config = new \Application\DeskPRO\Config\UserFileConfig('text-cut-patterns');
                    $cutter->addPatterns($pattern_config->all());
                    $cutter->setRequireFrom($cutters_require_from);
                    $this->logMessage('Text cutter set require from: '.implode(', ', $cutters_require_from));

                    $this->body = $cutter->cutQuoteBlock($this->body, false);

                    if ($cutter->getMatchedPatterns()) {
                        $has_text_cut = true;
                        foreach ($cutter->getMatchedPatterns() as $p) {
                            $this->logMessage('Text cutter matched pattern: '.$p->getPattern());
                        }
                    } else {
                        $this->logMessage('Text cutter did not match any pattern');
                    }

                    // Run generic cutter as well, in case it matches higher
                    $parts = $cut->splitFromFirstHeaderText($this->body);
                    if ($parts && count($parts) == 2) {
                        $this->logMessage('Split header cutter matched, cut from standard quote headers');
                        $this->body = trim($parts[0]);
                    } else {
                        $this->logMessage('Split header cutter did not match');
                    }
                }
            }

            $this->body        = Strings::utf8_bad_strip($this->body);
            $this->body_full   = Strings::utf8_bad_strip($this->body_full);
            $this->generic_cut = Strings::utf8_bad_strip($this->generic_cut);

            $this->body         = Strings::text2html($this->body, 'plaintext-email');
            $this->body_full    = Strings::text2html($this->body_full, 'plaintext-email');
            $this->generic_cut  = Strings::text2html($this->generic_cut, 'plaintext-email');
            $this->body_is_html = false;
        }

        if (!$is_text) {
            $this->body_raw = $this->body;
            $this->body     = $cleaner->clean($this->body, 'html_email_preclean');

            if ($did_html_trim) {
                // We pre-trimmed, lets set the full body to the plaintext version so we always have the full message
                $this->body_full = Strings::text2html($orig_text, 'plaintext-email');
            } else {
                $this->body_full = $this->body;
            }

            // Always generic cut from the DP_TOP_MARK position first
            // The PatternCutter will trim off the remaining quoted headers
            if ($do_cut) {
                $generic_cutter = new \Application\DeskPRO\EmailGateway\Cutter\Def\Generic();
                if ($mode == self::MODE_NEWREPLY) {
                    $generic_cut = $generic_cutter->cutQuoteBlock($this->body, $this->body_is_html);
                } else {
                    $generic_cut = $this->body;
                }

                if ($this->body != $generic_cut && trim(Strings::stripTags($generic_cut))) {
                    $this->body             = $generic_cut;
                    $this->generic_cut      = $generic_cut;
                    $this->found_top_marker = true;
                    $has_cut                = true;
                } else {
                    $this->found_top_marker = false;
                }

                if ($this->body_is_html && !$ticket_email->force_no_pattern_cutter) {
                    $cutter         = new \Application\DeskPRO\EmailGateway\Cutter\PatternCutter();
                    $pattern_config = new \Application\DeskPRO\Config\UserFileConfig('html-cut-patterns');
                    $cutter->addPatterns($pattern_config->all());

                    // We already cut, so we just want to make sure to cut the preceeding email headers
                    if ($this->found_top_marker) {
                        $cutter->setLimit(1);
                        $cutter->setMaxLinesFromEnd(18);
                        $this->logMessage('HTML cutter set limit =1 , max lines = 18');

                    // We didnt cut, so the cutline is missing so we need to guess based on our email address
                    } else {
                        $cutter->setRequireFrom($cutters_require_from);
                        $this->logMessage('HTML cutter set require from: '.implode(', ', $cutters_require_from));
                    }

                    $this->body = $cutter->cutQuoteBlock($this->body, true);

                    if ($cutter->getMatchedPatterns()) {
                        $has_cut = true;
                        foreach ($cutter->getMatchedPatterns() as $p) {
                            $this->logMessage('Cutter matched pattern: '.$p->getPattern());
                        }
                    } else {
                        $this->logMessage('Cutter did not match any pattern');
                    }
                }

                if (!trim(strip_tags($this->body))) {
                    $this->logMessage('We lost anything except tags after cutting. Fallback to raw text body to have something');
                    $this->body = nl2br($ticket_email->email_body_text); // this is an emergency case when nothing was
                    // replied
                } else {
                    $this->body .= $generic_cutter->cutBottomBlock($this->body_raw, true);
                }
            }
        }

        // Cut down the quoted message part to 10000 chars
        $cut_len  = strlen($this->body);
        $full_len = strlen($this->body_full);

        if (($full_len - $cut_len) > 19000) {
            $this->logMessage('body_full too long, trimming');
            $this->body_full = substr($this->body_full, 0, 19000 + $cut_len);

            // Simple way to try and handle if we cut in the middle of a tag name
            $tag_start_pos = strrpos($this->body_full, '<');
            if ($tag_start_pos) {
                $tag_end_pos = strrpos($this->body_full, '>');
                if ($tag_end_pos === false || $tag_end_pos < $tag_start_pos) {
                    $this->body_full = substr($this->body_full, 0, $tag_start_pos);
                }
            }

            $this->body_full .= "\n\n";
            if ($this->body_is_html) {
                $this->body_full .= '<br /><br />';
            }

            $this->body_full .= App::getTranslator()->phrase('user.emails.message-clipped');
        }

        // Replace inline image tags with tokens
        $this->body      = $inline_images->processTokens($this->body);
        $this->body_full = $inline_images2->processTokens($this->body_full);

        $linkedImages = new LinkedImages($this->logger);

        $this->body      = $linkedImages->importReplaceLinkedImages($this->body);
        $this->body_full = $linkedImages->importReplaceLinkedImages($this->body_full);

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

        if ($this->body_is_html) {
            // The basic cleaner cleans out outlook type stuff like empty <p>'s that cause whitespace
            $this->body = $cleaner->clean($this->body, 'html_email_preclean');
            $this->body = $cleaner->clean($this->body, 'html_email_basicclean');
            $this->body = $cleaner->clean($this->body, 'html_email');
            $this->body = Strings::trimHtmlAdvanced($this->body);
            $this->body = $cleaner->clean($this->body, 'html_email_postclean');
        }

        if ($token_replace_callback) {
            $this->body      = call_user_func($token_replace_callback, $this->body, $inline_images);
            $this->body_full = call_user_func($token_replace_callback, $this->body_full, $inline_images2);
        }

        // If there was no cutting, then the body is the full body
        // Dont store the dupe content
        if (!$has_cut && !$has_text_cut) {
            $this->logMessage('no cut was made, no body_full needed');
            $this->body_full = '';
        }

        $this->body_full = $cleaner->clean($this->body_full, 'html_email_basicclean');
        $this->body_full = $cleaner->clean($this->body_full, 'html_email');

        // The cut message is blank, fallback to using the full message
        if (!trim(strip_tags($this->body))) {
            if (isset($this->generic_cut) && trim(strip_tags($this->generic_cut))) {
                $this->body = $this->generic_cut;
            } else {
                $this->body      = $this->body_full;
                $this->body_full = '';
            }
        }

        // Clean out PTAC's on this ticket to prevent mistakes with forwarding
        // (Check on ticket since this can still be called from newticket if the users original ticket was closed)
        if ($ticket) {
            foreach ($ticket->access_codes as $code) {
                $this->body      = str_replace('(#'.$code->getAccessCode().')', '', $this->body);
                $this->body_full = str_replace('(#'.$code->getAccessCode().')', '', $this->body_full);
            }
        }

        $this->body_raw = $body_raw;

        $this->body      = $cleaner->clean($this->body, 'html_email_postclean');
        $this->body_raw  = $cleaner->clean($this->body_raw, 'html_email_postclean');
        $this->body_full = $cleaner->clean($this->body_full, 'html_email_postclean');

        if ($is_text && $did_html_trim) {
            $this->body_full = Strings::text2html($ticket_email->email_body_text, 'plaintext-email');
        }

        $pos_as_note  = false;
        $pos_as_reply = false;

        foreach ([$ticket_email->email_body_html, $ticket_email->email_body_text, $body_raw] as $haystack) {
            $pos_as_note  = strpos($haystack, 'DP_NEWMSG_AS_NOTE');
            $pos_as_reply = strpos($haystack, 'DP_NEWMSG_AS_REPLY');

            if ($pos_as_note !== false || $pos_as_reply !== false) {
                break;
            }
        }

        if ($pos_as_note) {
            $this->logMessage("Found DP_NEWMSG_AS_NOTE flag at $pos_as_note");
        } else {
            $this->logMessage('Did NOT find DP_NEWMSG_AS_NOTE flag');
            $pos_as_note = 0;
        }
        if ($pos_as_reply) {
            $this->logMessage("Found DP_NEWMSG_AS_REPLY flag at $pos_as_reply");
        } else {
            $this->logMessage('Did NOT find DP_NEWMSG_AS_REPLY flag');
            $pos_as_reply = 0;
        }

        if ($pos_as_note === 0 && $pos_as_reply === 0) {
            $this->logMessage('Did not find either note nor reply flag.');
            // Setting note mode here, but later in process this is overriden and
            // the actual value used is based the admin setting
            $this->agent_reply_as_note        = true;
            $this->agent_reply_mode_foundflag = false;
        } else {
            if (!$pos_as_note) {
                $pos_as_note = 999999999;
            }
            if (!$pos_as_reply) {
                $pos_as_reply = 999999999;
            }

            if ($pos_as_reply < $pos_as_note) {
                $this->logMessage('agent_reply_as_note = false -- acting as reply');
                $this->agent_reply_as_note        = false;
                $this->agent_reply_mode_foundflag = true;
            } else {
                $this->logMessage('agent_reply_as_note = true -- acting as note');
                $this->agent_reply_as_note        = true;
                $this->agent_reply_mode_foundflag = true;
            }
        }
    }

    /**
     * Set the logger.
     *
     * @param \Orb\Log\Logger $logger
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return \Orb\Log\Logger
     */
    public function getLogger()
    {
        if (!$this->logger) {
            $this->logger = new Logger();
        }

        return $this->logger;
    }

    /**
     * @param string $message
     * @param string $pri
     */
    protected function logMessage($message, $pri = 'debug')
    {
        if ($this->logger) {
            $this->logger->log($message, $pri);
        }
    }
}
