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
 * @category Entities
 */

namespace Application\DeskPRO\Email\EmailAccount\Repository;

use Doctrine\ORM\EntityManager;

class EmailAccountRepository
{
	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	/**
	 * @var \Application\DeskPRO\Entity\EmailAccount[]
	 */
	private $accounts;

	/**
	 * @var \Application\DeskPRO\Entity\EmailAccount[]
	 */
	private $enabled_accounts;

	/**
	 * @var \Application\DeskPRO\Entity\EmailAccount[]
	 */
	private $disabled_accounts;


	/**
	 * @param EntityManager $em
	 */
	public function __construct(EntityManager $em)
	{
		$this->em = $em;
	}


	/**
	 * Loads account info from the db
	 */
	private function preload()
	{
		if ($this->accounts !== null) return;

		$this->enabled_accounts  = array();
		$this->disabled_accounts = array();
		$this->accounts          = array();

		foreach ($this->em->getRepository('DeskPRO:EmailAccount')->findAll() as $acc) {
			$this->accounts[$acc->id] = $acc;

			if ($acc->is_enabled) {
				$this->enabled_accounts[$acc->id] = $acc;
			} else {
				$this->disabled_accounts[$acc->id] = $acc;
			}
		}
	}


	/**
	 * @return \Application\DeskPRO\Entity\EmailAccount[]
	 */
	public function getAccounts()
	{
		$this->preload();
		return $this->accounts;
	}


	/**
	 * @return \Application\DeskPRO\Entity\EmailAccount[]
	 */
	public function getEnabledAccounts()
	{
		$this->preload();
		return $this->enabled_accounts;
	}


	/**
	 * @return \Application\DeskPRO\Entity\EmailAccount[]
	 */
	public function getDisabledAccounts()
	{
		$this->preload();
		return $this->disabled_accounts;
	}


	/**
	 * @return \Application\DeskPRO\Entity\EmailAccount|null
	 */
	public function getAccount($id)
	{
		$this->preload();
		return isset($this->accounts[$id]) ? $this->accounts[$id] : null;
	}
}