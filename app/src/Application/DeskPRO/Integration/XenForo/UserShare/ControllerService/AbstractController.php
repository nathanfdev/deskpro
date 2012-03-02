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
