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
 * @subpackage ApiBundle
 */

namespace Application\ApiBundle\Controller;

use Application\ApiBundle\PermissionStrategy\AdminManagePermission;
use Application\DeskPRO\Entity\TicketTrigger;
use Application\DeskPRO\Tickets\Triggers\Edit\SpecialTriggerEdit;
use Application\DeskPRO\Tickets\Triggers\Terms\TriggerTermComposite;
use Application\DeskPRO\Tickets\Triggers\TriggerActions;
use Application\DeskPRO\Tickets\Triggers\TriggerTerms;

class TicketTriggersController extends AbstractController implements ProtectedControllerInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function getPermissionStrategy()
	{
		return new AdminManagePermission();
	}


	####################################################################################################################
	# list
	####################################################################################################################

	public function listAction($type = null)
	{
		if (!$type || $type == 'all') {
			$type = null;
		}

		$triggers = $this->em->getRepository('DeskPRO:TicketTrigger')->getTriggers($type);

		$data = $this->getApiData($triggers);
		$res = array();
		$res['triggers'] = $data;

		if ($type == 'all' || $type == 'newticket' || $type == 'update') {
			$dep_triggers_enabled = false;
			$acc_triggers_enabled = false;
			foreach ($triggers as $t) {
				if ($t->department && $t->is_enabled) {
					$dep_triggers_enabled = true;
				}
				if ($t->email_account && $t->is_enabled) {
					$acc_triggers_enabled = true;
				}
			}

			$res['department_triggers_enabled']   = $dep_triggers_enabled;
			$res['emailaccount_triggers_enabled'] = $acc_triggers_enabled;
		}

		return $this->createApiResponse($res);
	}

	####################################################################################################################
	# get
	####################################################################################################################

	public function getAction($id, $special_type = null)
	{
		switch ($special_type) {
			case 'departments':
			case 'departments_changed':
				$dep = $this->container->getTicketDepartments()->getById($id);
				if (!$dep) {
					throw $this->createNotFoundException();
				}

				$event = $special_type == 'departments' ? TicketTrigger::EVENT_TYPE_NEWTICKET : TicketTrigger::EVENT_TYPE_UPDATE;
				$trigger = $this->em->getRepository('DeskPRO:TicketTrigger')->findOneBy(array('department' => $dep, 'event_trigger' => $event));

				if (!$trigger) {
					$trigger = new TicketTrigger();
					$edit = SpecialTriggerEdit::createWithDepartment($dep, $event);
					$edit->applyToTrigger($trigger);
					$this->em->persist($trigger);
					$this->em->flush($trigger);
				}
				break;

			case 'email_accounts':
				if (!$this->container->getEmailAccountManager()->hasAcccount($id)) {
					throw $this->createNotFoundException();
				}

				$acc = $this->container->getEmailAccountManager()->getAccount($id);

				$trigger = $this->em->getRepository('DeskPRO:TicketTrigger')->findOneBy(array('email_account' => $acc));
				if (!$trigger) {
					$trigger = new TicketTrigger();
					$edit = SpecialTriggerEdit::createWithEmailAccount($acc);
					$edit->applyToTrigger($trigger);
					$this->em->persist($trigger);
					$this->em->flush($trigger);
				}
				break;

			default:
				$trigger = $this->em->find('DeskPRO:TicketTrigger', $id);
		}

		if (!$trigger) {
			throw $this->createNotFoundException();
		}

		$data = $this->getApiData($trigger);

		return $this->createApiResponse(array(
			'trigger' => $data
		));
	}

	####################################################################################################################
	# save
	####################################################################################################################

	public function saveAction($id, $special_type = null)
	{
		if ($id) {
			switch ($special_type) {
				case 'departments':
				case 'departments_changed':
					$dep = $this->container->getTicketDepartments()->getById($id);
					if (!$dep) {
						throw $this->createNotFoundException();
					}

					$event = $special_type == 'departments' ? TicketTrigger::EVENT_TYPE_NEWTICKET : TicketTrigger::EVENT_TYPE_UPDATE;

					$trigger = $this->em->getRepository('DeskPRO:TicketTrigger')->findOneBy(array('department' => $dep, 'event_trigger' => $event));
					if (!$trigger) {
						$trigger = new TicketTrigger();
						$edit = SpecialTriggerEdit::createWithDepartment($dep, $event);
						$edit->applyToTrigger($trigger);
						$this->em->persist($trigger);
						$this->em->flush($trigger);
					}
					break;

				case 'email_accounts':
					if (!$this->container->getEmailAccountManager()->hasAcccount($id)) {
						throw $this->createNotFoundException();
					}

					$acc = $this->container->getEmailAccountManager()->getAccount($id);

					$trigger = $this->em->getRepository('DeskPRO:TicketTrigger')->findOneBy(array('email_account' => $acc));
					if (!$trigger) {
						$trigger = new TicketTrigger();
						$edit = SpecialTriggerEdit::createWithEmailAccount($acc);
						$edit->applyToTrigger($trigger);
						$this->em->persist($trigger);
						$this->em->flush($trigger);
					}
					break;

				default:
					$trigger = $this->em->find('DeskPRO:TicketTrigger', $id);

					if ($trigger->department) {
						if ($trigger->event_trigger == 'newticket') {
							$special_type = 'departments';
						} else {
							$special_type = 'departments_changed';
						}
					} else if ($trigger->email_account) {
						$special_type = 'email_accounts';
					}
			}
		} else {
			if ($special_type) {
				throw $this->createNotFoundException();
			}
			$trigger = new TicketTrigger();
		}

		$is_new = !((bool)$trigger->id);

		$trigger->title         = $this->in->getString('title');
		$trigger->event_trigger = $this->in->getString('event_trigger');

		if ($trigger->event_trigger == TicketTrigger::EVENT_TYPE_UPDATE) {
			if ($this->in->getBool('flags.run_newreply')) {
				$trigger->addEventFlag(TicketTrigger::EVENT_FLAG_RUN_NEWREPLY);
			} else {
				$trigger->removeEventFlag(TicketTrigger::EVENT_FLAG_RUN_NEWREPLY);
			}
		}

		$trigger->setByAgentMode($this->in->getArrayOfStrings('by_agent_mode'));
		$trigger->setByUserMode($this->in->getArrayOfStrings('by_user_mode'));

		$error_criteria = array();
		$error_actions  = array();
		$error_messages = array();

		$terms = new TriggerTerms();
		foreach ($this->in->getArrayValue('criteria_sets') as $set) {
			if ($set) {
				$composite = new TriggerTermComposite(array(), TriggerTermComposite::OP_AND);
				foreach ($set as $ti) {
					try {
						$t = $terms->getTermFromArray($ti);
						$composite->add($t);
					} catch (\Exception $e) {
						$error_criteria[] = $ti['type'];
						$error_messages[] = $e->getMessage();
					}
				}
				if ($composite->count()) {
					$terms->addTerm($composite);
				}
			}
		}

		$action_defs = $this->container->getTicketActionDefManager();

		$actions = new TriggerActions();
		foreach ($this->in->getArrayValue('actions') as $act) {
			if ($act) {
				$type = $act['type'];

				if (isset($act['DP_DISABLED'])) {
					continue;
				}

				if ($action_defs->hasNamedDef($type)) {
					$act['type_class'] = $action_defs->getNamedDef($type)->getDef()->getTriggerActionClass();
					if (!$act['type_class']) {
						continue;
					}
				}

				try {
					$actions->addActionFromArray($act);
				} catch (\Exception $e) {
					$error_actions[] = $act['type'];
					$error_messages[] = $e->getMessage();
				}
			}
		}

		$ret = array();

		if ($error_criteria || $error_actions) {
			$ret['errors'] = array();
			if ($error_criteria) {
				$ret['errors']['criteria'] = $error_criteria;
			}
			if ($error_actions) {
				$ret['errors']['actions'] = $error_actions;
			}
			$ret['errors']['error_messages'] = $error_messages;

			return $this->createApiErrorInfoResponse('invalid', 'One or more criteria or actions are invalid', $ret['errors']);
		}

		$trigger->terms = $terms;
		$trigger->actions = $actions;

		if ($trigger->department) {
			$event = $special_type == 'departments' ? TicketTrigger::EVENT_TYPE_NEWTICKET : TicketTrigger::EVENT_TYPE_UPDATE;
			$edit = SpecialTriggerEdit::createWithDepartment($trigger->department, $event);
			$edit->applyToTrigger($trigger);
		} else if ($trigger->email_account) {
			$edit = SpecialTriggerEdit::createWithEmailAccount($trigger->email_account);
			$edit->applyToTrigger($trigger);
		}

		if ($is_new) {
			$ro = 0;
			if ($trigger->department) {
				$ro = $this->db->fetchColumn("SELECT run_order FROM ticket_triggers WHERE department_id IS NOT NULL LIMIT 1");
			} else if ($trigger->email_account) {
				$ro = $this->db->fetchColumn("SELECT run_order FROM ticket_triggers WHERE email_account_id IS NOT NULL LIMIT 1");
			}

			if (!$ro) {
				$ro = $this->db->fetchColumn("SELECT run_order FROM ticket_triggers ORDER BY run_order DESC");
			}

			$trigger->run_order = $ro + 10;
		}

		$this->em->persist($trigger);
		$this->em->flush();

		// Sanity check
		if ($trigger->department) {
			$this->db->executeUpdate("
				DELETE FROM ticket_triggers
				WHERE department_id = ? AND event_trigger = ? AND id != ?
			", array($trigger->department->id, $trigger->event_trigger, $trigger->id));
		}
		if ($trigger->email_account) {
			$this->db->executeUpdate("
				DELETE FROM ticket_triggers
				WHERE email_account_id = ?
				AND id != ?
			", array($trigger->email_account->id, $trigger->id));
		}

		$ret['trigger_id'] = $trigger->id;
		return $this->createSuccessResponse($ret);
	}

	####################################################################################################################
	# delete
	####################################################################################################################

	public function deleteAction($id)
	{
		$trigger = $this->em->find('DeskPRO:TicketTrigger', $id);
		if (!$trigger) {
			throw $this->createNotFoundException();
		}

		$old_id = $trigger->id;

		$this->em->remove($trigger);
		$this->em->flush();

		return $this->createSuccessResponse(array('old_id' => $old_id));
	}

	####################################################################################################################
	# toggle-trigger
	####################################################################################################################

	public function toggleTriggerAction($id, $is_enabled)
	{
		$trigger = $this->em->find('DeskPRO:TicketTrigger', $id);
		if (!$trigger) {
			throw $this->createNotFoundException();
		}

		$trigger->is_enabled = $is_enabled;
		$this->em->persist($trigger);
		$this->em->flush();

		return $this->createSuccessResponse();
	}

	####################################################################################################################
	# toggle-trigger-group
	####################################################################################################################

	public function toggleTriggerGroupAction($special_type, $is_enabled)
	{
		$is_enabled = (int)$is_enabled;

		switch ($special_type) {
			case 'departments':
				$this->db->executeUpdate("
					UPDATE ticket_triggers
					SET is_enabled = ?
					WHERE department_id IS NOT NULL AND event_trigger = 'newticket'
				", array($is_enabled));
				break;
			case 'departments_changed':
				$this->db->executeUpdate("
					UPDATE ticket_triggers
					SET is_enabled = ?
					WHERE department_id IS NOT NULL AND event_trigger = 'update'
				", array($is_enabled));
				break;
			case 'email_accounts':
				$this->db->executeUpdate("
					UPDATE ticket_triggers
					SET is_enabled = ?
					WHERE email_account_id IS NOT NULL AND event_trigger = 'newticket'
				", array($is_enabled));
				break;
			default:
				throw $this->createNotFoundException();
		}

		return $this->createSuccessResponse();
	}

	####################################################################################################################
	# save-run-order
	####################################################################################################################

	public function saveRunOrderAction()
	{
		$run_orders = $this->in->getCleanValueArray('run_orders', 'string', 'discard');
		$this->em->getRepository('DeskPRO:TicketTrigger')->updateRunOrders($run_orders);

		return $this->createSuccessResponse();
	}

	####################################################################################################################
	# get-custom-actions
	####################################################################################################################

	public function getCustomActionsAction()
	{
		$manager = $this->container->getTicketActionDefManager();

		$actions = array();
		foreach ($manager->getAllDefs() as $d) {
			$actions[] = $d->toApiData();
		}

		return $this->createJsonResponse(array('action_defs' => $actions));
	}
}