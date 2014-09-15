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

namespace Application\ApiBundle\Controller;


use Application\DeskPRO\Entity\Usersource;

class UsersourcesController extends AbstractController
{
	public function listByTypeAction($type)
	{
		if ($type == Usersource::TYPE_USER) {
			$sources = $this->getUsersourceManager()->getAll()->configuredForUsers(true);
		} else {
			$sources = $this->getUsersourceManager()->getAll()->configuredForAgents(true);
		}

		$usersources = array_map(
			function(Usersource $us) {
				return array ('usersource' => $us->toApiData(), 'app' => $us->app ? $us->app->toApiData() : null);
			},
			(array) $sources
		);

		return $this->createApiResponse(array('usersources' => $usersources));
	}


	public function getUsersourceAction($type, $id)
	{
		$sources = $this->getUsersourceManager()->getAll()->mustHaveId($id);

		if ($type === Usersource::TYPE_USER) {
			$sources = $sources->configuredForUsers(true);
		} else {
			$sources = $sources->configuredForAgents(true);
		}

		$source = $sources->getFirstOrNull();

		if (!$source) {
			throw $this->createNotFoundException('usersource id=' . $id . ' not found for type=' . $type);
		}

		return $this->createApiResponse(
			array(
				'usersource' => $source->toApiData(),
				'app'        => $source->app ? $source->app->toApiData() : null
			)
		);
	}


	public function updateDisplayOrderAction()
	{
		$inputOrders = $this->in->getCleanValueArray('display_orders', 'uint', 'discard');
		$this->em->getRepository('DeskPRO:Usersource')->updateDisplayOrders($inputOrders);
		return $this->createApiSuccessResponse();
	}


	/**
	 * @return \Application\DeskPRO\Usersource\UsersourceManager
	 */
	protected function getUsersourceManager()
	{
		return $this->container->getSystemService('usersource_manager');
	}
}
 