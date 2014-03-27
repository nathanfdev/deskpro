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
 * @category EmailGateway
 */

namespace Application\DeskPRO\EmailGateway\TicketGateway;

use Application\DeskPRO\EmailGateway\InlineImageTokens;
use Application\DeskPRO\Entity\Ticket;
use Orb\Input\Cleaner\Cleaner;
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
	 * @param Ticket $ticket
	 * @param TicketIncomingEmail $ticket_email
	 * @param Cleaner $cleaner
	 */
	public function __construct(Ticket $ticket, TicketIncomingEmail $ticket_email, Cleaner $cleaner)
	{
		$reader = $ticket_email->reader;
		$this->subject = $reader->getSubject()->getSubjectUtf8();
		if (!$this->subject && $reader->getSubject()->getSubject()) {
			$this->subject = $reader->getSubject()->getSubject();
		}

		$this->body_is_html = false;

		$inline_images = new InlineImageTokens($this->reader);

		$this->logMessage('[TicketIncomingEmailMessageV3] Processing DP3 reply text');

		if ($ticket_email->email_body_text) {
			$this->logMessage('[TicketIncomingEmailMessageV3] doNewReply read text email');
			$txt = $ticket_email->email_body_text;
			if (!$txt && $ticket_email->email_body_text) {
				$txt = $ticket_email->email_body_text;
				$this->charset_error = $this->reader->getBodyText()->getOriginalCharset();
			}

			$this->body = $txt;
		} else {
			$this->logMessage('[TicketIncomingEmailMessageV3] doNewReply read HTML email');
			$this->body = $ticket_email->email_body_html;
			if (!$this->body) {
				$this->body = strip_tags($ticket_email->email_body_html);
				$this->charset_error = $this->reader->getBodyHtml()->getOriginalCharset();
			}

			// Replace inline image tags with tokens
			$this->body = $inline_images->processTokens($this->body);
			$this->body_full = $inline_images->processTokens($this->body);
		}

		$this->body_raw = $this->body;
		$this->body_full = $this->body;

		$agent_pos_1 = strpos($this->body, '=== Enter your reply below this line ===');
		$agent_pos_2 = strpos($this->body, '=== Enter your reply above this line ===');

		#------------------------------
		# Agent markers
		#------------------------------

		if ($agent_pos_1 !== false && $agent_pos_2 !== false) {
			$this->body = Strings::getBetweenBoundary(
				$this->body,
				'=== Enter your reply below this line ===',
				'=== Enter your reply above this line ==='
			);

		#------------------------------
		# User email
		#------------------------------

		} else {
			$user_pos_1 = strpos($this->body, '========= Please enter your reply ABOVE this line =========');
			if ($user_pos_1 !== false) {
				$this->body = Strings::getAboveBoundary(
					$this->body,
					'========= Please enter your reply ABOVE this line ========='
				);
			}
		}

		$cut = new \Application\DeskPRO\EmailGateway\Cutter\Def\Generic();
		$this->body = $cut->cutQuoteBlock($this->body, $this->body_is_html);

		$this->body = trim($this->body, " >\n\r");

		$this->body = nl2br(htmlspecialchars($this->body, \ENT_QUOTES, 'UTF-8'));
		$this->body_full = nl2br(htmlspecialchars($this->body_full, \ENT_QUOTES, 'UTF-8'));

		$this->body = $this->replaceInlineAttachTokens($this->body, $inline_images);
	}
}