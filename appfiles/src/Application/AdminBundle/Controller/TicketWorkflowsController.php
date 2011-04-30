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

use \Application\AdminBundle\Form\EditTicketWorkflowType;

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
			ORDER BY w.title ASC
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

		$is_edited = false;
		$row_html = false;
		if ($this->in->getBool('process')) {
			$form->bindRequest($this->get('request'));

			if ($form->isValid()) {
				$is_edited = true;
				App::getOrm()->persist($workflow);
				App::getOrm()->flush();

				$row_html = $this->renderView('AdminBundle:TicketWorkflows:list-row.html.twig', array('workflow' => $workflow));
			}
		}

		return $this->render('AdminBundle:TicketWorkflows:edit.html.twig', array(
			'workflow'  => $workflow,
			'form'      => $form->createView(),
			'is_edited' => $is_edited,
			'row_html'  => $row_html
		));
	}
}