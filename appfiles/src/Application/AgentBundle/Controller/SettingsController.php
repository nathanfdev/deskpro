<?php

namespace Application\AgentBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;
use Application\DeskPRO\UI\RuleBuilder;

class SettingsController extends AbstractController
{
	public function indexAction()
    {
		if ($this->isPostRequest()) {
			$person = $this->person;
			$prefs = array();

			$prefs[] = $person->setPreference('agent.ticket_signature', $this->in->getString('ticket_signature'));

			if ($this->in->getBool('favicon_count_toggle')) {
				$prefs[] = $person->setPreference('agent.ui.favicon_count', $this->in->getString('favicon_count'));
			} else {
				$prefs[] = $person->setPreference('agent.ui.favicon_count', false);
			}

			$prefs[] = $person->setPreference('agent.ui.desktop_notifications', $this->in->getBool('desktop_notifications'));

			App::getOrm()->transactional(function ($em) use ($person, $prefs) {
				$em->persist($person);

				foreach ($prefs as $pref) {
					$em->persist($pref);
				}

				$em->flush();
			});
		}

        return $this->render('AgentBundle:Settings:index.html.twig', array(
			'ticket_signature' => $this->person->getPref('agent.ticket_signature'),
			'favicon_count' => $this->person->getPref('agent.ui.favicon_count'),
			'desktop_notifications' => $this->person->getPref('agent.ui.desktop_notifications'),
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

		return $this->render('AgentBundle:Settings:picture.html.twig', array(

		));
	}


	############################################################################
	# Ticket Filters
	############################################################################

	/**
	 * Just a list of filters
	 */
	public function ticketFiltersAction()
	{
		$filters_all = App::getApi('tickets.filters')->getFiltersForPerson($this->person);

		$filters = array();
		foreach ($filters_all as $q) {
			if (!$q['sys_name']) {
				$filters[] = $q;
			}
		}

		return $this->render('AgentBundle:Settings:ticket-filters.html.twig', array(
			'filters' => $filters
		));
	}

	/**
	 * Edit a filter
	 */
	public function ticketFilterEditAction($filter_id)
	{
		if ($filter_id) {
			$filter = $this->em->find('DeskPRO:TicketFilter', $filter_id);
			if ($filter AND $filter['sys_name']) {
				$filter = null;
			}

			if (!$filter) {
				throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no filter with ID $filter_id");
			}
		} else {
			$filter = new Entity\TicketFilter;
		}

		$term_options = App::getApi('tickets.search')->getSearchOptions($this->person);

		$ticket_field_defs = App::getApi('custom_fields.tickets')->getEnabledFields();
		$custom_fields = App::getApi('custom_fields.tickets')->getFieldsDisplayArray($ticket_field_defs);
		$term_options['custom_ticket_fields'] = $custom_fields;

		if ($this->isPostRequest()) {
			$errors = $this->_processEditFilter($filter);
			if (!$errors) {
				echo "Saved!";
			}
		}

		return $this->render('AgentBundle:Settings:ticket-filter-edit.html.twig', array(
			'term_options' => $term_options,
			'filter' => $filter
		));
	}

	public function _processEditFilter(Entity\TicketFilter $filter)
	{
		$filter['title']    = $this->in->getString('filter.title');
		$filter['group_by'] = $this->in->getString('filter.group_by');
		$filter['order_by'] = $this->in->getString('filter.order_by');

		$term_rules = RuleBuilder::newTermsBuilder();
		$filter['terms'] = $term_rules->readForm($this->in->getCleanValueArray('terms', 'raw' , 'discard'));

		if (!$filter['person_id']) {
			$filter['person'] = $this->person;
		}

		$filter['is_global'] = true;
		$filter['is_enabled'] = true;

		$this->em->persist($filter);
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

        return $this->render('AgentBundle:Settings:ticket-macros.html.twig', array(
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

			$action_rules = RuleBuilder::newActionsBuilder();
			$actions = $action_rules->readForm($this->in->getCleanValueArray('actions', 'raw', 'str_simple'));

			foreach ($actions as $k => &$action) {

				if (strpos($action['rule_type'], 'ticket_field') === 0) {
					$field_id = preg_replace('#^ticket_field\[(.*?)\]$#', '$1', $action['rule_type']);
					$action['renderable_value'] = App::getApi('custom_fields.util')->getRenderableDataArrayFromForm(
						$_POST['actions'][$k],
						$field_id,
						'DeskPRO:CustomDefTicket',
						'DeskPRO:CustomDataTicket'
					);
				} elseif (strpos($action['rule_type'], 'user_field') === 0) {
					$field_id = preg_replace('#^user_field\[(.*?)\]$#', '$1', $action['rule_type']);
					$action['renderable_value'] = App::getApi('custom_fields.util')->getRenderableDataArrayFromForm(
						$_POST['actions'][$k],
						$field_id,
						'DeskPRO:CustomDefPerson',
						'DeskPRO:CustomDataPerson'
					);
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

		// People studd
		$ticket_options['people_organizations'] = App::getEntityRepository('DeskPRO:Organization')->getOrganizationNames();
		$people_field_defs = App::getApi('custom_fields.people')->getEnabledFields();
		$ticket_options['custom_people_fields'] = $custom_fields = App::getApi('custom_fields.people')->getFieldsDisplayArray($people_field_defs);

        return $this->render('AgentBundle:Settings:ticket-macro-edit.html.twig', array(
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
