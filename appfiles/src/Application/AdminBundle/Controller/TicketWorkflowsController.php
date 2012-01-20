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

use Application\AdminBundle\Form\EditTicketWorkflowType;

/**
 * Simple management of workflows
 */
class TicketWorkflowsController extends AbstractController
{
	############################################################################
	# list
	############################################################################

	/**
	 * Shows the main listing of workflows
	 */
	public function listAction()
	{
		$all_workflows = App::getOrm()->createQuery("
			SELECT w
			FROM DeskPRO:TicketWorkflow w
			ORDER BY w.display_order ASC
		")->execute();

		return $this->render('AdminBundle:TicketWorkflows:list.html.twig', array(
			'all_workflows' => $all_workflows
		));
	}



	############################################################################
	# edit
	############################################################################

	public function saveTitleAction()
	{
		$workflow_id = $this->in->getUint('workflow_id');
		$workflow = App::findEntity('DeskPRO:TicketWorkflow', $workflow_id);

		if (!$workflow) {
			throw $this->createNotFoundException();
		}

		if ($this->in->getString('title')) {
			$workflow->title = $this->in->getString('title');
		}

		$this->em->getConnection()->beginTransaction();

		try {
			$this->em->persist($workflow);
			$this->em->flush();

			$this->em->getConnection()->commit();
		} catch (\Exception $e) {
			$this->em->getConnection()->rollback();
			throw $e;
		}

		return $this->redirectRoute('admin_ticketworks');
	}

	public function saveNewAction()
	{
		$workflow = new \Application\DeskPRO\Entity\TicketWorkflow();
		$workflow->title = $this->in->getString('title');

		if (!$workflow->title) {
			$workflow->title = 'Untitled';
		}

		$this->em->getConnection()->beginTransaction();

		try {
			$this->em->persist($workflow);
			$this->em->flush();

			$this->em->getConnection()->commit();
		} catch (\Exception $e) {
			$this->em->getConnection()->rollback();
			throw $e;
		}

		return $this->redirectRoute('admin_ticketworks');
	}

	############################################################################
	# delete
	############################################################################

	public function deleteAction($workflow_id)
	{
		$workflow = App::getEntityRepository('DeskPRO:TicketWorkflow')->find($workflow_id);

		return $this->render('AdminBundle:TicketWorkflows:delete.html.twig', array(
			'workflow'  => $workflow,
		));
	}

	public function doDeleteAction($workflow_id, $security_token)
	{
		$workflow = App::getEntityRepository('DeskPRO:TicketWorkflow')->find($workflow_id);

		if (!$this->session->getEntity()->checkSecurityToken('delete_workflow', $security_token)) {
			// TODO err
			die('invalid token');
		}

		$this->em->beginTransaction();
		$this->em->remove($workflow);
		$this->em->flush();

		$count = App::getDb()->fetchColumn("SELECT COUNT(*) FROM ticket_workflows");
		if (!$count) {
			App::getEntityRepository('DeskPRO:Setting')->updateSetting('core.use_ticket_workflow', '0');
		}

		$this->em->commit();

		$this->session->setFlash('deleted', $workflow->title);
		return $this->redirectRoute('admin_ticketworks');
	}

	############################################################################
	# update-orders
	############################################################################

	public function updateOrdersAction()
	{
		$helper = new \Application\AdminBundle\Controller\Helper\DisplayOrderUpdate($this);
		return $helper->doUpdate('ticket_workflows');
	}
}
