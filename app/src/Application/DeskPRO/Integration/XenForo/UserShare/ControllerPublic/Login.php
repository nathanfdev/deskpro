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

namespace Application\DeskPRO\Integration\XenForo\UserShare\ControllerPublic;

/**
 * This controller takes a users login and then directs them back to DeskPRO.
 */
class Login extends \XenForo_ControllerPublic_Abstract
{
	public function indexAction()
	{
		if (!isset($_GET['orba_token']) OR !isset($_GET['orba_verify']) OR !isset($_GET['redirect_url']) OR !strpos('-', $_GET['orba_token'])) {
			return $this->responseNoPermission();
		}

		list($req_id, $user_token) = explode('-', $_GET['orba_token'], 2);

		/** @var Zend_Db_Adapter_Abstract */
		$db = \XenForo_Application::get('db');

		$login_req = $db->fetchRow("SELECT * FROM xf_deskpro_loginrequest WHERE id = ?", array($req_id));
		if (!$login_req OR !$this->_verifyToken($login_req, $_GET['orba_verify'])) {
			return $this->responseNoPermission();
		}

		XenForo_Application::get('session')->set('deskpro-login-req', $login_req['id']);
		XenForo_Application::get('session')->set('deskpro-login-req-redirect', $_GET['redirect_url']);

		if (\XenForo_Visitor::getUserId()) {
			// They're already logged in
			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				\XenForo_Link::buildPublicLink('deskpro-login/success'),
				'Logging in...'
			);
		} else {
			// They need to log in
			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				\XenForo_Link::buildPublicLink('login'),
				'Getting ready to log in...',
				array('redirect' => \XenForo_Link::buildPublicLink('deskpro-login/success'))
			);
		}
	}


	public function successAction()
	{
		if (\XenForo_Visitor::getUserId()) {
			return $this->responseNoPermission();
		}

		$req_id = XenForo_Application::get('session')->get('deskpro-login-req');
		$login_req = $db->fetchRow("SELECT * FROM xf_deskpro_loginrequest WHERE id = ?", array($req_id));
		if (!$login_req) {
			return $this->responseNoPermission();
		}

		/** @var Zend_Db_Adapter_Abstract */
		$db = \XenForo_Application::get('db');

		$access_token = \Orb\Util\Strings::random(20);

		$db->update('xf_deskpro_loginrequest', array('access_token' => $access_token), array('id' => $req_id));

		$send_verify = sha1($login_req['id'] . '-' . $access_token . $login_req['user_key']);

		$redirect_url = XenForo_Application::get('session')->get('deskpro-login-req-redirect');
		if (\strpos($redirect_url, '?') === false) {
			$redirect_url .= '?';
		} else {
			$redirect_url .= '&';
		}
		$redirect_url .= 'orba_access_token=' . $access_token . '&orba_verify=' . $send_verify;

		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			$redirect_url,
			null
		);
	}


	protected function _verifyToken(array $login_req, $verify)
	{
		$check_verify = sha1($login_req['id'] . '-' . $login_req['user_token'] . $login_req['orba_user_key']);
		if ($check_verify != $verify) {
			return false;
		}

		return true;
	}
}
