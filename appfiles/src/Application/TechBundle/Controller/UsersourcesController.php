<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage TechBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\TechBundle\Controller;

/**
 * Handles creating/editing of Usersources
 */
class UsersourcesController extends AbstractController
{
	############################################################################
	# /tech/usersources                                   tech_admin_usersources
	############################################################################

	/**
	 * Shows existing usersources
	 */
	public function indexAction()
	{
		$all_usersources = $this->em->createQuery("
			SELECT us
			FROM DeskPRO:Usersource us
			ORDER BY us.display_order ASC
		")->getResult();

		if (!count($all_usersources)) {
			return $this->redirect($this->generateUrl('tech_admin_usersources_intro', array()));
		}

		$this->tplvars['all_usersources'] = $all_usersources;

		return $this->render('TechBundle:Usersources:index.twig');
	}



	############################################################################
	# /tech/usersources/intro                       tech_admin_usersources_intro
	############################################################################

	/**
	 * Just shows a simple intro page
	 */
	public function introAction()
	{
		$count = $this->db->fetchColumn("SELECT COUNT(*) FROM usersources LIMIT 1");
		$this->tplvars['has_no_usersources'] = !$count;

		return $this->render('TechBundle:Usersources:intro.twig');
	}



	############################################################################
	# /tech/usersources/:usersource_id/edit          tech_admin_usersources_edit
	############################################################################

	/**
	 * Edit a usersource
	 */
	public function editAction($usersource_id)
	{
		if ($usersource_id) {
			$usersource = $this->getUsersourceOr404($usersource_id);
		} else {
			$usersource = new \Application\DeskPRO\Entity\Usersource();

			if (!$this->in->getString('usersource.handler_class')) {
				return $this->render('TechBundle:Usersources:edit-choosetype.twig');
			}

			$usersource['handler_class'] = $this->in->getString('usersource.handler_class');
		}

		$renderer = new \Orb\Form\Renderer\Basic();
		$form = new \Application\TechBundle\Form\EditUsersource(array(
			'name' => 'usersource',
			'renderer' => $renderer,
			'event_dispatcher' => $this->get('event_dispatcher'),
			'usersource' => $usersource
		));

		$admin_handler = \Application\TechBundle\Usersource\AdminHandler\Factory::createUsersource($usersource);
		$form->addField($admin_handler->buildFormGroup());

		if ($this->isPostRequest()) {
			$form->setFormData($_POST);
			if ($form->isValid()) {
				$admin_handler->saveUsersource($form);
				$this->redirectRoute('tech_admin_usersources_info', array('usersource_id' => $usersource['id']));
			} else {
				// TODO proper handling
				print_r($form->getErrors());
			}
		}
		
		return $this->render('TechBundle:Usersources:edit.twig', array(
			'usersource' => $usersource,
			'form' => $form,
			'rendered_type_form' => $admin_handler->renderFormPartial($this, $form)
		));
	}

	

	############################################################################
	# /tech/usersources/:usersource_id/info          tech_admin_usersources_info
	############################################################################

	/**
	 * Shows info about a usersource such as user stats
	 */
	public function infoAction($usersource_id)
	{
		$usersource = $this->getUsersourceOr404($usersource_id);

		return $this->render('TechBundle:Usersources:info.twig', array(
			'usersource' => $usersource,
		));
	}



	############################################################################

	/**
	 * @return Application\DeskPRO\Entity\Usersource
	 */
	protected function getUsersourceOr404($usersource_id)
	{
		try {
			$usersource = $this->em->find('DeskPRO:Usersource', $usersource_id);
		} catch (\Doctrine\ORM\NoResultException $e) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no usersource with ID $usersource_id");
		}

		return $usersource;
	}
}