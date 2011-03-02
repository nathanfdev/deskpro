<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\EmailGateway\Ticket;

use \Application\DeskPRO\App;
use \Application\DeskPRO\EmailGateway\Reader\AbstractReader;

/**
 * Detects a ticket based off of the code in the TO address that
 * the email was sent to.
 *
 * This is used with a catch-all email address. When a user replies to a notification,
 * we automatically detect the ticket based on the address: ticket-ABIEUJF@example.com
 *
 * @see \Application\DeskPRO\Entity\TicketAccessCode
 */
class ToEmailTicketDetector implements TicketDetectorInterface
{
	/**
	 * The regex to match
	 * @var string
	 */
	protected $account_pattern;

	/**
	 * @var \Application\DeskPRO\Entity\TicketAccessCode
	 */
	protected $_found_tac = null;

	/**
	 * $account_pattern needs to be an email address with the special token TICKET_CODE
	 * in it to denote the position of the ticket code.
	 *
	 * For example:
	 * <code>
	 * $detector = new ToEmailTicketDetector('ticket-TICKET_CODE@example.com');
	 * </code>
	 *
	 * @param string The pattern with the special token TICKET_CODE in it.
	 */
	public function __construct($account_pattern)
	{
		$account_pattern = preg_quote($account_pattern, '#');
		$account_pattern = str_replace('TICKET_CODE', '(?P<code>[A-Z]{5,})', $account_pattern);

		$this->account_pattern = $account_pattern;
	}


	/**
	 * @return \Application\DeskPRO\Entity\Ticket
	 */
	public function findExistingTicket(AbstractReader $reader)
	{
		$search_addr = array();
		foreach ($reader->getToAddresses() as $addr) {
			$search_addr[] = $addr->email;
		}

		// Easier to run regex on all at once
		$search_addr = ' ' . implode(' ', $search_addr) . ' ';

		$access_code = \Orb\Util\Strings::extractRegexMatch($this->account_pattern, $search_addr, 'code');
		if (!$access_code) {
			return null;
		}

		$tac = App::getEntityRepository('DeskPRO:TicketAccessCode')->findByAccessCode($access_code);
		if ($tac) {
			$this->_found_tac = $tac;
			return $tac['ticket'];
		}

		return null;
	}

	/**
	 * @return \Application\DeskPRO\Entity\Person
	 */
	public function findExistingPerson(Ticket $ticket, AbstractReader $reader)
	{
		if ($this->_found_tac) {
			return $this->_found_tac['person'];
		}

		return null;
	}
}