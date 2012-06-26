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
 */

namespace Application\DeskPRO\EmailGateway\TicketGateway;

use Application\DeskPRO\EmailGateway\Reader\AbstractReader as AbstractEmailReader;
use Application\DeskPRO\Entity\TicketMessage;
use Doctrine\ORM\EntityManager;

class DetectInlineReply
{
	/**
	 * @var \Application\DeskPRO\ORM\EntityManager
	 */
	protected $em;

	/**
	 * @var \Application\DeskPRO\EmailGateway\Reader\AbstractReader
	 */
	protected $reader;

	/**
	 * @var array
	 */
	protected $message_texts = null;

	/**
	 * @var float
	 */
	protected $threshold = 0.18;

	public function __construct(EntityManager $em, AbstractEmailReader $reader)
	{
		$this->em     = $em;
		$this->reader = $reader;
	}


	/**
	 * How much longer/shorter does a messag eneed to be before we think its an inline reply
	 *
	 * E.g., 0.1 for 10%
	 *
	 * @param float $threshold
	 */
	public function setThreshold($threshold)
	{
		$this->threshold = $threshold;
	}


	/**
	 * Check to see if we've detected an inline reply
	 *
	 * @return bool
	 */
	public function hasDifferentMessage()
	{
		return $this->getDifferentMessage() !== null;
	}


	/**
	 * Gets the first message that we detect has changed
	 *
	 * @param bool $all
	 * @return \Application\DeskPRO\Entity\TicketMessage|null
	 */
	public function getDifferentMessage()
	{
		$message_texts = $this->getMessageTexts();
		if (!$message_texts) {
			return false;
		}

		$ticket_messages = $this->em->getRepository('DeskPRO:TicketMessage')->getByIds(array_keys($message_texts));

		foreach ($message_texts as $message_id => $message_text) {
			if (!isset($ticket_messages[$message_id])) {
				continue;
			}

			$ticket_message = $ticket_messages[$message_id];

			$real_message_text = $this->normalizeMessage($ticket_message->message);

			$diff = $this->getMessageDifference($message_text, $real_message_text);
			if ($diff >= $this->threshold) {
				return $ticket_message;
			}
		}

		return null;
	}


	/**
	 * Get difference as a float between two messages
	 *
	 * @param string $message1
	 * @param string $message2
	 * @return float
	 */
	public function getMessageDifference($message1, $message2)
	{
		$len1 = strlen($message1);
		$len2 = strlen($message2);

		if ($len1 > $len2) {
			return 1.0 - ($len2 / $len1);
		} else {
			return 1.0 - ($len1 / $len2);
		}
	}


	/**
	 * Read body and extract message texts from the source
	 *
	 * @return array
	 */
	public function getMessageTexts()
	{
		if ($this->message_texts !== null) {
			return $this->message_texts;
		}

		$this->message_texts = array();

		$body = $this->reader->getBodyHtml()->getBodyUtf8();
		if (!$body) {
			return $this->message_texts;
		}

		$matches = 0;
		if (!preg_match_all('#dp_message_([0-9]+)_begin(.*?)dp_message_\\1_end#s', $body, $matches, \PREG_SET_ORDER)) {
			return $this->message_texts;
		}

		foreach ($matches as $match) {
			$message_id = $match[1];
			$message    = $match[2];

			// Clean off the spans that contain the message wraps
			if ($pos = strpos($message, '</span>')) {
				$message = substr($message, $pos + 7);
			}
			if ($pos = strrpos($message, '<span')) {
				$message = substr($message, 0, $pos);
			}

			$message = $this->normalizeMessage($message);

			$this->message_texts[$message_id] = $message;
		}

		return $this->message_texts;
	}


	/**
	 * Normalize message text so our detection can be a bit more accurate.
	 *
	 * @param string $message_text
	 * @return string
	 */
	public function normalizeMessage($message_text)
	{
		$message_text = strip_tags($message_text);
		$message_text = preg_replace('#\s#', ' ', $message_text);

		return $message_text;
	}
}