<?php

namespace Application\AgentBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

class SettingsController extends AbstractController
{
	public function indexAction()
    {
        return $this->render('AgentBundle:Settings:index.twig');
    }

	
	
	############################################################################
	# Ticket Macros
	############################################################################

	public function ticketMacrosAction()
    {
		$all_macros = App::getOrm()->getRepository('DeskPRO:TicketMacro')->findAll();

		if (!count($all_macros)) {
			$all_macros = false;
		}

        return $this->render('AgentBundle:Settings:ticket-macros.twig', array(
			'show_saved_flash' => $this->in->getBool('saved'),
			'all_macros' => $all_macros
		));
    }

	public function ticketMacroEditAction($macro_id)
	{
		$ticket_options = App::getApi('tickets')->getTicketOptions($this->person);

		if ($macro_id) {

			$macro = App::getOrm()->getRepository('DeskPRO:TicketMacro')->find($macro_id);
			if (!$macro) {
				throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("Could not find macro");
			}

		} else {
			$macro = new Entity\TicketMacro();
			$macro['person'] = $this->person;
		}

		if ($this->isPostRequest()) {

			$macro['title'] = $this->in->getString('macro.title');
			$macro['is_global'] = true;
			$macro['actions'] = $this->in->getCleanValueArray('actions', 'raw', 'str_simple');

			App::getOrm()->persist($macro);
			App::getOrm()->flush();

			return $this->redirectRoute('agent_settings_ticketmacros', array('saved' => 1));
		}

        return $this->render('AgentBundle:Settings:ticket-macro-edit.twig', array(
			'ticket_options' => $ticket_options,
			'macro' => $macro
		));
	}

	public function ticketMacroDeleteAction($macro_id)
	{
		$macro = App::getOrm()->getRepository('DeskPRO:TicketMacro')->find($macro_id);
		if (!$macro) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("Could not find macro");
		}

		App::getOrm()->remove($macro);
		App::getOrm()->flush();

		return $this->redirectRoute('agent_settings_ticketmacros');
	}
}
