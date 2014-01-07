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
 * @subpackage ApiBundle
 */

namespace Application\ApiBundle\Controller;

use Application\DeskPRO\People\AgentPermissions\GroupsDbLoader;
use Application\DeskPRO\Usergroups\UsergroupEdit;
use Application\DeskPRO\Usergroups\Usergroups;
use Application\DeskPRO\Usergroups\Form\Type\UsergroupType;
use Application\DeskPRO\Exception\ValidationException;

use Orb\Util\Arrays;

class UsergroupsController extends AbstractController
{
	public function listAction($type)
	{
		$data = array();

		if ($type == 'agent') {

			$ugs = $this->em->createQuery("
				SELECT ug
				FROM DeskPRO:Usergroup ug
				WHERE ug.is_agent_group = true
				ORDER BY ug.title ASC
			")->execute();

			$data['agentgroups'] = $this->getApiData($ugs);

		} elseif ($type == 'non_sys_user') {

			$data['usergroups'] = $this->em->getRepository('DeskPRO:Usergroup')->getUsergroupNames();

		} else {

			$ugs = $this->em->createQuery("
				SELECT ug
				FROM DeskPRO:Usergroup ug
				WHERE ug.is_agent_group = false
				ORDER BY ug.title ASC
			")->execute();

			$data['usergroups'] = $this->getApiData($ugs);
		}

		return $this->createApiResponse($data);
	}

	####################################################################################################################
	# list
	####################################################################################################################

	public function listAllAction()
	{
		/**
		 * @var \Application\DeskPRO\Usergroups\Usergroups $usergroups
		 */

		$usergroups = $this->container->getSystemService('usergroups');

		return $this->createApiResponse(
			array(
				 'user_groups' => $this->getApiData(Arrays::flatten($usergroups->getAll())),
			)
		);
	}

	###################################################################################################################
	# get
	####################################################################################################################

	public function getAction($id)
	{
		/**
		 * @var \Application\DeskPRO\Usergroups\Usergroups $usergroups
		 */

		$usergroups = $this->container->getSystemService('user_groups');
		$usergroup  = $usergroups->getById($id);

		$returnedData                = $this->getApiData($usergroup);
		$returnedData['permissions'] = $usergroups->getPermissionsById($id);

		if (!$usergroup) {

			throw $this->createNotFoundException();
		}

		return $this->createApiResponse(
			array(
				 'user_group' => $returnedData,
			)
		);
	}

	###################################################################################################################
	# get-agentgroup-perms
	####################################################################################################################

	public function getAgentgroupPermsAction()
	{
		$ugs = $this->em->createQuery("
			SELECT ug
			FROM DeskPRO:Usergroup ug
			WHERE ug.is_agent_group = true
			ORDER BY ug.title ASC
		")->execute();

		$loader = new GroupsDbLoader($ugs, $this->em);

		$group_data = array();
		foreach ($ugs as $ug) {
			$group_data[] = array(
				'group' => array('id' => $ug->id, 'title' => $ug->title),
				'perms' => $loader->getGroupPermissions($ug->id)->toArray(),
			);
		}
		return $this->createApiResponse(array('groups' => $group_data));
	}

	####################################################################################################################
	# toggleUsergroup
	####################################################################################################################

	public function toggleUsergroupAction($user_group_id, $is_enabled)
	{
		/**
		 * @var \Application\DeskPRO\Usergroups\Usergroups $usergroups
		 */

		$usergroups = $this->container->getSystemService('usergroups');
		$usergroups->setFieldEnabledById($user_group_id, $is_enabled);

		return $this->createSuccessResponse();
	}

	####################################################################################################################
	# save
	####################################################################################################################

	public function saveAction($id)
	{
		/**
		 * @var \Application\DeskPRO\Usergroups\Usergroups $usergroups
		 */

		$usergroups = $this->container->getSystemService('usergroups');

		if ($id) {

			$usergroup = $usergroups->getById($id);

			if (!$usergroup) {

				throw $this->createNotFoundException();
			}
		} else {

			$usergroup = $usergroups->createNew();
		}

		$postData = $this->in->getAll('post');

		$usergroup_edit = new UsergroupEdit($usergroup);

		$form = $this->createForm(new UsergroupType(), $usergroup_edit, array('cascade_validation' => true));
		$form->submit($this->deleteExtraDataFromRequest($form, $postData, 'user_group'), true);

		if ($form->isValid()) {

			$usergroup_edit->save($this->em);

		} else {

			throw ValidationException::create($this->getFormValidationErrorsString($form));
		}

		return $this->createApiResponse(
			array(
				 'success' => true,
				 'id'      => $usergroup->id,
			)
		);
	}
}