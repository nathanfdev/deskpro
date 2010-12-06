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
