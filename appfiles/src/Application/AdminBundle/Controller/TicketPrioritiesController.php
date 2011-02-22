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
 * Simple management of priorities
 */
class TicketPrioritiesController extends AbstractController
{
	############################################################################
	# list
	############################################################################

	/**
	 * Shows the main listing of priorities
	 */
	public function listAction()
	{
		$all_priorities = App::getOrm()->createQuery("
			SELECT p
			FROM DeskPRO:TicketPriority p
			ORDER BY p.priority ASC
		")->execute();

		return $this->render('AdminBundle:TicketPriorities:list.html.twig', array(
			'all_priorities' => $all_priorities
		));
	}



	############################################################################
	# edit
	############################################################################

	/**
	 * Edit a priority
	 */
	public function editAction($priority_id)
	{
		if (!$priority_id) {
			$priority = new Entity\TicketPriority();
		} else {
			$priority = App::getEntityRepository('DeskPRO:TicketPriority')->find($priority_id);
		}

		$form = EditTicketPriorityForm::create($this->get('form.context'), 'priority', array('priority' => $priority));
		$form->bind($this->get('request'), $priority);

		$is_edited = false;
		$row_html = false;
		if ($this->in->getBool('process')) {
			$is_edited = true;
			App::getOrm()->persist($priority);
			App::getOrm()->flush();

			$row_html = $this->renderView('AdminBundle:TicketPriorities:list-row.html.twig', array('priority' => $priority));
		}

		return $this->render('AdminBundle:TicketPriorities:edit.html.twig', array(
			'priority'  => $priority,
			'form'      => $form,
			'is_edited' => $is_edited,
			'row_html'  => $row_html
		));
	}
}