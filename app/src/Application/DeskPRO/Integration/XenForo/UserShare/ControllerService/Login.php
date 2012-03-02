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
 * @subpackage XenForo
 */

namespace Application\DeskPRO\Integration\XenForo\UserShare\ControllerService;

/**
 * This is a service controller that handles registering and validating login
 * workflows.
 */
class Login extends AbstractController
{
	public function actionIndex()
	{
		/** @var Zend_Db_Adapter_Abstract */
		$db = \XenForo_Application::get('db');

		$_POST = '123';
		if (!isset($_POST['orba_user_key'])) {
			return $this->renderJsonErrorResponse('no_user_key');
		}

		$data = array(
			'user_key' => $_POST,
			'user_token' => \Orb\Util\Strings::random(20),
			'access_token' => '',
			'created_at' => time(),
		);

		$db->insert('xf_deskpro_loginrequest', $data);
		$data['id'] = $db->lastInsertId();

		$return = array(
			'orba_token' => $data['id'] . '-' . $data['user_token'],
			'orba_service_url' => \XenForo_Link::buildPublicLink('full:deskpro-login')
		);

		return $this->renderJsonResponse($return);
	}

	public function actionVerify()
	{
		
	}
}
