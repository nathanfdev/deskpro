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
 * @subpackage AdminBundle
 */

namespace Application\AdminBundle\Controller;

use Application\DeskPRO\Entity;
use Application\DeskPRO\App;
use Orb\Util\Strings;
use Orb\Util\Arrays;
use Orb\Util\Util;

use Application\AdminBundle\Form\EditTicketTriggerType;
use Application\DeskPRO\UI\RuleBuilder;

use Application\AdminBundle\Urgency\UrgencyOptions;
use Application\AdminBundle\Form\TicketUrgencyOptionsType;
use Application\AdminBundle\AutoClose\AutoCloseOptions;
use Application\AdminBundle\Form\TicketAutoCloseOptionsType;

class TicketTriggersController extends AbstractController
{
	############################################################################
	# list
	############################################################################

	public function listAction()
	{
		$triggers = $this->em->getRepository('DeskPRO:TicketTrigger')->getGroupedTriggers();
		$triggers = Arrays::removeFalsey($triggers);

		return $this->render('AdminBundle:TicketTriggers:list.html.twig', array(
			'triggers' => $triggers,
		));
	}

	############################################################################
	# new-choose-type
	############################################################################

	public function newChooseTypeAction($trigger_type)
	{
		return $this->render('AdminBundle:TicketTriggers:edit-choosetype.html.twig', array(
			'trigger_type' => $trigger_type
		));
	}

	############################################################################
	# edit
	############################################################################

	public function editAction($trigger_id)
	{
		if (!$trigger_id) {
			$trigger = new Entity\TicketTrigger();

			switch ($this->in->getString('trigger_group')) {
				case 'new_ticket.web_person':
					$trigger['event_trigger'] = 'new_ticket';
					$trigger->terms = array(array('type' => 'creation_system', 'op' => 'is', 'options' => array('creation_system' => 'web.person')));
					break;
				case 'new_ticket.gateway_person':
					$trigger['event_trigger'] = 'new_ticket';
					$trigger->terms = array(array('type' => 'creation_system', 'op' => 'is', 'options' => array('creation_system' => 'gateway.person')));
					break;
				case 'new_ticket.widget':
					$trigger['event_trigger'] = 'new_ticket';
					$trigger->terms = array(array('type' => 'creation_system', 'op' => 'is', 'options' => array('creation_system' => 'widget')));
					break;
				case 'new_ticket.agent':
					$trigger['event_trigger'] = 'new_ticket';
					$trigger->terms = array(array('type' => 'creation_system', 'op' => 'is', 'options' => array('creation_system' => 'web.agent')));
					break;
				case 'new_reply.agent':
					$trigger['event_trigger'] = 'new_reply';
					$trigger->terms = array(array('type' => 'action_performer', 'op' => 'is', 'options' => array('action_performer' => 'agent')));
					break;
				case 'new_reply.web_person':
					$trigger['event_trigger'] = 'new_reply';
					$trigger->terms = array(
						array('type' => 'action_performer', 'op' => 'is', 'options' => array('action_performer' => 'user')),
						array('type' => 'creation_system', 'op' => 'is', 'options' => array('creation_system' => 'web'))
					);
					break;
				case 'new_reply.gateway_person':
					$trigger['event_trigger'] = 'new_reply';
					$trigger->terms = array(
						array('type' => 'action_performer', 'op' => 'is', 'options' => array('action_performer' => 'user')),
						array('type' => 'creation_system', 'op' => 'is', 'options' => array('creation_system' => 'gateway'))
					);
					break;
				case 'property_change.agent':
					$trigger['event_trigger'] = 'property_change';
					$trigger->terms = array(array('type' => 'action_performer', 'op' => 'is', 'options' => array('action_performer' => 'agent')));
					break;
				case 'property_change.user':
					$trigger['event_trigger'] = 'property_change';
					$trigger->terms = array(array('type' => 'action_performer', 'op' => 'is', 'options' => array('action_performer' => 'user')));
					break;
				default:
					$trigger['event_trigger'] = $this->in->getString('trigger_group');
					break;
			}

			if ($this->in->getUint('event_trigger_time')) {
				$trigger->event_trigger_option = $this->in->getUint('event_trigger_time') . ' ' . $this->in->getString('event_trigger_scale');
			}

		} else {
			$trigger = $this->em->getRepository('DeskPRO:TicketTrigger')->find($trigger_id);
			if ($this->in->getString('trigger.event_trigger_option')) {
				$trigger['event_trigger_option'] = $this->in->getString('trigger.event_trigger_option');
			}
		}

		if ($trigger->getTriggerGroup() == 'other') {
			return $this->redirectRoute('admin_tickettriggers_new_choosetype');
		}

		$ticket_options = App::getApi('tickets')->getTicketOptions($this->person);
		$ticket_options['people_term_options']  = array();
		$ticket_options['people_term_options']  = array();
		$ticket_options['people_term_options']['organizations']  = App::getOrm()->getRepository('DeskPRO:Organization')->getOrganizationNames();
		$ticket_options['people_term_options']['usergroups']  = App::getOrm()->getRepository('DeskPRO:Usergroup')->getUsergroupNames();
		$ticket_options['people_term_options']['languages']  = App::getOrm()->getRepository('DeskPRO:Language')->getTitles();

		$form = $this->get('form.factory')->create(new EditTicketTriggerType($trigger), $trigger);

		if ($this->in->getBool('process')) {

			$form->bindRequest($this->get('request'));

			if ($form->isValid()) {
				if (!$trigger->title) {
					$trigger->title = '';
				}
				$this->em->beginTransaction();

				$term_rules = RuleBuilder::newTermsBuilder();
				$trigger['terms'] = $term_rules->readForm($this->in->getCleanValueArray('terms', 'raw' , 'discard'));

				$action_rules = RuleBuilder::newActionsBuilder();
				$trigger['actions'] = $action_rules->readForm($this->in->getCleanValueArray('actions', 'raw' , 'discard'));

				$this->em->persist($trigger);
				$this->em->flush();

				$this->em->commit();

				return $this->redirectRoute('admin_tickettriggers');
			}
		}

		$ticket_options['email_gateway_addresses'] = $this->em->getRepository('DeskPRO:EmailGatewayAddress')->getOptions();

		return $this->render('AdminBundle:TicketTriggers:edit.html.twig', array(
			'trigger' => $trigger,
			'event_trigger_time' => $trigger->getOptionTime(),
			'event_trigger_scale' => $trigger->getOptionScale(),
			'form'      => $form->createView(),
			'term_options' => $ticket_options,
		));
	}

	############################################################################
	# update-order
	############################################################################

	public function updateOrderAction()
	{
		$trigger_ids = $this->in->getCleanValueArray('trigger_ids', 'uint', 'discard');

		$x = 10;

		$this->db->beginTransaction();
		try {
			foreach ($trigger_ids as $id) {
				$this->db->update('ticket_triggers', array('run_order' => $x), array('id' => $id));
				$x += 10;
			}

			$this->db->commit();
		} catch (\Exception $e) {
			$this->db->rollback();
			throw $e;
		}

		return $this->createJsonResponse(array('success' => true));
	}

	############################################################################
	# toggle-enabled
	############################################################################

	public function toggleEnabledAction()
	{
		$trigger = $this->em->find('DeskPRO:TicketTrigger', $this->in->getUint('trigger_id'));

		if ($trigger) {
			$trigger->is_enabled = $this->in->getBool('onoff');
			$this->em->persist($trigger);
			$this->em->flush();
		}

		return $this->createJsonResponse(array('success' => true));
	}

	############################################################################
	# delete
	############################################################################

	public function deleteAction($id, $auth)
	{
		$this->ensureAuthToken('delete_trigger', $auth);

		$trigger = $this->em->find('DeskPRO:TicketTrigger', $id);

		if ($trigger) {
			$this->db->beginTransaction();
			try {
				$this->em->remove($trigger);
				$this->em->flush();
				$this->db->commit();
			} catch (\Exception $e) {
				$this->db->rollback();
				throw $e;
			}
		}

		return $this->redirectRoute('admin_tickettriggers');
	}
}
