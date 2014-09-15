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
 * @subpackage
 */

namespace Application\InstallBundle\Upgrade\Build;

use Application\DeskPRO\Entity\Usersource;
use Application\DeskPRO\ORM\EntityManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Build1409758642 extends AbstractBuild
{
	public function run()
	{
		$this->out("Upgrade usersources to new auth settings");

		$userType = Usersource::TYPE_USER;

		$this->execMutateSql("ALTER TABLE usersources  ADD type VARCHAR(25) NOT NULL");
		$this->execMutateSql("UPDATE usersources SET type = '$userType'");

		$em = $this->container->getEm();

		$this->setupDeskProUsersource($userType, $em);
	}


	private function setupDeskProUsersource($type, EntityManager $em)
	{
		$enabled = $this->container->getSetting('core.deskpro_source_enabled') ? 1 : 0;
		$forgot_password_url = $this->container->getRouter()->generate(
			'user_login_resetpass', array(), UrlGeneratorInterface::ABSOLUTE_URL
		);

		$deskProUsers = new Usersource();
		$deskProUsers->type = $type;
		$deskProUsers->source_type = 'Application\\DeskPRO\\Usersource\\Adapter\\DeskPRO';
		$deskProUsers->is_enabled = $enabled;
		$deskProUsers->display_order = 500; // just a really high number to make sure its the highest priority by default on form logins (initially!)
		$deskProUsers->lost_password_url = $forgot_password_url;
		$deskProUsers->title = 'DeskPRO';
		$deskProUsers->options = array();

		$em->persist($deskProUsers);
		$em->flush($deskProUsers);

		return $deskProUsers;
	}
}
