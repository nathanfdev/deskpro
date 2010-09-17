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
			FROM CoreEntity:Usersource us
			ORDER BY us.display_order ASC
		")->getResult();

		if (!$all_usersources->count()) {
			return $this->redirect($this->generateUrl('tech_admin_usersources_info', array()));
		}

		$this->tplvars['all_usersources'] = $all_usersources;

		return $this->render('TechBundle:Usersources:index');
	}



	############################################################################
	# /tech/usersources/intro                       tech_admin_usersources_intro
	############################################################################

	/**
	 * Just shows a simple intro page
	 */
	public function introAction()
	{
		return $this->render('TechBundle:Usersources:intro');
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
			$usersource = $this->em->createEntity('CoreBundle:Usersource');

			if (!$this->in->getString('typename')) {
				return $this->render('TechBundle:Usersources:edit-choosetype');
			}

			$usersource['typename'] = $this->in->getString('typename');
		}

		if (!in_array($usersource['typename'], $this->getUsersourceTypes())) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("The typename chosen is invalid");
		}

		$classname = $usersource['typename'];
		$classname = ucfirst($classname);
		$classname = 'DeskPRO\\Usersource\\Setup\\' . $classname;

		$setup = new $classname($this);
		if ($usersource['id']) {
			$setup->setExistingUsersource($usersource);
		}

		if ($this->isPostRequest()) {
			if ($setup->setFormData($this->in->getArray('form_data'))) {
				$usersource['adapter_class']   = $setup->getAdapterClass();
				$usersource['adapter_options'] = $setup->getAdapterOptions();
				$setup->setupRemoteResource($usersource);

				$this->em->persist($usersource);
				$this->em->flush();

				return $this->redirect($this->generateUrl('tech_admin_usersources_info', array('usersource_id' => $usersource['id'])));
			}
		}

		return $this->render('TechBundle:Usersources:edit');
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

		// TODO

		return $this->render('TechBundle:Usersources:info');
	}



	############################################################################

	/**
	 * @return Application\CoreBundle\Entity\Usersource
	 */
	protected function getUsersourceOr404($usersource_id)
	{
		try {
			$usersource = $this->em->find('CoreBundle:Usersource', $usersource_id);
		} catch (\Doctrine\ORM\NoResultException $e) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no usersource with ID $usersource_id");
		}

		return $usersource;
	}

	protected function getUsersourceTypes()
	{
		return array(
			'twitter',
		);
	}
}