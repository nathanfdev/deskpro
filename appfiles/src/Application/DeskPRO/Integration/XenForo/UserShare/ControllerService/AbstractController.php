<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage XenForo
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Integration\XenForo\UserShare\ControllerService;

/**
 * Base controller sets basic assertions
 */
class AbstractController extends \XenForo_ControllerPublic_Abstract
{
	public function _preDispatchFirst($action)
	{
		$this->getRouteMatch()->setResponseType('json');

		$options = \XenForo_Application::get('options');
		if (!$options->boardActive) {
			throw new XenForo_Exception($options->boardInactiveMessage, true);
		}

		$key = isset($_GET['orba_consumer_key']) ? $_GET['orba_consumer_key'] : '';
		if ($key != $options->DeskPRO_UserShare_Key) {
			throw new \XenForo_Exception('invalid_key', true);
		}

		// Cleanup entries older than 1 hour, they're expired
		/** @var Zend_Db_Adapter_Abstract */
		$db = \XenForo_Application::get('db');
		$db->delete('xf_deskpro_loginrequest', 'created_at <= '.(time() - 3600));
	}

	public function responseNoPermission()
	{
		return $this->responseView('Application\\DeskPRO\\Integration\\XenForo\\View\\SimpleJson', '', array('error' => true, 'error_code' => 'no_permission'));
	}

	public function renderJsonResponse(array $data)
	{
		return $this->responseView('Application\\DeskPRO\\Integration\\XenForo\\View\\SimpleJson', '', $data);
	}

	public function renderJsonErrorResponse($error_code)
	{
		return $this->renderJsonResponse(array('error' => true, 'error_code' => $error_code));
	}

	public function getResponseType()
	{
		return 'json';
	}

	public function _checkCsrf($action)
	{
		return;
	}
}