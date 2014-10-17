<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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
use Orb\Log\Logger;

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
	 * @param Ticket              $ticket
	 * @param TicketIncomingEmail $ticket_email
	 * @param Cleaner             $cleaner
	 * @param null                $token_replace_callback
	 * @param Logger              $logger
	 */
	public function __construct(Ticket $ticket = null, TicketIncomingEmail $ticket_email, Cleaner $cleaner, $token_replace_callback = null, Logger $logger = null)
	{
		if ($logger) {
			$this->setLogger($logger);
		}

		$reader = $ticket_email->reader;
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
				$txt = $ticket_email->email_body_text;
				$this->charset_error = $reader->getBodyText()->getOriginalCharset();
			}

			$this->body = $txt;
		} else {
			$this->logMessage('[TicketIncomingEmailMessageV3] read HTML email');
			$this->body = $ticket_email->email_body_html;
			if (!$this->body) {
				$this->body = strip_tags($ticket_email->email_body_html);
				$this->charset_error = $reader->getBodyHtml()->getOriginalCharset();
			}

			// Replace inline image tags with tokens
			$this->body = $inline_images->processTokens($this->body);
		}

		$this->body_raw = $this->body;

		$agent_pos_1 = strpos($this->body, '=== Enter your reply below this line ===');
		$agent_pos_2 = strpos($this->body, '=== Enter your reply above this line ===');

		#------------------------------
		# Agent markers
		#------------------------------

		if ($agent_pos_1 !== false && $agent_pos_2 !== false) {
			$this->logMessage('[TicketIncomingEmailMessageV3] read agent markers');
			$this->body = Strings::getBetweenBoundary(
				$this->body,
				'=== Enter your reply below this line ===',
				'=== Enter your reply above this line ==='
			);

		#------------------------------
		# User email
		#------------------------------

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

		$cut = new \Application\DeskPRO\EmailGateway\Cutter\Def\Generic();
		$this->body = $cut->cutQuoteBlock($this->body, $this->body_is_html);

		$this->body = trim($this->body, " >\n\r");

		$this->body = nl2br(htmlspecialchars($this->body, \ENT_QUOTES, 'UTF-8'));

		if ($token_replace_callback) {
			$this->body = call_user_func($token_replace_callback, $this->body, $inline_images);
		}

		$this->body_full = $this->body;
	}
}