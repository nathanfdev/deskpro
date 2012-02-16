<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AdminBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\AdminBundle\Controller;

use Application\DeskPRO\App;

/**
 * Handles creating/editing of Usersources
 */
class TestController extends AbstractController
{
	public function indexAction()
	{
		$AGENTGROUP_ALL = $this->em->find('DeskPRO:Usergroup', 6);

		$scanner = new \Application\InstallBundle\Data\AgentGroupPermScanner();
		foreach ($scanner->getNames() as $p_name) {
			$p = new \Application\DeskPRO\Entity\Permission();
			$p->usergroup = $AGENTGROUP_ALL;
			$p->name = $p_name;
			$p->value = 1;
			$this->em->persist($p);
		}
		$this->em->flush();

		return $this->createResponse('');
	}
}
