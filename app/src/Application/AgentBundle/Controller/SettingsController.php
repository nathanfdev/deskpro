<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
* DeskPRO
*
* @package DeskPRO
*/

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

		if ($edit_profile->requiresAuth()) {
			$code = $this->in->getString('authcode');
			if (!$this->session->getEntity()->checkSecurityToken('password_confirm' . $this->person->secret_string, $code)) {
				return $this->createJsonResponse(array('error' => true, 'error_code' => 'invalid_auth'));
			}
		}

		// Check for dupe emails where a user already exists, we'll send a merge request
		// -> Only do this when the other user is a plain user and not an agent
		// TODO: user merge
		if (false) {
			$check_exists = $this->em->getRepository('DeskPRO:Person')->findByEmail($edit_profile->email);
			if ($check_exists && $check_exists->getId() != $this->person->getId() && !$check_exists->is_agent) {

				// Insert the merge code
				$tmpdata = \Application\DeskPRO\Entity\TmpData::create('validated_merge_user', array(
					'agent_id' => $this->person->getId(),
					'other_user_id' => $check_exists->getId(),
					'email_address' => $edit_profile->email,
					'_type' => 'agent_profile_email',
				), '+2 days');

				$this->em->persist($tmpdata);
				$this->em->flush();

				$vars = array(
					'person'       => $this->person,
					'other_person' => $check_exists,
					'authcode'     => $tmpdata->getCode(),
					'old_email'    => $this->person->getEmailAddress(),
					'new_email'    => $edit_profile->email
				);

				// Send validation email
				$message = $this->container->getMailer()->createMessage();
				$message->setTemplate('DeskPRO:emails_agent:agent-changeemail-mergeuser.html.twig', $vars);
				$message->setTo($edit_profile->email, $agent->getDisplayName());
				$this->container->getMailer()->send($message);

				// Pop the old email address back so it passes the dupe check validation,
				// we're not actually updating the address yet
				$edit_profile->email = $this->person->getEmailAddress();
			}
		}

		$validator = new \Application\AgentBundle\Validator\AgentProfileValidator();
		if (!$validator->isValid($edit_profile)) {
			return $this->createJsonResponse(array(
				'error' => true,
				'error_code' => 'form_errors',
				'form_errors' => $validator->getErrors()
			));
		}

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

		$admin_triggers = $this->em->getRepository('DeskPRO:TicketTrigger')->findTriggersForcingNotificationForAgent($this->person);

		return $this->render('AgentBundle:Settings:ticket-notifications.html.twig', array(
			'all_filters' => $all_filters,
			'sys_filters' => $sys_filters,
			'sys_filters_hold' => $sys_filters_hold,
			'custom_filters' => $custom_filters,
			'my_subs' => $my_subs,
			'admin_triggers' => $admin_triggers,
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
		$prefs = $this->in->getCleanValueArray('my_prefs', 'bool', 'string');

		$person_editor = $this->container->getSystemService('person_edit_manager');
		$person_editor->saveNotificationPreferences($this->person, $prefs);

		return $this->createJsonResponse(array('success' => true));
	}

	############################################################################
	# Ticket Filters
	############################################################################

	/**
	 * Just a list of filters
	 */
	public function ticketFiltersAction()
	{
		$filters = $this->em->getRepository('DeskPRO:TicketFilter')->getPersonalFilters($this->person);

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

		$term_options = App::getApi('tickets')->getTicketOptions($this->person);

		$ticket_field_defs = App::getApi('custom_fields.tickets')->getEnabledFields();
		$custom_fields = App::getApi('custom_fields.tickets')->getFieldsDisplayArray($ticket_field_defs);
		$term_options['custom_ticket_fields'] = $custom_fields;

		return $this->render('AgentBundle:Settings:ticket-filter-edit.html.twig', array(
			'term_options' => $term_options,
			'filter' => $filter,
		));
	}

	public function ticketFilterEditSaveAction($filter_id)
	{
		if ($filter_id) {
			$is_new = false;
			$filter = $this->em->find('DeskPRO:TicketFilter', $filter_id);
			if ($filter AND $filter['sys_name']) {
				$filter = null;
			}

			if (!$filter) {
				throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no filter with ID $filter_id");
			}
		} else {
			$is_new = true;
			$filter = new Entity\TicketFilter;
		}

		$filter['title']    = $this->in->getString('filter.title');
		$filter['person']   = $this->person;
		$filter['order_by'] = $this->in->getString('filter.order_by');

		$term_rules = RuleBuilder::newTermsBuilder();
		$filter['terms'] = $term_rules->readForm($this->in->getCleanValueArray('terms', 'raw' , 'discard'));

		$filter['is_global'] = true;
		$filter['is_enabled'] = true;

		$this->em->persist($filter);
		$this->em->flush();

		return $this->createJsonResponse(array('success' => true, 'filter_id' => $filter->id, 'filter_title' => $filter->title, 'is_new' => $is_new));
	}

	public function ticketFilterDeleteAction($filter_id)
	{
		$filter = $this->em->find('DeskPRO:TicketFilter', $filter_id);
		if (!$filter) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("Could not find filter");
		}

		$this->em->remove($filter);
		$this->em->flush();

		return $this->createJsonResponse(array('success' => true));
	}



	############################################################################
	# Ticket Macros
	############################################################################

	public function ticketMacrosAction()
    {
		$all_macros = $this->em->getRepository('DeskPRO:TicketMacro')->findAll();

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

			$macro = $this->em->getRepository('DeskPRO:TicketMacro')->find($macro_id);
			if (!$macro) {
				throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("Could not find macro");
			}

		} else {
			$macro = new Entity\TicketMacro();
			$macro['person'] = $this->person;
		}

		$ticket_field_defs = App::getApi('custom_fields.tickets')->getEnabledFields();
		$custom_fields = App::getApi('custom_fields.tickets')->getFieldsDisplayArray($ticket_field_defs);
		$ticket_options['custom_ticket_fields'] = $custom_fields;

		// People stuff
		$ticket_options['people_organizations'] = $this->em->getRepository('DeskPRO:Organization')->getOrganizationNames();
		$people_field_defs = App::getApi('custom_fields.people')->getEnabledFields();
		$ticket_options['custom_people_fields'] = $custom_fields = App::getApi('custom_fields.people')->getFieldsDisplayArray($people_field_defs);

        return $this->render('AgentBundle:Settings:ticket-macro-edit.html.twig', array(
			'ticket_options' => $ticket_options,
			'macro' => $macro
		));
	}

	public function ticketMacroEditSaveAction($macro_id)
	{
		if ($macro_id) {

			$macro = $this->em->getRepository('DeskPRO:TicketMacro')->find($macro_id);
			if (!$macro) {
				throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("Could not find macro");
			}

		} else {
			$macro = new Entity\TicketMacro();
			$macro['person'] = $this->person;
		}

		$macro['title'] = $this->in->getString('macro.title');
		$macro['is_global'] = false;

		$action_rules = RuleBuilder::newActionsBuilder();
		$actions = $action_rules->readForm($this->in->getCleanValueArray('actions', 'raw', 'str_simple'));

		$macro['actions'] = $actions;

		$this->em->persist($macro);
		$this->em->flush();

		return $this->createJsonResponse(array('success' => true));
	}

	public function ticketMacroDeleteAction($macro_id)
	{
		$macro = $this->em->getRepository('DeskPRO:TicketMacro')->find($macro_id);
		if (!$macro) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("Could not find macro");
		}

		$this->em->remove($macro);
		$this->em->flush();

		return $this->createJsonResponse(array('success' => true));
	}
}
