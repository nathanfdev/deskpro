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

	public function saveTitleAction()
	{
		$priority_id = $this->in->getUint('priority_id');
		$priority = App::findEntity('DeskPRO:TicketPriority', $priority_id);

		if (!$priority) {
			throw $this->createNotFoundException();
		}

		if ($this->in->getString('title')) {
			$priority->title = $this->in->getString('title');
		}

		$priority->priority = $this->in->getUint('priority');

		$this->em->getConnection()->beginTransaction();

		try {
			$this->em->persist($priority);
			$this->em->flush();

			$this->em->getConnection()->commit();
		} catch (\Exception $e) {
			$this->em->getConnection()->rollback();
			throw $e;
		}

		return $this->redirectRoute('admin_ticketpris');
	}

	public function saveNewAction()
	{
		$priority = new \Application\DeskPRO\Entity\TicketPriority();
		$priority->title = $this->in->getString('title');

		if (!$priority->title) {
			$priority->title = 'Untitled';
		}

		$priority->priority = $this->in->getUint('priority');

		$this->em->getConnection()->beginTransaction();

		try {
			$this->em->persist($priority);
			$this->em->flush();

			// First priority: enable the feature
			$count = App::getDb()->fetchColumn("SELECT COUNT(*) FROM ticket_priorities");
			if ($count == 1) {
				App::getEntityRepository('DeskPRO:Setting')->updateSetting('core.use_ticket_priority', '0');
			}

			$this->em->getConnection()->commit();
		} catch (\Exception $e) {
			$this->em->getConnection()->rollback();
			throw $e;
		}

		App::getEntityRepository('DeskPRO:Setting')->updateSetting('core.task_completed_add_ticketpriority', time());

		return $this->redirectRoute('admin_ticketpris');
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

		$count = App::getDb()->fetchColumn("SELECT COUNT(*) FROM ticket_priorities");
		if (!$count) {
			App::getEntityRepository('DeskPRO:Setting')->updateSetting('core.use_ticket_priority', '0');
		}

		$this->em->commit();

		$this->session->setFlash('deleted', $priority->title);
		return $this->redirectRoute('admin_ticketpris');
	}


	############################################################################
	# toggle-feature
	############################################################################

	public function toggleFeatureAction($enable)
	{
		if ($enable) {
			$count = App::getDb()->fetchColumn("SELECT COUNT(*) FROM ticket_priorities");
			if (!$count) {
				return $this->redirectRoute('admin_ticketpris');
			}

			App::getEntityRepository('DeskPRO:Setting')->updateSetting('core.use_ticket_priority', '1');
		} else {
				App::getEntityRepository('DeskPRO:Setting')->updateSetting('core.use_ticket_priority', '0');
		}

		$url = $this->generateUrl('admin_ticketpris');
		if ($this->in->getString('return')) {
			$url = $this->in->getString('return');
		}

		return $this->redirect($url);
	}
}
