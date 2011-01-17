<?php

namespace Application\AgentBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

class SettingsController extends AbstractController
{
	public function indexAction()
    {
		$pref_name = 'agent.ui.show-listpane';
		if ($this->isPostRequest()) {
			$pref = App::getOrm()->getRepository('DeskPRO:PersonPref')->find(array('person_id' => $this->person['id'], 'name' => $pref_name));
			if (!$pref) {
				$pref = new Entity\PersonPref();
				$pref['name'] = $pref_name;
				$this->person->addPreference($pref);
			}

			$pref['value'] = $this->in->getString('show_listpane');
			App::getOrm()->persist($pref);
			App::getOrm()->flush();
		}

        return $this->render('AgentBundle:Settings:index.twig.html', array(
			'show_listpane' => $this->person->getPref($pref_name)
		));
    }


	############################################################################
	# Ticket Queues
	############################################################################

	/**
	 * Just a list of queues
	 */
	public function ticketQueuesAction()
	{
		$queues = App::getApi('tickets.queues')->getQueuesForPerson($this->person);

		return $this->render('AgentBundle:Settings:ticket-queues.twig.html', array(
			'queues' => $queues
		));
	}

	/**
	 * Edit a queue
	 */
	public function ticketQueueEditAction($queue_id)
	{
		if ($queue_id) {
			try {
				$queue = $this->em->find('DeskPRO:TicketQueue', $queue_id);
			} catch (\Doctrine\ORM\NoResultException $e) {
				throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no queue with ID $queue_id");
			}
		} else {
			$queue = new Entity\TicketQueue;
		}

		$term_options = App::getApi('tickets.search')->getSearchOptions($this->person);

		if ($this->isPostRequest()) {
			$errors = $this->_processEditQueue($queue);
			if (!$errors) {
				echo "Saved!";
			}
		}

		return $this->render('AgentBundle:Settings:ticket-queue-edit.twig.html', array(
			'term_options' => $term_options,
			'queue' => $queue
		));
	}

	public function _processEditQueue(Entity\TicketQueue $queue)
	{
		$queue['title'] = $this->in->getString('title');
		$queue['terms'] = $this->in->getCleanValueArray('terms', 'raw' , 'discard');

		if (!$queue['person_id']) {
			$queue['person'] = $this->person;
		}

		$queue['is_global'] = true;
		$queue['is_enabled'] = true;

		$this->em->persist($queue);
		$this->em->flush();

		return null;
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

        return $this->render('AgentBundle:Settings:ticket-macros.twig.html', array(
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

        return $this->render('AgentBundle:Settings:ticket-macro-edit.twig.html', array(
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
