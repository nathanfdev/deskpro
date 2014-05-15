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
 * @subpackage
 */

namespace Application\InstallBundle\Upgrade\Build;

use Application\DeskPRO\Entity\TicketTrigger;
use Application\DeskPRO\Monolog\NullLogger;
use Application\DeskPRO\Tickets\Triggers\Terms\TriggerTermComposite;
use Application\DeskPRO\Tickets\Triggers\TriggerActions;
use Application\DeskPRO\Tickets\Triggers\TriggerTerms;
use Application\InstallBundle\Data\DefaultData\TriggerData;
use Application\InstallBundle\Upgrade\Build\Helper201404\TriggerActionConverter;
use Application\InstallBundle\Upgrade\Build\Helper201404\TriggerTermConverter;

class Build1398788030 extends AbstractBuild
{
	/**
	 * @var TriggerTermConverter
	 */
	private $term_converter;

	/**
	 * @var TriggerActionConverter
	 */
	private $action_converter;

	public function run()
	{
		require_once DP_ROOT.'/src/Application/InstallBundle/Upgrade/Build/2014/04/Helper/TriggerActionConverter.php';
		require_once DP_ROOT.'/src/Application/InstallBundle/Upgrade/Build/2014/04/Helper/TriggerTermConverter.php';

		$this->out("Upgrading triggers");

		$db = $this->container->getDb();
		$db->executeUpdate('DELETE FROM ticket_triggers');

		#------------------------------
		# Install new default triggers
		#------------------------------

		$this->out("Installing default triggers");
		$trigger_data = new TriggerData($this->container, new NullLogger());
		$trigger_data->runInstall();

		$db->executeUpdate("
			UPDATE ticket_triggers
			SET is_enabled = 0
			WHERE sys_name IN (
				'default_newticket_userautoreply', 'default_newreply_userautoreply',
				'default_newticket_requirevalid'
			)
		");

		$this->out("Processing old triggers ...");

		#------------------------------
		# Init helpers
		#------------------------------

		$gateway_addr_map = $this->container->getDb()->fetchColumn("SELECT data FROM install_data WHERE build = 1396876000 AND name = 'upgrade_mapping_gateway_address_map'");
		if ($gateway_addr_map) {
			$gateway_addr_map = unserialize($gateway_addr_map);
		} else {
			$gateway_addr_map = array();
		}

		$mappings = array(
			'gateway_address_to_email_account' => $gateway_addr_map
		);

		$this->term_converter   = new TriggerTermConverter($mappings);
		$this->action_converter = new TriggerActionConverter($mappings);

		#------------------------------
		# Process old triggers
		#------------------------------

		$old_triggers = $this->container->getDb()->fetchColumn("SELECT data FROM install_data WHERE build = 1396876000 AND name = 'upgrade_data_ticket_triggers'");
		if ($old_triggers) {
			$old_triggers = unserialize($old_triggers);
		} else {
			$old_triggers = array();
		}

		foreach ($old_triggers as $trigger) {
			$this->out("Processing #{$trigger['id']} ...");
			$new_trigger = $this->processTrigger($trigger);
			if ($new_trigger) {
				$this->container->getEm()->persist($new_trigger);
				$this->out("-- Saved");
			} else {
				$this->out("-- Skipped");
			}
		}

		$this->container->getEm()->flush();
	}


	/**
	 * @param array $old_trigger
	 * @return TicketTrigger|null
	 */
	private function processTrigger(array $old_trigger)
	{
		// We dont do escalations or slas in this task
		if (strpos($old_trigger['event_trigger'], 'time') !== false || strpos($old_trigger['event_trigger'], 'sla') !== false) {
			return null;
		}

		// Default triggers just turn on depending on the status of the old default triggers
		if ($old_trigger['sys_name']) {
			if (!$old_trigger['is_enabled']) {
				return null;
			}
			switch ($old_trigger['sys_name']) {
				case 'email_validation.email':
					$this->container->getDb()->update('ticket_triggers', array('is_enabled' => true), array('sys_name' => 'default_newticket_requirevalid'));
					break;
				case 'newticket_confirm.email_user':
					$this->container->getDb()->update('ticket_triggers', array('is_enabled' => true), array('sys_name' => 'default_newticket_userautoreply'));
					break;
				case 'newticket_confirm.web_user':
					$this->container->getDb()->update('ticket_triggers', array('is_enabled' => true), array('sys_name' => 'default_newticket_userautoreply'));
					break;
				case 'email_validation.web':
					$this->container->getDb()->update('ticket_triggers', array('is_enabled' => true), array('sys_name' => 'default_newticket_requirevalid'));
					break;
				case 'email_validation.widget':
					$this->container->getDb()->update('ticket_triggers', array('is_enabled' => true), array('sys_name' => 'default_newticket_requirevalid'));
					break;
				case 'response.reply_confirm':
					$this->container->getDb()->update('ticket_triggers', array('is_enabled' => true), array('sys_name' => 'default_newreply_userautoreply'));
					break;
			}
			return null;
		}

		$old_trigger['terms']     = @unserialize($old_trigger['terms']) ?: array();
		$old_trigger['terms_any'] = @unserialize($old_trigger['terms_any']) ?: array();
		$old_trigger['actions']   = @unserialize($old_trigger['actions']) ?: array();

		$trigger = new TicketTrigger();

		$replytype = false;
		foreach (array($old_trigger['terms'], $old_trigger['terms_any']) as $terms) {
			foreach ($terms as $t) {
				if ($t['type'] == 'new_reply_user' || $t['type'] == 'new_reply_agent' || $t['type'] == 'new_reply_note') {
					$replytype = true;
					break 2;
				}
			}
		}

		switch ($old_trigger['event_trigger']) {
			case 'new.email.user':
				$trigger->event_trigger = 'newticket';
				$trigger->by_user_mode = array('email');
				break;
			case 'new.web.user':
				$trigger->event_trigger = 'newticket';
				$trigger->by_user_mode = array('portal', 'widget', 'form');
				break;
			case 'new.email.agent':
				$trigger->event_trigger = 'newticket';
				$trigger->by_agent_mode = array('email');
				break;
			case 'new.web.agent.portal':
				$trigger->event_trigger = 'newticket';
				$trigger->by_agent_mode = array('web');
				break;
			case 'new.web.api':
				$trigger->event_trigger = 'newticket';
				$trigger->by_agent_mode = array('api');
				$trigger->by_user_mode  = array('api');
				break;
			case 'update.agent':
				$trigger->event_trigger = $replytype ? 'newreply' : 'update';
				$trigger->by_agent_mode = array('web', 'email');
				break;
			case 'update.user':
				$trigger->event_trigger = $replytype ? 'newreply' : 'update';
				$trigger->by_user_mode = array('portal', 'email', 'api');
				break;
			case 'update.api':
				$trigger->event_trigger = $replytype ? 'newreply' : 'update';
				$trigger->by_agent_mode = array('api');
				$trigger->by_user_mode  = array('api');
				break;
		}

		#------------------------------
		# Update terms
		#------------------------------

		$is_incomplete = false;

		/*
		 * Before we had "When ALL of these match: <set> AND ANY of these match: <set>"
		 * That is: (a and b and c) AND (x or y or z)
		 *
		 * Now we have: "When <set> or <set>"
		 * That is: (a and b and c) OR (x and y and z)
		 *
		 * This means that if there's an 'any' set, we have to combine it with multiple
		 * permutations of the old 'all' set:
		 * (a and b and c and x) or (a and b and c and y) or (a and b and c and z)
		 */

		$term_sets = new TriggerTerms();
		$term_sets->setOperator(TriggerTermComposite::OP_OR);

		$terms_all = new TriggerTermComposite();
		if (!empty($old_trigger['terms_any'])) {
			foreach ($old_trigger['terms_any'] as $term) {
				$new_term = $this->term_converter->getTriggerTerm($old_trigger['event_trigger'], $term);
				if ($new_term) {
					$terms_all->add($new_term);
				} else {
					$is_incomplete = true;
				}
			}
		}

		if (!empty($old_trigger['terms_any'])) {
			foreach ($old_trigger['terms_any'] as $term) {
				$new_term = $this->term_converter->getTriggerTerm($old_trigger['event_trigger'], $term);
				if ($new_term) {
					$set = new TriggerTermComposite();
					$set->setOperator(TriggerTermComposite::OP_AND);
					$set->add($new_term);

					if (count($terms_all)) {
						foreach ($terms_all->getAll() as $t) {
							$set->add($t);
						}
					}

					$term_sets->addTerm($set);
				} else {
					$is_incomplete = true;
				}
			}
		}

		// no any terms, so we can just use the normal all terms
		if (!count($term_sets)) {
			$term_sets->addTerm($terms_all);
		}

		#------------------------------
		# Update actions
		#------------------------------

		$actions_set = new TriggerActions();

		foreach ($old_trigger['actions'] as $act) {
			$new_act = $this->action_converter->getTriggerAction($act);
			if ($new_act) {
				$actions_set->addAction($new_act);
			} else {
				$is_incomplete = true;
			}
		}

		if (!count($actions_set)) {
			return null;
		}

		#------------------------------
		# Create trigger object
		#------------------------------

		$trigger->title      = $old_trigger['title'] ?: 'Trigger ' . $old_trigger['id'];
		if ($is_incomplete) {
			$trigger->title .= ' (REQUIRES REVIEW)';
		}
		$trigger->is_enabled = (bool)$old_trigger['is_enabled'] && !$is_incomplete;
		$trigger->run_order  = (int)$old_trigger['run_order'];
		$trigger->terms      = $term_sets;
		$trigger->actions    = $actions_set;

		return $trigger;
	}
}