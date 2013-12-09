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

namespace Application\DeskPRO\TicketAccounts;

use Application\DeskPRO\Entity\Ticket;
use Doctrine\ORM\EntityManager;
use Application\DeskPRO\Entity\EmailGateway;
use Orb\Util\Arrays;

class TicketAccounts
{
	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	/**
	 * @var \Application\DeskPRO\Entity\EmailGateway[]
	 */
	private $accounts;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
	}

	private function preload()
	{
		if ($this->accounts !== null) {
			return;
		}

		$accounts = $this->em->getRepository('DeskPRO:EmailGateway')->getTicketAccounts();
		$this->accounts = array();

		foreach ($accounts as $acc) {
			$this->accounts[$acc->id] = $acc;
		}
	}


	/**
	 * Get the default account to use. This is just the first defined account.
	 *
	 * @return \Application\DeskPRO\Entity\EmailGateway|null
	 */
	public function getDefaultAccount()
	{
		$this->preload();

		$accounts = $this->getEnabledAccounts();
		if (!$accounts) {
			$this->getAllAccounts();
		}

		if ($accounts) {
			return Arrays::getFirstItem($accounts);
		}

		return null;
	}


	/**
	 * @return \Application\DeskPRO\Entity\EmailGateway[]
	 */
	public function getAllAccounts()
	{
		$this->preload();
		return array_values($this->accounts);
	}


	/**
	 * @return \Application\DeskPRO\Entity\EmailGateway[]
	 */
	public function getEnabledAccounts()
	{
		$this->preload();

		$ret = array();
		foreach ($this->accounts as $acc) {
			if ($acc->is_enabled) {
				$ret[] = $acc;
			}
		}
		return $ret;
	}


	/**
	 * Get an email gateway by ID
	 *
	 * @param $id
	 * @return \Application\DeskPRO\Entity\EmailGateway
	 */
	public function getById($id)
	{
		$this->preload();

		return isset($this->accounts[$id]) ? $this->accounts[$id] : null;
	}


	/**
	 * Get an email gateway by ID, but only if its enabled
	 *
	 * @param int $id
	 * @return \Application\DeskPRO\Entity\EmailGateway
	 */
	public function getEnabledById($id)
	{
		$acc = $this->getById($id);
		if (!$acc || !$acc->is_enabled) {
			return null;
		}

		return $acc;
	}


	/**
	 * @return int
	 */
	public function count()
	{
		$this->preload();
		return count($this->accounts);
	}


	/**
	 * @return int
	 */
	public function countEnabled()
	{
		$this->preload();
		return count($this->getEnabledAccounts());
	}


	/**
	 * @param Ticket $ticket
	 */
	public function getAccountForTicket(Ticket $ticket)
	{
		if ($ticket->email_gateway && $ticket->email_gateway->is_enabled) {
			return $ticket->email_gateway;
		}

		return $this->getDefaultAccount();
	}


	/**
	 * @param Ticket $ticket
	 */
	public function getEmailAddressForTicket(Ticket $ticket)
	{
		$account = $this->getAccountForTicket($ticket);

		if (!$account) {
			return null;
		}

		return $account->getPrimaryEmailAddress();
	}
}