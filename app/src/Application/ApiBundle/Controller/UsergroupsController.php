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

use Application\ApiBundle\PermissionStrategy\AdminManagePermission;
use Application\ApiBundle\PermissionStrategy\MultiPermissions;
use Application\ApiBundle\PermissionStrategy\PassPermission;
use Application\DeskPRO\Entity\Usergroup;
use Application\DeskPRO\Exception\ValidationException;
use Application\DeskPRO\People\UserPermissions\GroupDbPersister;
use Application\DeskPRO\People\UserPermissions\GroupsDbLoader;
use Application\DeskPRO\People\UserPermissions\UserPermissions;
use Application\DeskPRO\Usergroups\Form\Type\UsergroupType;
use Application\DeskPRO\Usergroups\UsergroupEdit;

class UsergroupsController extends AbstractController implements ProtectedControllerInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function getPermissionStrategy()
	{
		$multi = new MultiPermissions();
		$multi->addPermissionStrategy(new AdminManagePermission());
		$multi->addPermissionStrategy(new PassPermission(), 'listAction');
		return $multi;
	}


	####################################################################################################################
	# list
	####################################################################################################################

	public function listAction($type)
	{
		$data = array();

		if ($type == 'non_sys_user') {
			$data['groups'] = $this->em->getRepository('DeskPRO:Usergroup')->getUsergroupNames();
		} else {
			$ugs = $this->em->createQuery("
				SELECT ug
				FROM DeskPRO:Usergroup ug
				WHERE ug.is_agent_group = false
				ORDER BY ug.title ASC
			")->execute();

			$data['groups'] = $this->getApiData($ugs);
		}

		return $this->createApiResponse($data);
	}

	###################################################################################################################
	# get
	####################################################################################################################

	public function getAction($id)
	{
		$usergroups = $this->container->getUserGroups();
		$usergroup  = $usergroups->getGroup($id);

		if (!$usergroup || $usergroup->is_agent_group) {
			throw $this->createNotFoundException();
		}

		$perms = new GroupsDbLoader(array($usergroup), $this->em);

		$data = $usergroup->toApiData();
		$data['perms'] = $perms->getGroupPermissions($usergroup->id);

		return $this->createApiResponse(array('group' => $data));
	}


	###################################################################################################################
	# delete
	####################################################################################################################

	public function deleteAction($id)
	{
		$usergroups = $this->container->getUserGroups();
		$usergroup  = $usergroups->getGroup($id);

		if (!$usergroup || $usergroup->is_agent_group) {
			throw $this->createNotFoundException();
		}

		if ($usergroup->sys_name) {
			return $this->createApiErrorResponse('no_delete_sys', 'You cannot delete built-in user groups');
		}

		$this->em->remove($usergroup);
		$this->em->flush();

		$this->db->executeUpdate("DELETE FROM permissions_cache");

		return $this->createApiDeleteResponse(array('old_group_id' => (int)$id));
	}


	####################################################################################################################
	# save
	####################################################################################################################

	public function saveAction($id)
	{
		$usergroups = $this->container->getUserGroups();

		#------------------------------
		# Get group
		#------------------------------

		if ($id) {
			$usergroup = $usergroups->getGroup($id);
			if (!$usergroup) {
				throw $this->createNotFoundException();
			}
		} else {
			$usergroup = new Usergroup();
		}

		#------------------------------
		# Save form
		#------------------------------

		$usergroup_edit = new UsergroupEdit($usergroup);

		$formData = array('group' => $this->in->getArrayValue('group'));
		unset($formData['group']['perms']);

		$form = $this->createForm(new UsergroupType(), $usergroup_edit, array('cascade_validation' => true));
		$form->submit($formData, true);

		if (!$form->isValid()) {
			throw ValidationException::create($this->getFormValidationErrorsString($form));
		}

		$usergroup_edit->save($this->em);

		#------------------------------
		# Save permissions
		#------------------------------

		// Save perms
		$perms = new UserPermissions();
		$perms->fromArray($this->in->getArrayValue('group.perms'));

		$db_persister = new GroupDbPersister($this->em);
		$db_persister->savePerms($usergroup, $perms);

		#------------------------------
		# Clear permission cache
		#------------------------------

		$this->db->executeUpdate("DELETE FROM permissions_cache");

		#------------------------------
		# Result
		#------------------------------

		if (!$id) {
			return $this->createApiCreateResponse(
				array('id' => $usergroup->id),
				$this->generateUrl('api_user_groups_get', array('id' => $usergroup->id), true)
			);
		} else {
			return $this->createApiSuccessResponse();
		}
	}
}