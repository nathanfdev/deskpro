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
		$usergroups = App::getOrm()->createQuery("
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
		} else {
			$usergroup = App::getEntityRepository('DeskPRO:Usergroup')->find($id);
		}

		if (!$usergroup || $usergroup->sys_name) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		if ($this->in->getBool('process')) {
			$this->ensureRequestToken('edit_usergroup');
			$usergroup['title'] = $this->in->getString('usergroup.title');
			$usergroup['note'] = $this->in->getString('usergroup.note');

			App::getOrm()->persist($usergroup);
			App::getOrm()->flush();

			return $this->redirectRoute('admin_usergroups');
		}

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

		return $this->render('AdminBundle:Usergroups:edit.html.twig', array(
			'usergroup' => $usergroup,
			'form'      => $form->getForm()->createView(),
			'member_count' => $member_count,
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

		if ($id) {
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

		$q = App::getOrm()->createQueryBuilder();
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
