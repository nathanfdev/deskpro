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

use \Application\AdminBundle\Form\EditTicketWorkflowForm;

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

		$form = EditTicketWorkflowForm::create($this->get('form.context'), 'workflow', array('workflow' => $workflow));
		$form->bind($this->get('request'), $workflow);

		$is_edited = false;
		$row_html = false;
		if ($this->in->getBool('process')) {
			$is_edited = true;
			App::getOrm()->persist($workflow);
			App::getOrm()->flush();

			$row_html = $this->renderView('AdminBundle:TicketWorkflows:list-row.html.twig', array('workflow' => $workflow));
		}

		return $this->render('AdminBundle:TicketWorkflows:edit.html.twig', array(
			'workflow'  => $workflow,
			'form'      => $form,
			'is_edited' => $is_edited,
			'row_html'  => $row_html
		));
	}
}