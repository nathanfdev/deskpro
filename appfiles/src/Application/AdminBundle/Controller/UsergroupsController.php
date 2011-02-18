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

use \Application\DeskPRO\Entity;
use \Application\DeskPRO\App;
use \Orb\Util\Strings;
use \Orb\Util\Arrays;
use \Orb\Util\Util;

use \Symfony\Component\Form;

class UsergroupsController extends AbstractController
{
	public function listAction()
	{
		$usergroups = App::getOrm()->createQuery("
			SELECT ug
			FROM DeskPRO:Usergroup ug
			WHERE ug.is_agent_group = false
			ORDER BY ug.title ASC
		")->execute();

		return $this->render('AdminBundle:Usergroups:list.html.twig', array(
			'usergroups' => $usergroups
		));
	}

	public function editAction($usergroup_id)
	{
		if (!$usergroup_id) {
			$usergroup = new Entity\Usergroup();
		} else {
			$usergroup = App::getEntityRepository('DeskPRO:Usergroup')->find($usergroup_id);
		}

		$is_edited = false;
		$row_html = false;
		if ($this->in->getBool('process')) {
			$is_edited = true;

			$usergroup['title'] = $this->in->getString('usergroup.title');
			$usergroup['note'] = $this->in->getString('usergroup.note');

			App::getOrm()->persist($usergroup);
			App::getOrm()->flush();

			$row_html = $this->renderView('AdminBundle:Usergroups:list-row.html.twig', array('usergroup' => $usergroup));
		}

		$form = new Form\Form('usergroup');
		$form->add(new Form\TextField('title', array('data' => $usergroup['title'])));
		$form->add(new Form\TextareaField('note', array('data' => $usergroup['note'])));

		return $this->render('AdminBundle:Usergroups:edit.html.twig', array(
			'usergroup' => $usergroup,
			'form'      => $form,
			'is_edited' => $is_edited,
			'row_html'  => $row_html
		));
	}
}