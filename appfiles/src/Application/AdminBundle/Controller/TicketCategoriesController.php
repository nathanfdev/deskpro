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

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

use \Application\AdminBundle\Form\EditTicketCategoryType;

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
			ORDER BY c.title ASC
		")->getResult();

		return $this->render('AdminBundle:TicketCategories:list.html.twig', array(
			'all_categories' => $all_categories
		));
	}



	############################################################################
	# edit
	############################################################################

	/**
	 * Edit a department
	 */
	public function editAction($category_id)
	{
		if (!$category_id) {
			$category = new Entity\TicketCategory();
		} else {
			$category = App::getEntityRepository('DeskPRO:TicketCategory')->find($category_id);
		}

		$form = $this->get('form.factory')->create(new EditTicketCategoryType($category), $category);
		
		$is_edited = false;
		$row_html = false;
		if ($this->in->getBool('process')) {
			$form->bindRequest($this->get('request'));

			if ($form->isValid()) {
				$is_edited = true;
				App::getOrm()->persist($category);
				App::getOrm()->flush();

				$row_html = $this->renderView('AdminBundle:TicketCategories:list-row.html.twig', array('category' => $category));

				// Recreate form because parent_id field cant be changed, so we need to get rid of it
				$form = $this->get('form.factory')->create(new EditTicketCategoryType($category), $category);
			}
		}

		return $this->render('AdminBundle:TicketCategories:edit.html.twig', array(
			'category' => $category,
			'form'      => $form->createView(),
			'is_edited' => $is_edited,
			'row_html'  => $row_html
		));
	}
}