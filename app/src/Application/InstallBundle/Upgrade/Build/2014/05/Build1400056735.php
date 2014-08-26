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

use Application\DeskPRO\Entity\Sla;
use Application\DeskPRO\Tickets\Triggers\Terms\CheckOrgName;
use Application\DeskPRO\Tickets\Triggers\Terms\CheckPriority;
use Application\DeskPRO\Tickets\Triggers\Terms\CheckUserEmail;
use Application\DeskPRO\Tickets\Triggers\Terms\TriggerTermComposite;
use Application\DeskPRO\Tickets\Triggers\TriggerActions;
use Application\DeskPRO\Tickets\Triggers\TriggerTerms;
use Application\InstallBundle\Upgrade\Build\Helper201405\TriggerActionConverter;
use Orb\Util\Arrays;

class Build1400056735 extends AbstractBuild
{
	/**
	 * @var TriggerActionConverter
	 */
	private $action_converter;

	public function run()
	{
		require_once DP_ROOT.'/src/Application/InstallBundle/Upgrade/Build/2014/05/Helper/TriggerActionConverter.php';

		$this->out("Upgrading SLAs");

		$db = $this->container->getDb();

		#------------------------------
		# Init helpers
		#------------------------------

		$gateway_addr_map = $this->getUpgradeData('201404', 'gateway_address_map') ?: array();

		$mappings = array(
			'gateway_address_to_email_account' => $gateway_addr_map
		);

		$this->action_converter = new TriggerActionConverter($mappings);

		#------------------------------
		# Load old data
		#------------------------------

		$slas = $this->getUpgradeData('201404', 'slas') ?: array();

		$old_triggers = array();
		foreach (($this->getUpgradeData('201404', 'ticket_triggers') ?: array()) as $rec) {
			$old_triggers[$rec['id']] = $rec;
		}

		$sla_people = array();
		foreach (($this->getUpgradeData('201404', 'sla_people') ?: array()) as $rec) {
			if (!isset($sla_people[$rec['sla_id']])) $sla_people[$rec['sla_id']] = array();
			$sla_people[$rec['sla_id']][] = $rec['person_id'];
		}

		$sla_orgs = array();
		foreach (($this->getUpgradeData('201404', 'sla_organizations') ?: array()) as $rec) {
			if (!isset($sla_orgs[$rec['sla_id']])) $sla_orgs[$rec['sla_id']] = array();
			$sla_orgs[$rec['sla_id']][] = $rec['organization_id'];
		}

		foreach ($slas as $sla) {
			$sla['@people']          = isset($sla_people[$sla['id']]) ? $sla_people[$sla['id']] : array();
			$sla['@orgs']            = isset($sla_orgs[$sla['id']]) ? $sla_orgs[$sla['id']] : array();
			$sla['@apply_trigger']   = $sla['apply_trigger_id'] && isset($old_triggers[$sla['apply_trigger_id']]) ? $old_triggers[$sla['apply_trigger_id']] : null;
			$sla['@warn_trigger']    = $sla['warning_trigger_id'] && isset($old_triggers[$sla['warning_trigger_id']]) ? $old_triggers[$sla['warning_trigger_id']] : null;
			$sla['@fail_trigger']    = $sla['fail_trigger_id'] && isset($old_triggers[$sla['fail_trigger_id']]) ? $old_triggers[$sla['fail_trigger_id']] : null;

			if ($sla['@apply_trigger']) {
				$sla['@apply_trigger']['terms']     = @unserialize($sla['@apply_trigger']['terms']);
				$sla['@apply_trigger']['terms_any'] = @unserialize($sla['@apply_trigger']['terms_any']);
			}

			if ($sla['@warn_trigger']) {
				$sla['@warn_trigger']['actions'] = unserialize($sla['@warn_trigger']['actions']);
				$sla['@warn_trigger']['event_trigger_options'] = unserialize($sla['@warn_trigger']['event_trigger_options']);
			}
			if ($sla['@fail_trigger']) {
				$sla['@fail_trigger']['actions'] = unserialize($sla['@fail_trigger']['actions']);
				$sla['@fail_trigger']['event_trigger_options'] = unserialize($sla['@fail_trigger']['event_trigger_options']);
			}

			$new_sla = $this->processSla($sla);
			if ($new_sla) {
				$this->out("-- Saved");
				$this->container->getEm()->persist($new_sla);
				$this->container->getEm()->flush();
			} else {
				$this->out("-- Skipped");
				$this->container->getDb()->delete('slas', array('id' => $sla['id']));
			}
		}

		$this->container->getEm()->flush();
	}


	/**
	 * @param array $old_sla
	 * @return Sla
	 */
	private function processSla(array $old_sla)
	{
		/** @var Sla $sla */
		$sla = $this->container->getEm()->find('DeskPRO:Sla', $old_sla['id']);
		if (!$sla) {
			return null;
		}

		$is_incomplete = false;

		#------------------------------
		# Sort out apply type
		#------------------------------

		$sla->apply_terms = new TriggerTerms();

		switch ($old_sla['apply_type']) {
			case 'all':
				$sla->apply_type = 'all';
				break;

			case 'manual':
				$sla->apply_type = 'manual';
				break;

			case 'people_orgs':
				$sla->apply_type = 'auto';

				if ($old_sla['@people']) {
					$ids = Arrays::castToType($old_sla['@people'], 'int');
					$email_addresses = $this->container->getDb()->fetchAllCol("
						SELECT email
						FROM people_emails
						WHERE person_id IN (".implode(',', $ids).")
						GROUP BY person_id
					");
					if ($email_addresses) {
						$set = new TriggerTermComposite();
						$set->add(new CheckUserEmail('is', array('email' => $email_addresses)));
						$sla->apply_terms->addTerm($set);
					}
				} else if ($old_sla['@orgs']) {
					$ids = Arrays::castToType($old_sla['@orgs'], 'int');
					$names = $this->container->getDb()->fetchAllCol("
						SELECT name
						FROM organizations
						WHERE id IN (".implode(',', $ids).")
					");
					if ($names) {
						$set = new TriggerTermComposite();
						$set->add(new CheckOrgName('is', array('name' => $names)));
						$sla->apply_terms->addTerm($set);
					}
				} else {
					$sla->apply_type = 'manual';
				}
				break;

			case 'priority':
				if ($old_sla['apply_priority_id']) {
					$sla->apply_type = 'auto';
					$set = new TriggerTermComposite();
					$set->add(new CheckPriority('is', array('priority_ids' => $old_sla['apply_priority_id'])));
					$sla->apply_terms->addTerm($set);
				} else {
					$sla->apply_type = 'manual';
				}
				break;

			case 'criteria':
				if ($old_sla['@apply_trigger']) {
					$sets = $this->convertTriggerTerms($old_sla['@apply_trigger'], $is_incomplete);
					if ($sets and count($sets)) {
						$sla->apply_terms = $sets;
					}
				} else {
					$sla->apply_type = 'manual';
					$is_incomplete = true;
				}
				break;
		}

		#------------------------------
		# Sort out warn and failure times
		#------------------------------

		if (!empty($old_sla['@warn_trigger']['event_trigger_options'])) {
			list ($time, $unit) = explode(' ', $old_sla['@warn_trigger']['event_trigger_options']['time']);
			$sla->warn_time = $time ?: 1;
			$sla->warn_time_unit = $unit ?: 'hours';
		} else {
			$sla->warn_time = 1;
			$sla->warn_time_unit = 'hours';
		}

		if (!empty($old_sla['@fail_trigger']['event_trigger_options'])) {
			list ($time, $unit) = explode(' ', $old_sla['@fail_trigger']['event_trigger_options']['time']);
			$sla->fail_time = $time ?: 1;
			$sla->fail_time_unit = $unit ?: 'hours';
		} else {
			$sla->fail_time = 1;
			$sla->fail_time_unit = 'hours';
		}

		#------------------------------
		# Sort out warn and fail actions
		#------------------------------

		if (!empty($old_sla['@warn_trigger']['actions'])) {
			$actions = $this->convertTriggerActions($old_sla['@warn_trigger'], $is_incomplete);
			if ($actions) {
				$sla->warn_actions = $actions;
			} else {
				$sla->warn_actions = new TriggerActions();
			}
		} else {
			$sla->warn_actions = new TriggerActions();
		}

		if (!empty($old_sla['@fail_trigger']['actions'])) {
			$actions = $this->convertTriggerActions($old_sla['@fail_trigger'], $is_incomplete);
			if ($actions) {
				$sla->fail_actions = $actions;
			} else {
				$sla->fail_actions = new TriggerActions();
			}
		} else {
			$sla->fail_actions = new TriggerActions();
		}

		#------------------------------
		# Finish
		#------------------------------

		if ($is_incomplete) {
			$sla->title .= ' (REQUIRES REVIEW)';
		}

		return $sla;
	}


	/**
	 * @param array $old_trigger
	 * @param bool $is_incomplete
	 * @return TriggerActions|null
	 */
	private function convertTriggerActions($old_trigger, &$is_incomplete)
	{
		$actions_set = new TriggerActions();

		foreach ($old_trigger['actions'] as $act) {
			$new_act = $this->action_converter->getTriggerAction($act);
			if ($new_act) {
				if (is_array($new_act)) {
					foreach ($new_act as $a) {
						$actions_set->addAction($a);
					}
				} else {
					$actions_set->addAction($new_act);
				}
			} else {
				$this->out("-- Skipping action {$act['type']}");
				if ($act['type'] != 'recalculate_sla_status') {
					$is_incomplete = true;
				}
			}
		}

		if (!count($actions_set)) {
			$this->out("-- empty action set");
			return null;
		}

		return $actions_set;
	}


	/**
	 * @param array $old_trigger
	 * @param bool $is_incomplete
	 * @return TriggerTerms
	 */
	private function convertTriggerTerms($old_trigger, &$is_incomplete)
	{
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

		$terms_all = new TriggerTermComposite();
		if (!empty($old_trigger['terms_any'])) {
			foreach ($old_trigger['terms_any'] as $term) {
				$new_term = $this->term_converter->getTriggerTerm($old_trigger['event_trigger'], $term);
				if ($new_term) {
					$terms_all->add($new_term);
				} else {
					$this->out("-- Skipping all term {$term['type']}");
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
					$this->out("-- Skipping any term {$term['type']}");
					$is_incomplete = true;
				}
			}
		}

		// no any terms, so we can just use the normal all terms
		if (!count($term_sets)) {
			$term_sets->addTerm($terms_all);
		}

		return $term_sets;
	}
}