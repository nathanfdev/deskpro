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
 * @subpackage Usersource
 */

namespace Application\DeskPRO\Usersource;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Usersource;
use Doctrine\ORM\EntityManager;

class UsersourceManager
{
	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $em;

	/**
	 * @var \Application\DeskPRO\Entity\Usersource[]
	 */
	protected $usersources = null;

	/**
	 * @var \Application\DeskPRO\Entity\Usersource[]|\Application\DeskPRO\Usersource\UsersourceCollection
	 */
	protected $agentUsersources;


	/**
	 * @param \Doctrine\ORM\EntityManager $em
	 */
	public function __construct(EntityManager $em)
	{
		$this->em = $em;
	}


	/**
	 * Find a person in a usersource based on an email address.
	 *
	 * @return \Application\DeskPRO\Entity\Person
	 */
	public function findPersonByEmail($email)
	{
		$person = $this->em->getRepository('DeskPRO:Person')->findOneByEmail($email);
		if ($person) {
			return $person;
		}

		foreach ($this->getWithCapability(UsersourceInfo::CAPABILITY_FIND_IDENTITY) as $us) {
			/** @var $adapter \Application\DeskPRO\Usersource\Adapter\AbstractAdapter */
			$adapter = $us->getAdapter();

			try {
				$identity = $adapter->findIdentityByInput($email);
			} catch (\Exception $e) {
				$identity = null;
			}
			if (!$identity) {
				continue;
			}

			$login_processor = new \Application\DeskPRO\Auth\LoginProcessor($us, $identity);
			$person = $login_processor->getPerson();

			return $person;
		}

		return null;
	}


	/**
	 * Get all installed usersources
	 *
	 * @return \Application\DeskPRO\Entity\Usersource[]
	 * @deprecated use getAll() and filter with UsersourceCollection as needed
	 */
	public function getUsersources()
	{
		if ($this->usersources !== null) {
			return $this->usersources;
		}

		$this->usersources = $this->em->getRepository('DeskPRO:Usersource')->getAllUsersources(true);
		return $this->usersources;
	}


	/**
	 * Get all usersources for the agent/admin area
	 *
	 * @param bool $active if true only returns enabled usersources
	 * @return \Application\DeskPRO\Entity\Usersource[]|\Application\DeskPRO\Usersource\UsersourceCollection
	 */
	public function getAll()
	{
		return new UsersourceCollection(
			$this->em->getRepository('DeskPRO:Usersource')->getAll()
		);
	}


	/**
	 * @param string $type
	 * @return \Application\DeskPRO\Entity\Usersource[]
	 * @deprecated use getAll() and filter with UsersourceCollection as needed
	 */
	public function getUsersourcesOfType($type)
	{
		$ret = array();

		$type = strtolower($type);

		foreach ($this->getUsersources() as $us) {
			if (strtolower($us->source_type) == $type) {
				$ret[$us->id] = $us;
			}
		}

		return $ret;
	}


	/**
	 * Get usersources with a certain capability
	 * @param $capability
	 * @return \Application\DeskPRO\Entity\Usersource[]
	 * @deprecated use getAll() and filter with UsersourceCollection as needed
	 */
	public function getWithCapability($capability)
	{
		$ret = array();
		foreach ($this->getUsersources() as $us) {
			if ($us->getAdapter()->isCapable($capability)) {
				$ret[] = $us;
			}
		}

		return $ret;
	}


	/**
	 * @return string
	 * @deprecated this shouldn't be used anymore, try to eliminate it form the codebase and use twig extension instead
	 */
	public function renderView(Usersource $usersource, $type, array $params = array())
	{
		$params['usersource'] = $usersource;

		$name = $usersource->getAdapter()->getTypename();
		$tpl = "DeskPRO:Auth:" . $name . "-" . $type . ".html.twig";

		if (!isset($params['type'])) {
			$params['type'] = 'user';
		}

		$html = App::getTemplating()->render($tpl, $params);
		return $html;
	}
}
