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

use \Application\AdminBundle\Form\EditTicketPriorityForm;

/**
 * Lists fields, categories, priorities, widgets, workflows
 */
class TicketPropertiesController extends AbstractController
{
	############################################################################
	# list
	############################################################################

	/**
	 * Shows the main listing of priorities
	 */
	public function listAction()
	{
		$this->rememberLastPage();

		$tabs = array(
			'categories' => $this->forward('AdminBundle:TicketCategories:list')->getContent(),
			'priorities' => $this->forward('AdminBundle:TicketPriorities:list')->getContent(),
			'workflows'  => $this->forward('AdminBundle:TicketWorkflows:list')->getContent(),
			'widgets'    => $this->forward('AdminBundle:TicketWidgets:list')->getContent(),
		);
		return $this->render('AdminBundle:TicketProperties:list.html.twig', array(
			'tabs' => $tabs
		));
	}
}