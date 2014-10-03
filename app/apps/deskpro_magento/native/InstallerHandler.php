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

namespace deskpro_magento;

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
		$app->setSettings($settings);

		$em->flush($app);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function applyAppToUsersource(AppInstance $app, Usersource $us, EntityManager $em)
	{
		$us->title = $app->title;
		$us->options           = array(
			'url'      => $app->getSetting('url'),
			'api_user' => $app->getSetting('api_user'),
			'api_key'  => $app->getSetting('api_key'),
			'sso_js'   => $app->getSetting('enable_sso') ? true : false,
		);
		$us->lost_password_url = $app->getSetting('lost_pwd_url') ? : '';
		$us->is_enabled        = $app->getSetting('enable_usersource') ? 1 : 0;
		$us->source_type       = 'deskpro_magento\\Usersource\\Adapter\\Magento';

		$this->setupAutoAgent($us, $app->getSetting('auto_agent'), $app->getSetting('auto_agent_permission_group'));

		if ($app->getSetting('enable_sso')) {
			$us->makeSsoBackgroundOnly();
		} else {
			$us->disableSso();
		}

		$em->persist($app);
		$em->persist($us);
		$em->flush();
	}
}
