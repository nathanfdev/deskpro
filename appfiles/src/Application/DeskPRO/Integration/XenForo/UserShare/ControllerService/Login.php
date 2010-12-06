<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage XenForo
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
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