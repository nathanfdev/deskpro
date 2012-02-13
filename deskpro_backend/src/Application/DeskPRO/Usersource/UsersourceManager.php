<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Usersource
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Usersource;

use Doctrine\ORM\EntityManager;
use Orb\Util\Arrays;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Usersource;

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
	 * @param \Doctrine\ORM\EntityManager $em
	 */
	public function __construct(EntityManager $em)
	{
		$this->em = $em;
	}


	/**
	 * Get all installed usersources
	 *
	 * @return \Application\DeskPRO\Entity\Usersource[]
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
	 * Get usersources with a certain capability
	 *
	 * @param $capability
	 * @return \Application\DeskPRO\Entity\Usersource[]
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
	 */
	public function renderView(Usersource $usersource, $type, array $params = array())
	{
		$params['usersource'] = $usersource;

		$name = $usersource->getAdapter()->getTypename();
		$tpl = "DeskPRO:Auth:" . $name . "-" . $type . ".html.twig";

		$html = App::getTemplating()->render($tpl, $params);
		return $html;
	}
}
