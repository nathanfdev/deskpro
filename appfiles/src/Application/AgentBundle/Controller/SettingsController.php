<?php

namespace Application\AgentBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;
use Application\DeskPRO\UI\RuleBuilder;

class SettingsController extends AbstractController
{
	############################################################################
	# Profile
	############################################################################

	public function profileAction()
	{
		$edit_profile = new \Application\AgentBundle\Form\Model\SettingsProfile($this->person);
		$edit_form    = new \Application\AgentBundle\Form\Type\SettingsProfile();
		$form      = $this->get('form.factory')->create($edit_form, $edit_profile);

        return $this->render('AgentBundle:Settings:profile.html.twig', array(
			'form' => $form->createView(),
			'edit_profile' => $edit_profile
		));
    }

	public function profileSaveAction()
	{
		$edit_profile = new \Application\AgentBundle\Form\Model\SettingsProfile($this->person);
		$edit_form    = new \Application\AgentBundle\Form\Type\SettingsProfile();
		$form      = $this->get('form.factory')->create($edit_form, $edit_profile);

		$form->bindRequest($this->get('request'));

		$edit_profile->save();

		return $this->createJsonResponse(array('success' => true));
	}

	############################################################################
	# Ticket Notifications
	############################################################################

	public function ticketNotificationsAction()
	{
		$filter_info      = App::getApi('tickets.filters')->getGroupedFiltersForPerson($this->person);
		$all_filters      = $filter_info['all_filters'];
		$sys_filters      = $filter_info['sys_filters'];
		$sys_filters_hold = $filter_info['sys_filters_hold'];
		$custom_filters   = $filter_info['custom_filters'];

		$my_subs = $this->em->getRepository('DeskPRO:TicketFilterSubscription')->getForAgent($this->person);

		return $this->render('AgentBundle:Settings:ticket-notifications.html.twig', array(
			'all_filters' => $all_filters,
			'sys_filters' => $sys_filters,
			'sys_filters_hold' => $sys_filters_hold,
			'custom_filters' => $custom_filters,
			'my_subs' => $my_subs,
		));
	}

	public function ticketNotificationsSaveAction()
	{
		$subs = $this->in->getCleanValueArray('filter_sub', 'array', 'uint');

		$person_editor = $this->container->getSystemService('person_edit_manager');
		$person_editor->saveFilterSubscriptions($this->person, $subs);

		return $this->createJsonResponse(array('success' => true));
	}

	############################################################################
	# General Notifications
	############################################################################

	public function otherNotificationsAction()
	{
		$my_prefs = $this->em->getRepository('DeskPRO:PersonPref')->getPrefgroupForPersonId('agent_notif', $this->person->id, true);
		return $this->render('AgentBundle:Settings:other-notifications.html.twig', array(
			'my_prefs' => $my_prefs,
		));
	}

	public function otherNotificationsSaveAction()
	{
		$prefs = $this->in->getCleanValueArray('notify_prefs', 'bool', 'string');

		$person_editor = $this->container->getSystemService('person_edit_manager');
		$person_editor->saveNotificationPreferences($this->person, $prefs);

		return $this->createJsonResponse(array('success' => true));
	}


	############################################################################
	# Upload picture
	############################################################################

	public function pictureAction()
	{
		if ($this->in->getBool('do_upload')) {
			$file = $this->request->files->get('picture');

			try {
				$im = new \Imagick($file->getRealPath());

				$s = min(100, $im->getImageWidth(), $im->getImageHeight());
				$im->resizeImage($s, $s, \Imagick::FILTER_LANCZOS, true);
				$im->setImageFormat('png');
			} catch (\Exception $e) {
				die('invalid image');
			}

			$desc = App::getApi('filestorage')->createRandomPath();

			$desc->write($im->getImageBlob(), array(
				'content_type' => $file->getMimeType(),
				'filename' => $file->getClientOriginalName()
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
		return $this->createResponse('not updated yet');

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

		$is_saved = false;
		if ($this->isPostRequest()) {
			$errors = $this->_processEditFilter($filter);
			if (!$errors) {
				$is_saved = true;
			}
		}

		return $this->render('AgentBundle:Settings:ticket-filter-edit.html.twig', array(
			'term_options' => $term_options,
			'filter' => $filter,
			'is_saved' => $is_saved,
		));
	}

	public function _processEditFilter(Entity\TicketFilter $filter)
	{
		$filter['title']    = $this->in->getString('filter.title');
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
		return $this->createResponse('not updated yet');

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
			$macro['is_global'] = false;

			$action_rules = RuleBuilder::newActionsBuilder();
			$actions = $action_rules->readForm($this->in->getCleanValueArray('actions', 'raw', 'str_simple'));

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
