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
	# Upload picture
	############################################################################

	public function pictureAction()
	{
		if ($this->in->getBool('do_upload')) {
			$file = $this->request->files->get('picture');

			try {
				$im = new \Imagick($file->getPath());

				$s = min(100, $im->getImageWidth(), $im->getImageHeight());
				$im->resizeImage($s, $s, \Imagick::FILTER_LANCZOS, true);
				$im->setImageFormat('png');
			} catch (\Exception $e) {
				die('invalid image');
			}

			$desc = App::getApi('filestorage')->createRandomPath();

			$desc->write($im->getImageBlob(), array(
				'content_type' => $file->getMimeType(),
				'filename' => $file->getOriginalName()
			));

			$im->destroy();
			unset($im);

			$blob_id = $desc->getPath();
			$blob = App::getOrm()->getRepository('DeskPRO:Blob')->find($blob_id);

			$this->person['picture_blob'] = $blob;
			App::getOrm()->persist($this->person);
			App::getOrm()->flush();
		}

		return $this->render('AgentBundle:Settings:picture.twig.html', array(

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

		$ticket_field_defs = App::getApi('custom_fields.tickets')->getEnabledFields();
		$custom_fields = App::getApi('custom_fields.tickets')->getFieldsDisplayArray($ticket_field_defs);
		$term_options['custom_ticket_fields'] = $custom_fields;

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
		$queue['title']    = $this->in->getString('queue.title');
		$queue['group_by'] = $this->in->getString('queue.group_by');
		$queue['order_by'] = $this->in->getString('queue.order_by');
		$queue['terms']    = $this->in->getCleanValueArray('terms', 'raw' , 'discard');

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

			$ticket_field_defs = App::getApi('custom_fields.tickets')->getEnabledFields();

			$macro['title'] = $this->in->getString('macro.title');
			$macro['is_global'] = true;

			$actions = $this->in->getCleanValueArray('actions', 'raw', 'str_simple');
			foreach ($actions as $k => &$action) {

				// This complexity is because the values expected by the field handlers to render
				// data was written to handle the models in the db,
				// but here saving the macro we only have a form, so we're hacking together
				// fake model data so we have the correct data structure.
				// see Ticket::setCustomData
				if (strpos($action['rule_type'], 'ticket_field') === 0) {
					$field_id = preg_replace('#^ticket_field\[(.*?)\]$#', '$1', $action['rule_type']);
					$field = App::getEntityRepository('DeskPRO:CustomDefTicket')->find($field_id);

					$action_custm_datas = array();

					foreach ($field->getHandler()->getDataFromForm($_POST['actions'][$k]) as $info) {
						$custom_data = new Entity\CustomDataTicket();
						$custom_data['field'] = $field;
						$custom_data[$info[1]] = $info[2];

						$action_custm_datas[] = $custom_data;
					}

					$ticket_data_structured = App::getApi('custom_fields.util')->createDataHierarchy($action_custm_datas, $ticket_field_defs);
					$ticket_data_structured = $ticket_data_structured[$field_id];

					$action['renderable_value'] = $ticket_data_structured;
				}
			}

			$macro['actions'] = $actions;

			App::getOrm()->persist($macro);
			App::getOrm()->flush();

			return $this->redirectRoute('agent_settings_ticketmacros', array('saved' => 1));
		}

		$ticket_field_defs = App::getApi('custom_fields.tickets')->getEnabledFields();
		$custom_fields = App::getApi('custom_fields.tickets')->getFieldsDisplayArray($ticket_field_defs);
		$ticket_options['custom_ticket_fields'] = $custom_fields;

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
