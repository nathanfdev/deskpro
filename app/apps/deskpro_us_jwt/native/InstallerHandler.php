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
 * @category Entities
 */

namespace deskpro_us_jwt;

use Application\DeskPRO\App\Native\InstallerHandler\AbstractUsersourceInstallerHandler;
use Application\DeskPRO\Entity\AppInstance;
use Application\DeskPRO\Entity\Usersource;
use Application\DeskPRO\ORM\EntityManager;

class InstallerHandler extends AbstractUsersourceInstallerHandler
{
	/**
	 * {@inheritDoc}
	 */
	public function disableSsoSettings(AppInstance $app, EntityManager $em)
	{
		$settings               = $app->getSettings();
		$settings['enable_sso'] = false;
		$settings['sso_type']   = null;
		$app->setSettings($settings);

		$em->flush($app);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function applyAppToUsersource(AppInstance $app, Usersource $us, EntityManager $em)
	{
		$us->title = $app->title;
		$us->options = array(
			'url'               => $app->getSetting('url'),
			'secret'            => $app->getSetting('secret'),
			'login_custom_text' => $app->getSetting('login_custom_text'),
			'logout_agent_url'  => $app->getSetting('logout_agent_url'),
			'logout_user_url'   => $app->getSetting('logout_user_url'),
		);
		$us->is_enabled = $app->getSetting('enable_usersource') ? 1 : 0;
		$us->lost_password_url = $app->getSetting('url') ?: '';
		$us->source_type = 'deskpro_us_jwt\\Usersource\\Adapter\\Jwt';

		if ('auto' == $app->getSetting('sso_type')) {
			$us->makeSsoAutoOnly();
		} elseif ('background' == $app->getSetting('sso_type')) {
			$us->makeSsoBackgroundOnly();
		} else {
			$us->disableSso();
		}

		$em->persist($us);
		$em->persist($app);
		$em->flush();
	}
}
