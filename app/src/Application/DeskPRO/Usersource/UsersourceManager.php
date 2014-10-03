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
 * @subpackage Usersource
 */

namespace Application\DeskPRO\Usersource;

use Application\DeskPRO\App;
use Application\DeskPRO\Auth\LoginProcessor;
use Application\DeskPRO\Entity\Usersource;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Usersource\Adapter\IdentityFinderInterface;
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
	 * @var \Application\DeskPRO\App\AppManipulator
	 */
	private $app_manipulator;


	/**
	 * @param EntityManager      $em
	 * @param App\AppManipulator $app_manipulator
	 */
	public function __construct(EntityManager $em, App\AppManipulator $app_manipulator)
	{
		$this->em = $em;
		$this->app_manipulator = $app_manipulator;
	}

	public function ensureSsoSettings(Usersource $usersource)
	{
		if ($usersource->is_sso_auto || $usersource->is_sso_background) {
			if ($usersource->type === Usersource::TYPE_USER) {
				$sources = $this->getAll()->configuredForUsers(true);
			} else {
				$sources = $this->getAll()->configuredForAgents(true);
			}

			/** @var \Application\DeskPRO\Entity\Usersource $source */
			foreach ($sources as $source) {
				if ($source->id != $usersource->id) {
					$source->disableSso();
					if ($source->app) {
						$this->app_manipulator->disableSso($source->app);
					}
				}
			}

			$this->em->flush();
		}
	}


	/**
	 * Find a person in a USER usersource based on an email address.
	 *
	 * @param string $input this can actually be any input (but is usually email)
	 * @return \Application\DeskPRO\Entity\Person
	 */
	public function findPersonByEmail($input)
	{
		$identityUsersources = $this->getAll()->configuredForUsers()->withCapability(UsersourceInfo::CAPABILITY_FIND_IDENTITY);

		/** @var \Application\DeskPRO\Entity\Usersource $usersource */
		foreach ($identityUsersources as $usersource) {
			$adapter = $usersource->getAdapter();

			if ($adapter instanceof IdentityFinderInterface) {
				try {
					if ($identity = $adapter->findIdentityByInput($input)) {

						// if the usersource can return a person directly, return that now
						if ($identity instanceof Person) {
							return $identity;
						}

						// otherwise it must be an identity, lets get the person:
						$login_processor = new LoginProcessor($usersource, $identity);

						return $login_processor->getPerson();
					}
				} catch (\Exception $e) {
				}
			}
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


	public function getById($sso_usersource_id)
	{
		return $this->usersources = $this->em->getRepository('DeskPRO:Usersource')->find($sso_usersource_id);
	}
}
