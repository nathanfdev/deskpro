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

use Application\AdminBundle\Form\EditTicketPriorityType;

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

		$form = $this->get('form.factory')->create(new EditTicketPriorityType(), $priority);

		if ($this->in->getBool('process')) {
			$form->bindRequest($this->get('request'));

			if ($form->isValid()) {
				App::getOrm()->persist($priority);
				App::getOrm()->flush();

				$this->session->setFlash('saved', $priority->title);
				return $this->redirectRoute('admin_ticketpris');
			}
		}

		return $this->render('AdminBundle:TicketPriorities:edit.html.twig', array(
			'priority'  => $priority,
			'form'      => $form->createView(),
		));
	}

	############################################################################
	# delete
	############################################################################

	public function deleteAction($priority_id)
	{
		$priority = App::getEntityRepository('DeskPRO:TicketPriority')->find($priority_id);

		return $this->render('AdminBundle:TicketPriorities:delete.html.twig', array(
			'priority'  => $priority,
		));
	}

	public function doDeleteAction($priority_id, $security_token)
	{
		$priority = App::getEntityRepository('DeskPRO:TicketPriority')->find($priority_id);

		if (!$this->session->getEntity()->checkSecurityToken('delete_ticket_priority', $security_token)) {
			// TODO err
			die('invalid token');
		}

		$this->em->beginTransaction();
		$this->em->remove($priority);
		$this->em->flush();
		$this->em->commit();

		$this->session->setFlash('deleted', $priority->title);
		return $this->redirectRoute('admin_ticketpris');
	}
}
