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

	/**
	 * Edit a workflow
	 */
	public function editAction($workflow_id)
	{
		if (!$workflow_id) {
			$workflow = new Entity\TicketWorkflow();
		} else {
			$workflow = App::getEntityRepository('DeskPRO:TicketWorkflow')->find($workflow_id);
		}

		$form = $this->get('form.factory')->create(new EditTicketWorkflowType(), $workflow);

		if ($this->in->getBool('process')) {
			$form->bindRequest($this->get('request'));

			if ($form->isValid()) {
				App::getOrm()->persist($workflow);
				App::getOrm()->flush();

				$this->session->setFlash('saved', $workflow->title);
				return $this->redirectRoute('admin_ticketworks');
			}
		}

		return $this->render('AdminBundle:TicketWorkflows:edit.html.twig', array(
			'workflow'  => $workflow,
			'form'      => $form->createView(),
		));
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
