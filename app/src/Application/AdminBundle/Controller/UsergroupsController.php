<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AdminBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
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

		$member_counts = App::getDb()->fetchAllKeyValue("
			SELECT usergroup_id, COUNT(*)
			FROM person2usergroups
			GROUP BY usergroup_id
		");

		$member_counts[0] = App::getDb()->fetchColumn("
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
			$usergroup = App::getEntityRepository('DeskPRO:Usergroup')->find($id);
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
		$form->add('title', 'text', array('data' => $usergroup['title']));
		$form->add('note', 'textarea', array('data' => $usergroup['note']));

		$member_count = 0;
		if ($id) {
			$member_count = App::getDb()->fetchColumn("
				SELECT COUNT(*)
				FROM person2usergroups
				WHERE usergroup_id = ?
			", array($id));
		}

		$departments = $this->em->getRepository('DeskPRO:Department')->getAll();

		$ug_deps = $this->db->fetchAllGrouped("
			SELECT department_id, app
			FROM department_permissions
			WHERE usergroup_id = ?
		", array($usergroup->id), 'department_id', 'app', 'app');

		$permissions = App::getDb()->fetchAllKeyValue("
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
			return new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		if (!$id) {
			$usergroup = new Entity\Usergroup();
		} else {
			$usergroup = App::getEntityRepository('DeskPRO:Usergroup')->find($id);
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


	############################################################################
	# members
	############################################################################

	public function browseAction($id, $page = 1)
	{
		$usergroup = null;
		if ($id) {
			$usergroup = App::getEntityRepository('DeskPRO:Usergroup')->find($id);
			if (!$usergroup || $usergroup->sys_name) {
				throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
			}
		}

		if ($id && $id != 1) {
			$member_count = App::getDb()->fetchColumn("
				SELECT COUNT(*)
				FROM person2usergroups
				WHERE usergroup_id = ?
			", array($id));
		} else {
			$member_count = App::getDb()->fetchColumn("
				SELECT COUNT(*)
				FROM people
			", array($id));
		}

		$per_page = 100;
		$pageinfo = \Orb\Util\Numbers::getPaginationPages($member_count, $page, $per_page);
		$page = $pageinfo['curpage'];
		$offset = ($page - 1) * $per_page;

		$q = $this->em->createQueryBuilder();
		$q->from('DeskPRO:Person', 'p')
		  ->select('p')
		  ->leftJoin('p.usergroups', 'u')
		  ->orderBy('p.id', 'DESC')
		  ->setFirstResult($offset)
		  ->setMaxResults($per_page);

		if ($id) {
			$q->where('u.id = :usergroup_id')->setParameter('usergroup_id', $id);
		}

		$people = $q->getQuery()->execute();

		return $this->render('AdminBundle:Usergroups:browse.html.twig', array(
			'usergroup '=> $usergroup,
			'people' => $people,
			'pageinfo' => $pageinfo,
			'member_count' => $member_count
		));
	}
}
