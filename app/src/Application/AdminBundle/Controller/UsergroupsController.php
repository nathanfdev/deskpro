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
 * @subpackage AdminBundle
 */

namespace Application\AdminBundle\Controller;

use Application\DeskPRO\Entity;
use Application\DeskPRO\App;
use Orb\Util\Strings;
use Orb\Util\Arrays;
use Orb\Util\Util;

use Symfony\Component\Form;

class UsergroupsController extends AbstractController
{
	############################################################################
	# list
	############################################################################

	public function listAction()
	{
		$usergroups = $this->em->createQuery("
			SELECT ug
			FROM DeskPRO:Usergroup ug
			WHERE ug.is_agent_group = false AND ug.sys_name IS NULL
			ORDER BY ug.title ASC
		")->execute();

		$member_counts = $this->db->fetchAllKeyValue("
			SELECT usergroup_id, COUNT(*)
			FROM person2usergroups
			GROUP BY usergroup_id
		");

		$member_counts[0] = $this->db->fetchColumn("
			SELECT COUNT(*) FROM people
		");

		return $this->render('AdminBundle:Usergroups:list.html.twig', array(
			'usergroups' => $usergroups,
			'member_counts' => $member_counts
		));
	}


	############################################################################
	# edit
	############################################################################

	public function editAction($id)
	{
		if (!$id) {
			$usergroup = new Entity\Usergroup();
			$is_new = true;
		} else {
			$usergroup = $this->em->getRepository('DeskPRO:Usergroup')->find($id);
			$is_new = false;
		}

		if (!$usergroup) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		#------------------------------
		# Saving form
		#------------------------------

		if ($this->in->getBool('process')) {
			$this->ensureRequestToken('edit_usergroup');

			$this->em->getConnection()->beginTransaction();

			try {
				$usergroup['title'] = $this->in->getString('usergroup.title');
				$usergroup['note'] = $this->in->getString('usergroup.note');

				$this->em->persist($usergroup);
				$this->em->flush();

				#---
				# Department selections
				#---

				if (!$is_new) {
					$this->db->delete('department_permissions', array('usergroup_id' => $usergroup->id));
				}

				$department_selections = $this->in->getCleanValueArray('department_permissions', 'raw', 'uint');
				foreach ($department_selections as $dep_id => $app_choices) {
					foreach ($app_choices as $app => $x) {
						if ($x) {
							$this->db->insert('department_permissions', array(
								'department_id' => $dep_id,
								'usergroup_id' => $usergroup->id,
								'app' => $app
							));
						}
					}
				}

				#---
				# Permission selections
				#---

				if (!$is_new) {
					$this->db->delete('permissions', array('usergroup_id' => $usergroup->id));
				}

				$permission_selections = $this->container->getIn()->getCleanValueArray('permissions', 'ibool', 'string');
				foreach ($permission_selections as $perm => $x) {
					if ($x) {
						$this->db->insert('permissions', array(
							'usergroup_id' => $usergroup->id,
							'name' => $perm,
							'value' => 1
						));
					}
				}

				$this->em->getConnection()->commit();
				return $this->redirectRoute('admin_usergroups');
			} catch (\Exception $e) {
				$this->em->getConnection()->rollback();
				throw $e;
			}
		}

		#------------------------------
		# Display form
		#------------------------------

		$form = $this->get('form.factory')->createNamedBuilder('form', 'usergroup');
		$form->add('title', 'text', array('data' => $usergroup['title'], 'required' => false));
		$form->add('note', 'textarea', array('data' => $usergroup['note'], 'required' => false));

		$member_count = 0;
		if ($id) {
			$member_count = $this->db->fetchColumn("
				SELECT COUNT(*)
				FROM person2usergroups
				WHERE usergroup_id = ?
			", array($id));
		}

		$departments = $this->container->getDataService('Department')->getAll();

		$ug_deps = $this->db->fetchAllGrouped("
			SELECT department_id, app
			FROM department_permissions
			WHERE usergroup_id = ?
		", array($usergroup->id), 'department_id', 'app', 'app');

		$permissions = $this->db->fetchAllKeyValue("
			SELECT name, value
			FROM permissions
			WHERE permissions.usergroup_id = ?
		", array($usergroup->id));

		return $this->render('AdminBundle:Usergroups:edit.html.twig', array(
			'usergroup' => $usergroup,
			'form'      => $form->getForm()->createView(),
			'member_count' => $member_count,
			'departments' => $departments,
			'ug_deps' => $ug_deps,
			'permissions' => $permissions,
		));
	}

	############################################################################
	# delete
	############################################################################

	public function deleteAction($id, $auth)
	{
		if (!$this->session->checkSecurityToken('delete_usergroup', $auth)) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		if (!$id) {
			$usergroup = new Entity\Usergroup();
		} else {
			$usergroup = $this->em->getRepository('DeskPRO:Usergroup')->find($id);
		}

		if (!$usergroup || $usergroup->sys_name) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		$this->em->transactional(function ($em) use ($usergroup) {
			$em->remove($usergroup);
			$em->flush();
		});

		return $this->redirectRoute('admin_usergroups');
	}
}
