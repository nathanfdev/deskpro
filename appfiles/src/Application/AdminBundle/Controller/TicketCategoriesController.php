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

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use Application\AdminBundle\Form\EditTicketCategoryType;

/**
 * Managing ticket categories
 */
class TicketCategoriesController extends AbstractController
{
	############################################################################
	# list
	############################################################################

	/**
	 * Shows the main listing of departments
	 */
	public function listAction()
	{
		$all_categories = $this->em->createQuery("
			SELECT c
			FROM DeskPRO:TicketCategory c
			WHERE c.parent IS NULL
			ORDER BY c.display_order ASC
		")->getResult();

		return $this->render('AdminBundle:TicketCategories:list.html.twig', array(
			'all_categories' => $all_categories
		));
	}



	############################################################################
	# edit
	############################################################################

	public function saveTitleAction()
	{
		$category_id = $this->in->getUint('category_id');
		$category = App::findEntity('DeskPRO:TicketCategory', $category_id);

		if (!$category) {
			throw $this->createNotFoundException();
		}

		if ($this->in->getString('title')) {
			$category->title = $this->in->getString('title');
		}

		$this->em->getConnection()->beginTransaction();

		try {
			$this->em->persist($category);
			$this->em->flush();

			$this->em->getConnection()->commit();
		} catch (\Exception $e) {
			$this->em->getConnection()->rollback();
			throw $e;
		}

		return $this->redirectRoute('admin_ticketcats');
	}

	public function saveNewAction()
	{
		$category = new \Application\DeskPRO\Entity\TicketCategory();
		$category->title = $this->in->getString('title');

		if (!$category->title) {
			$category->title = 'Untitled';
		}

		if ($this->in->getUint('parent_id')) {
			$parent = App::findEntity('DeskPRO:TicketCategory', $this->in->getUint('parent_id'));
		}

		if ($parent and !$parent->parent) {
			$category->parent = $parent;
		}

		$this->em->getConnection()->beginTransaction();

		try {
			$this->em->persist($category);
			$this->em->flush();

			$this->em->getConnection()->commit();
		} catch (\Exception $e) {
			$this->em->getConnection()->rollback();
			throw $e;
		}

		App::getEntityRepository('DeskPRO:Setting')->updateSetting('core.task_completed_add_ticketcategory', time());

		return $this->redirectRoute('admin_ticketcats');
	}

	############################################################################
	# delete
	############################################################################

	public function deleteAction($category_id)
	{
		$category = App::getEntityRepository('DeskPRO:TicketCategory')->find($category_id);

		return $this->render('AdminBundle:TicketCategories:delete.html.twig', array(
			'category'  => $category,
		));
	}

	public function doDeleteAction($category_id, $security_token)
	{
		$category = App::getEntityRepository('DeskPRO:TicketCategory')->find($category_id);

		if (!$this->session->getEntity()->checkSecurityToken('delete_ticket_category', $security_token)) {
			// TODO err
			die('invalid token');
		}

		$this->em->beginTransaction();
		foreach ($category->children as $c) {
			$this->em->remove($c);
		}
		$this->em->remove($category);
		$this->em->flush();
		$this->em->commit();

		$this->session->setFlash('deleted', $category->title);
		return $this->redirectRoute('admin_ticketcats');
	}

	############################################################################
	# update-orders
	############################################################################

	public function updateOrdersAction()
	{
		$helper = new \Application\AdminBundle\Controller\Helper\DisplayOrderUpdate($this);
		return $helper->doUpdate('ticket_categories');
	}
}
