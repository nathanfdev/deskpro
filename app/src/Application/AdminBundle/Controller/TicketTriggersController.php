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

use Application\DeskPRO\Entity\TicketTrigger;
use Application\AdminBundle\Form\EditTicketTriggerType;
use Application\DeskPRO\UI\RuleBuilder;

class TicketTriggersController extends AbstractController
{
	############################################################################
	# list-triggers
	############################################################################

	public function listTriggersAction($list_type)
	{
		switch ($list_type) {
			case 'new':
				$types = array(
					'new.email.user',
					'new.email.agent',
					'new.web.agent',
					'new.web.agent.portal',
					'new.web.user',
					'new.web.user.portal',
					'new.web.user.widget',
					'new.web.user.embed',
					'new.web.api'
				);
				$list_tpl = 'AdminBundle:TicketTriggers:list-triggers-new.html.twig';
				break;

			case 'update':
				$types = array('update.agent', 'update.user', 'update.api');
				$list_tpl = 'AdminBundle:TicketTriggers:list-triggers-update.html.twig';
				break;

			default:
				return $this->redirectRoute('admin_tickettriggers', array('list_type' => 'new'));
		}

		$triggers = $this->em->getRepository('DeskPRO:TicketTrigger')->getGroupedTriggers($types);

		$triggers['new.web.user_any'] = array();
		if (!empty($triggers['new.web.user'])) $triggers['new.web.user_any'] = array_merge($triggers['new.web.user_any'], $triggers['new.web.user']);
		if (!empty($triggers['new.web.user.portal'])) $triggers['new.web.user_any'] = array_merge($triggers['new.web.user_any'], $triggers['new.web.user.portal']);
		if (!empty($triggers['new.web.user.widget'])) $triggers['new.web.user_any'] = array_merge($triggers['new.web.user_any'], $triggers['new.web.user.widget']);
		if (!empty($triggers['new.web.user.embed'])) $triggers['new.web.user_any'] = array_merge($triggers['new.web.user_any'], $triggers['new.web.user.embed']);

		$show_api_option = $this->em->getRepository('DeskPRO:ApiKey')->countApiKeys() > 0;

		return $this->render($list_tpl, array(
			'list_type'  => $list_type,
			'types'      => $types,
			'triggers'   => $triggers,
			'show_api_option' => $show_api_option
		));
	}

	public function listEscalationsAction()
	{
		$types = array(
			'time.open',
			'time.user_waiting',
			'time.total_user_waiting',
			'time.agent_waiting',
			'time.resolved',
		);
		$triggers = $this->em->getRepository('DeskPRO:TicketTrigger')->getGroupedTriggers($types);

		return $this->render('AdminBundle:TicketTriggers:list-escalations.html.twig', array(
			'triggers'   => $triggers,
		));
	}


	############################################################################
	# edit trigger
	############################################################################

	public function editTriggerAction($id, $trigger_type = null)
	{
		if ($id) {
			$trigger = $this->em->find('DeskPRO:TicketTrigger', $id);
			if (!$trigger) {
				throw $this->createNotFoundException();
			}
		} else {
			$trigger = new TicketTrigger();

			if ($trigger_type == null) {
				return $this->redirectRoute('admin_tickettriggers_new', array('type' => 'new.email.user'));
			}

			$trigger->event_trigger = $trigger_type;
		}

		if ($trigger->getTriggerType() == 'escalation') {
			return $this->redirectRoute('admin_ticketescalations_edit', array('id' => $trigger->getId()));
		}

		return $this->render('AdminBundle:TicketTriggers:edit-trigger.html.twig', array(
			'trigger'      => $trigger,
			'term_options' => $this->_getTermOptions(),
		));
	}

	public function editEscalationAction($id, $trigger_type = null)
	{
		if ($id) {
			$trigger = $this->em->find('DeskPRO:TicketTrigger', $id);
			if (!$trigger) {
				throw $this->createNotFoundException();
			}
		} else {
			$trigger = new TicketTrigger();

			if ($trigger_type == null) {
				return $this->redirectRoute('admin_ticketescalations_new', array('type' => 'time.open'));
			}

			$trigger->event_trigger = $trigger_type;
		}

		return $this->render('AdminBundle:TicketTriggers:edit-escalation.html.twig', array(
			'trigger'      => $trigger,
			'term_options' => $this->_getTermOptions(),
		));
	}

	protected function _getTermOptions()
	{
		$term_options = App::getApi('tickets')->getTicketOptions($this->person);
		$term_options['people_term_options']  = array();
		$term_options['people_term_options']  = array();
		$term_options['people_term_options']['organizations']  = $this->container->getDataService('Organization')->getOrganizationNames();
		$term_options['people_term_options']['usergroups']     = $this->container->getDataService('Usergroup')->getUsergroupNames();
		$term_options['email_gateway_addresses'] = $this->em->getRepository('DeskPRO:EmailGatewayAddress')->getOptions();

		if ($this->container->getDataService('Language')->isMultiLang()) {
			$term_options['people_term_options']['languages']  = $this->container->getDataService('Language')->getTitles();
		}

		$term_options['web_hooks']  = $this->container->getDataService('WebHook')->getHookTitles();
		$term_options['api_keys']  = $this->container->getDataService('ApiKey')->getApiKeyTitles();

		$term_options['plugin_actions'] = $this->container->getDataService('TicketTriggerPluginActions')->getSetupObjects();
		foreach ($term_options['plugin_actions'] AS $object) {
			$term_options = $object->alterTermOptionData($term_options);
		}

		return $term_options;
	}

	public function saveTriggerAction($id)
	{
		if ($id) {
			$trigger = $this->em->find('DeskPRO:TicketTrigger', $id);
			if (!$trigger) {
				throw $this->createNotFoundException();
			}
		} else {
			$trigger = new TicketTrigger();
		}

		$trigger->title = $this->in->getString('trigger.title');
		$trigger->event_trigger = $this->in->getString('trigger.event_trigger');
		$trigger->event_trigger_options = $this->in->getCleanValueArray('trigger.event_trigger_options', 'string', 'discard');

		if ($this->in->getString('event_trigger_time')) {
			$time = $this->in->getString('event_trigger_time') . ' ' . $this->in->getString('event_trigger_scale');
			$trigger->setEventTriggerOption('time', $time);
		}

		$term_rules = RuleBuilder::newTermsBuilder();

		$trigger->terms = $term_rules->readForm($this->in->getCleanValueArray('terms', 'raw' , 'discard'));
		$trigger->terms_any = $term_rules->readForm($this->in->getCleanValueArray('terms_any', 'raw' , 'discard'));

		$action_rules = RuleBuilder::newActionsBuilder();
		$trigger->actions = $action_rules->readForm($this->in->getCleanValueArray('actions', 'raw' , 'discard'));

		$this->em->beginTransaction();
		$this->em->persist($trigger);
		$this->em->flush();
		$this->em->commit();

		if ($trigger->getTriggerType() == 'escalation') {
			return $this->redirectRoute('admin_ticketescalations');
		} else {
			if (strpos($trigger->event_trigger, 'update.') === 0) {
				return $this->redirectRoute('admin_tickettriggers', array('list_type' => 'update'));
			} else {
				return $this->redirectRoute('admin_tickettriggers');
			}
		}
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
		if (!$trigger) {
			throw $this->createNotFoundException();
		}

		$type = $trigger->getTriggerType();

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

		if ($type == 'escalation') {
			return $this->redirectRoute('admin_ticketescalations');
		} else {
			return $this->redirectRoute('admin_tickettriggers');
		}
	}
}
