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
 * @category Translate
 */

namespace Application\DeskPRO\Translate;

use Application\DeskPRO\App;
use Orb\Util\Arrays;
use Orb\Util\Util;

/**
 * Summarizes terms
 */
class TermSummary
{
	const OP_IS          = 'is';
	const OP_NOT         = 'not';
	const OP_LT          = 'lt';
	const OP_GT          = 'gt';
	const OP_LTE         = 'lte';
	const OP_GTE         = 'gte';
	const OP_BETWEEN     = 'between';
	const OP_CONTAINS    = 'contains';
	const OP_NOTCONTAINS = 'notcontains';
	const OP_NOOP        = null;

	const OP_CHANGED            = 'changed';
	const OP_CHANGED_TO         = 'changed_to';
	const OP_CHANGED_FROM       = 'changed_from';
	const OP_NOT_CHANGED_TO     = 'not_changed_to';
	const OP_NOT_CHANGED_FROM   = 'not_changed_from';

	const OP_CHANGED_TO_GTE         = 'changed_to_gte';
	const OP_CHANGED_TO_LTE         = 'changed_to_lte';
	const OP_CHANGED_FROM_GTE       = 'changed_from_gte';
	const OP_CHANGED_FROM_LTE       = 'changed_from_lte';
	const OP_NOT_CHANGED_TO_GTE     = 'not_changed_to_gte';
	const OP_NOT_CHANGED_TO_LTE     = 'not_changed_to_let';
	const OP_NOT_CHANGED_FROM_GTE   = 'not_changed_from_gte';
	const OP_NOT_CHANGED_FROM_LTE   = 'not_changed_from_lte';

	public function getSummary($term, $op, $choice)
	{
		$tr = App::getTranslator();
		$summary = false;

		$term_id = null;

		$m = null;
		if (preg_match('#^(.*?)\[(.*?)\]$#', $term, $m)) {
			$term = $m[1];
			$term_id = $m[2];
		}

		switch ($term) {
			case 'id':
				$summary = $this->_rangeSummary($tr->phrase('global.id'), $op, $choice);
				break;

			case 'text':
				if (is_array($choice)) {
					$choice = array_pop($choice);
				}
				$summary = "Content matches: " . $choice;
				break;

			case 'department':
				$summary = $this->_choiceSummary($tr->phrase('global.department'), $op, $choice, function($choice) {
					$titles = App::getEntityRepository('DeskPRO:Department')->getDepartmentNames((array)$choice);
					return $titles;
				});
				break;

			case 'ticket_deleted':
				$summary = $tr->phrase('agent.tickets.ticket_is_deleted');
				break;

			case 'ticket_category':
				$summary = $this->_choiceSummary($tr->phrase('global.category'), $op, $choice, function($choice) {
					$titles = App::getEntityRepository('DeskPRO:TicketCategory')->getCategoryNames((array)$choice);
					return $titles;
				});
				break;

			case 'product':
				$summary = $this->_choiceSummary($tr->phrase('global.product'), $op, $choice, function($choice) {
					$titles = App::getEntityRepository('DeskPRO:Product')->getProductNames((array)$choice);
					return $titles;
				});
				break;

			case 'ticket_priority':
				$summary = $this->_choiceSummary($tr->phrase('global.priority'), $op, $choice, function($choice) {
					$titles = App::getEntityRepository('DeskPRO:TicketPriority')->getPriorityNames((array)$choice);
					return $titles;
				});
				break;

			case 'ticket_urgency':
				$summary = $this->_rangeSummary($tr->phrase('global.urgency'), $op, $choice);
				break;

			case 'date_created':
				$summary = $this->_dateRangeSummary($tr->phrase('global.date_created'), $op, $choice);
				break;

			case 'date_resolved':
				$summary = $this->_dateRangeSummary($tr->phrase('agent.tickets.date_resolved'), $op, $choice);
				break;

			case 'date_closed':
				$summary = $this->_dateRangeSummary($tr->phrase('agent.tickets.date_closed'), $op, $choice);
				break;

			case 'date_last_user_reply':
				$summary = $this->_dateRangeSummary($tr->phrase('agent.tickets.date_last_user_reply'), $op, $choice);
				break;

			case 'date_last_agent_reply':
				$summary = $this->_dateRangeSummary($tr->phrase('agent.tickets.date_last_agent_reply'), $op, $choice);
				break;

			case 'ticket_workflow':
				$summary = $this->_choiceSummary($tr->phrase('global.workflow'), $op, $choice, function($choice) {
					$titles = App::getEntityRepository('DeskPRO:TicketWorkflow')->getWorkflowNames((array)$choice);
					return $titles;
				});
				break;

			case 'language':
				$summary = $this->_choiceSummary($tr->phrase('global.language'), $op, $choice, function($choice) {
					$titles = App::getEntityRepository('DeskPRO:Language')->getTitles((array)$choice);
					return $titles;
				});
				break;

			case 'agent':
				$info = $this->_normalizeAgentChoice($choice);
				$unassigned = $info['unassigned'];
				$agent_ids = $info['agent_ids'];
				$not_id = $info['not_id'];

				if ($unassigned) {
					$summary = $this->_choiceSummary($tr->phrase('global.agent'), $op, $tr->phrase('global.unassigned'));
				} else {
					if ($agent_ids) {
						$summary = $this->_choiceSummary($tr->phrase('global.agent'), $op, $agent_ids, function($choice) {
							$titles = App::getEntityRepository('DeskPRO:Person')->getAgentNames((array)$choice);
							return $titles;
						});
					}

					if ($not_id) {
						$summary = $tr->phrase('agent.agent_is_not_me');
					}
				}
				break;

			case 'agent_team':
				$info = $this->_normalizeAgentTeamChoice($choice);
				$team_ids = $info['team_ids'];
				$not_ids = $info['not_ids'];
				$no_team = $info['no_team'];

				if ($no_team) {
					$summary = $this->_choiceSummary($tr->phrase('global.agent_team'), $op, $tr->phrase('global.unassigned'));

				} else {
					if ($team_ids) {
						$summary = $this->_choiceSummary($tr->phrase('global.agent_team'), $op, $team_ids, function($choice) {
							$titles = App::getEntityRepository('DeskPRO:AgentTeam')->getTeamNames((array)$choice);
							return $titles;
						});
					}

					if ($not_ids) {
						$summary = $this->_choiceSummary($tr->phrase('global.agent_team'), 'not', $not_ids, function($choice) {
							$titles = App::getEntityRepository('DeskPRO:AgentTeam')->getTeamNames((array)$choice);
							return $titles;
						});
					}
				}
				break;

			case 'ticket_status':
				foreach ((array)$choice as $c) {
					if (strpos($c, '.') !== false) {
						$choice_str[] = $tr->phrase('agent.tickets.hidden_status_' . $c);
					} else {
						$choice_str[] = $tr->phrase('agent.tickets.status_' . $c);
					}
				}

				$choice_str = implode(' or ', $choice_str);
				$summary = 'Status is ' . $choice_str;
				break;

			case 'ticket_status_hidden':
				$choice_str = array();
				foreach ((array)$choice as $c) {
					$choice_str[] = $tr->phrase('agent.tickets.hidden_status_' . $c);
				}
				$choice_str = implode(', ', $choice_str);
				break;

			case 'ticket_hold':
				$summary = $tr->phrase('agent.is_not_x', array('field' => 'on hold'));
				break;

			case 'organization':
				$summary = $this->_choiceSummary("Organization", $op, $choice, function($choice) {
					$titles = App::getEntityRepository('DeskPRO:Organization')->getOrganizationNames((array)$choice);
					return $titles;
				});
				break;

			case 'usergroup':
				$summary = $this->_choiceSummary("Usergroup", $op, $choice, function($choice) {
					$titles = App::getEntityRepository('DeskPRO:Usergroup')->getUsergroupNames((array)$choice);
					return $titles;
				});
				break;

			case 'email':
				$this->summary[] = "Email is " . $choice;
				break;

			case 'email_domain':
				$this->summary[] = "Email domain is " . $choice;
				break;

			case 'name':
				$this->summary[] = "Name is " . $choice;
				break;

			case 'ticket_participant':
				$choice_info = $this->_normalizeAgentChoice($choice);
				if (!empty($choice_info['agent_ids'])) {
					$choice = $choice_info['agent_ids'];
					$summary = $this->_choiceSummary($tr->phrase('global.followers'), $op, $choice, function($choice) {
						$titles = App::getEntityRepository('DeskPRO:Person')->getAgentNames((array)$choice);
						return $titles;
					}, true);
				}
				break;

			case 'ticket_subject':
				if ($op == self::OP_IS) {
					$summary = $tr->phrase('agent.x_is_y', array('field' => $tr->phrase('global.subject'), 'value' => $choice['subject']));
				} else {
					$summary = $tr->phrase('agent.x_is_not_y', array('field' => $tr->phrase('global.subject'), 'value' => $choice['subject']));
				}
				break;

			case 'flagged':
				$color = $choice;
				if ($color == 'any') {
					$summary = "Flagged";
				} else {
					$summary = "Flagged with color {$color}";
				}
				break;

			case 'label':
				$choices_in = array();
				if (is_array($choice)) {
					foreach ((array)$choice as $c) {
						$choices_in[] = $db->quote($c);
					}
					$choices_in = implode(',', $choices_in);
				}

				$summary = $this->_choiceSummary($tr->phrase('global.label'), $op, $choice);
				break;

			case 'person_field':
				$field = App::getEntityRepository('DeskPRO:CustomDefPerson')->find($term_id);
				if (!$field) break;

				$search_type = $field->getHandler()->getSearchType();

				switch ($search_type) {
					case 'input':
					case 'value':

						if ($op == self::OP_IS) {
							$summary = $tr->phrase('agent.x_is_y', array('field' => $field['title'], 'value' => $choice));
						} else {
							$summary = $tr->phrase('agent.x_is_not_y', array('field' => $field['title'], 'value' => $choice));
						}

						break;

					case 'id':
						if ($op == self::OP_IS OR $op== self::OP_CONTAINS) {
							$summary = $tr->phrase('agent.x_is_y', array('field' => $field['title'], 'value' => $choice_str));
						} else {
							$summary = $tr->phrase('agent.x_is_not_y', array('field' => $field['title'], 'value' => $choice_str));
						}
						break;
				}
				break; // end TERM_PERSON_FIELD

			case 'ticket_field':
				$field = App::getEntityRepository('DeskPRO:CustomDefTicket')->find($term_id);
				if (!$field) break;

				$search_type = $field->getHandler()->getSearchType();

				switch ($search_type) {
					case 'input':
					case 'value':

						if ($op == self::OP_IS) {
							$summary = $tr->phrase('agent.x_is_y', array('field' => $field['title'], 'value' => $choice));
						} else {
							$summary = $tr->phrase('agent.x_is_not_y', array('field' => $field['title'], 'value' => $choice));
						}

						break;

					case 'id':
						if ($op == self::OP_IS OR $op== self::OP_CONTAINS) {
							$summary = $tr->phrase('agent.x_is_y', array('field' => $field['title'], 'value' => $choice_str));
						} else {
							$summary = $tr->phrase('agent.x_is_not_y', array('field' => $field['title'], 'value' => $choice_str));
						}
						break;
				}
				break; // end break TERM_TICKET_FIELD

			case 'user_waiting':
				$summary = "User waiting " . \Orb\Util\Dates::secsToReadable($choice);
				break;

			case 'agent_waiting':
				$summary = "Agent waiting " . \Orb\Util\Dates::secsToReadable($choice);
				break;

			case 'total_user_waiting':
				$summary = "Total user waiting time is " . \Orb\Util\Dates::secsToReadable($choice);
				break;

			case 'ticket_creation_system':
				$vals = array();

				foreach ((array)$choice as $c) {
					$vals[] = $tr->phrase('agent.tickets.creation_system_' . str_replace('.', '_', $c));
				}

				$vals = implode(', ', $vals);

				$summary = $tr->phrase('agent.x_is_y', array(
					'field' => $tr->phrase('agent.tickets.creation_system'),
					'value' => $vals
				));
				break;

			case 'email_gateway_address':
				$summary = $this->_choiceSummary($tr->phrase('agent.tickets.sent_to_gateway_address'), $op, $choice, function($choice) {
					$titles = App::getEntityRepository('DeskPRO:EmailGatewayAddress')->getOptions((array)$choice);
					return $titles;
				});
				break;

			case 'recieving_gateway':
				$summary = $this->_choiceSummary($tr->phrase('agent.tickets.receiving_gateway'), $op, $choice, function($choice) {
					$titles = App::getEntityRepository('DeskPRO:EmailGateway')->getGatewayNames((array)$choice);
					return $titles;
				});
				break;

			case 'robot_email':
				$summary = 'Email sent from a robot (such as an auto-reply)';
				break;

            case 'time_created':
                $summary = "Time created $op {$choice['hour1']}:{$choice['minute1']}:00";
                break;

            case 'time_last_user_reply':
                $summary = "'Time of last user reply $op {$choice['hour1']}:{$choice['minute1']}:00";
                break;

            case 'day_created':
                $summary = "Day created $op in ".implode(', ', $choice['days']);
                break;

            case 'day_last_user_reply':
                $summary = "Day of last user reply $op in ".implode(', ', $choice['days']);
                break;

			case 'is_new_user':
				$summary = "Is a new user";
				break;

			case 'is_not_new_user':
				$summary = "Is not a new user";
				break;

			case 'gateway_account':
				$names = App::getOrm()->getRepository('DeskPRO:EmailGateway')->getGatewayNames((array)$choice['gateway_address']);
				$summary = "Gateway account is " . implode($names, ' or ');
				break;
		}

		return $summary;
	}

	/**
	 * Get a summary string for a term
	 *
	 * @param  $field
	 * @param  $op
	 * @param  $choice
	 * @return string
	 */
	protected function _rangeSummary($field, $op, $choice)
	{
		$summary = '';

		$choice = (array)$choice;
		$choice = array_values($choice);

		$range1 = !empty($choice[0]) ? $choice[0] : null;
		$range2 = !empty($choice[1]) ? $choice[1] : null;

		// There should always be at least one
		if ($range1 === null AND $range2 === null) {
			return '';
		}

		// Normalize operations
		if ($op == self::OP_LT) $op = self::OP_LTE;
		if ($op == self::OP_GT) $op = self::OP_GTE;

		if ($op == self::OP_BETWEEN && ($range1 === null or $range2 === null)) {
			if ($range1) {
				$op = self::OP_GTE;
			} else {
				$op = self::OP_LTE;
			}
		}

		if ($op == self::OP_BETWEEN) {
			$summary = App::getTranslator()->phrase('agent.x_is_between_y_and_z', array(
				'field' => $field,
				'value1' => $range1,
				'value2' => $range2
			));
		} elseif ($op == self::OP_GTE) {
			$summary = App::getTranslator()->phrase('agent.x_is_greater_than_y', array(
				'field' => $field,
				'value' => $range1,
			));
		} else {
			$summary = App::getTranslator()->phrase('agent.x_is_less_than_y', array(
				'field' => $field,
				'value' => $range1,
			));
		}

		return $summary;
	}


	/**
	 * Get summary of the range summary
	 *
	 * @param $field
	 * @param $op
	 * @param $choice
	 * @return string
	 */
	public function _dateRangeSummary($field, $op, $choice)
	{
		$summary = '';

		$choice = (array)$choice;

		$date1 = null;
		if (!empty($choice['date1'])) {
			$date1 = $choice['date1'];
		} else if (!empty($choice['date1_relative']) AND !empty($choice['date1_relative_type'])) {
			return App::getTranslator()->phrase('agent.x_before_y', array(
				'field' => $field,
				'value' => (int)$choice['date1_relative'] . " {$choice['date1_relative_type']} ago"
			));
		} else if (!empty($choice[0])) {
			$date1 = $choice[0];
		}

		$date2 = null;
		if (!empty($choice['date2'])) {
			$date2 = $choice['date2'];
		} else if (!empty($choice['date2_relative']) AND !empty($choice['date2_relative_type'])) {
			return App::getTranslator()->phrase('agent.x_before_y', array(
				'field' => $field,
				'value' => (int)$choice['date2_relative'] . " {$choice['date2_relative_type']} ago"
			));
		} else if (!empty($choice[1])) {
			$date2 = $choice[1];
		}

		if ($date1 AND !($date1 instanceof \DateTime)) {
			$date1 = new \DateTime("@{$date1}");
		}
		if ($date2 AND !($date2 instanceof \DateTime)) {
			$date2 = new \DateTime("@{$date2}");
		}

		// There should always be at least one date
		if ($date1 === null AND $date2 === null) {
			return '';
		}

		// Normalize operations
		if ($op == self::OP_LT) $op = self::OP_LTE;
		if ($op == self::OP_GT) $op = self::OP_GTE;

		if ($op == self::OP_BETWEEN && ($date1 === null or $date2 === null)) {
			if ($date1) {
				$op = self::OP_GTE;
			} else {
				$op = self::OP_LTE;
			}
		}

		if ($op == self::OP_BETWEEN) {
			$summary = App::getTranslator()->phrase('agent.x_is_between_y_and_z', array(
				'field' => $field,
				'value1' => $date1->format('M j, Y'),
				'value2' => $date2->format('M j, Y')
			));
		} elseif ($op == self::OP_GTE) {
			$summary = App::getTranslator()->phrase('agent.x_after_y', array(
				'field' => $field,
				'value' => $date1->format('M j, Y'),
			));
		} else {
			$summary = App::getTranslator()->phrase('agent.x_before_y', array(
				'field' => $field,
				'value' => $date1->format('M j, Y'),
			));
		}

		return $summary;
	}

	/**
	 * @param  $field
	 * @param  $op
	 * @param  $choice
	 * @param bool $is_id
	 * @return string
	 */
	protected function _choiceSummary($field, $op, $choice, $title_callback = null, $always_choice = false)
	{
		$summary = '';

		if (!$choice) {
			return '';
		}

		if (is_array($choice) AND count($choice) == 1) {
			$choice = Arrays::getFirstItem($choice);
		}

		// Normalize op
		if (is_array($choice)) {
			if ($op == self::OP_IS) $op = self::OP_CONTAINS;
			if ($op == self::OP_NOT) $op = self::OP_NOTCONTAINS;
		} else {
			if ($op == self::OP_CONTAINS) $op = self::OP_IS;
			if ($op == self::OP_NOTCONTAINS) $op = self::OP_NOT;
		}

		if ($always_choice) {
			if ($op == self::OP_IS) $op = self::OP_CONTAINS;
			if ($op == self::OP_NOT) $op = self::OP_NOTCONTAINS;
		}

		if ($title_callback) {
			$title = call_user_func($title_callback, $choice, $field);
		} else {
			$title = $choice;
		}

		if (is_array($title)) {
			$last = array_pop($title);
			$title = implode(', ', (array)$title);
			if ($title) {
				$title .= ' or ';
			}
			$title .= $last;
		}

		switch ($op) {
			case self::OP_IS:
				$summary = App::getTranslator()->phrase('agent.x_is_y', array('field' => $field, 'value' => $title));
				break;
			case self::OP_NOT:
				$summary = App::getTranslator()->phrase('agent.x_is_not_y', array('field' => $field, 'value' => $title));
				break;
			case self::OP_CONTAINS:
				$summary = App::getTranslator()->phrase('agent.x_is_y', array('field' => $field, 'value' => $title));
				break;
			case self::OP_NOTCONTAINS:
				$summary = App::getTranslator()->phrase('agent.x_is_not_y', array('field' => $field, 'value' => $title));
				break;
		}

		return $summary;
	}


	protected function _normalizeOpAndChoice(&$op, &$choice)
	{
		if (is_array($choice) AND count($choice) == 1) {
			$choice = Arrays::getFirstItem($choice);
		}

		// Normalize op
		if (is_array($choice)) {
			if ($op == self::OP_IS) $op = self::OP_CONTAINS;
			if ($op == self::OP_NOT) $op = self::OP_NOTCONTAINS;
		} else {
			if ($op == self::OP_CONTAINS) $op = self::OP_IS;
			if ($op == self::OP_NOTCONTAINS) $op = self::OP_NOT;
		}
	}

	protected function _normalizeAgentChoice($choice)
	{
		$choice = (array)$choice;

		$agent_ids = array();
		$not_id = null;
		$unassigned = false;

		foreach ($choice as $c) {
			$c = (int)$c;
			if ($c === 0) {
				$unassigned = true;
				break;
			} elseif ($c == -1) {
				if ($this->getPersonContext()) {
					$agent_ids[] = $this->getPersonContext()->getId();
				} else {
					$agent_ids[] = -1;
				}
			} elseif ($c == -2) {
				if ($this->getPersonContext()) {
					$not_id = $this->getPersonContext()->getId();
				} else {
					$not_id = -1;
				}
			} else {
				$agent_ids = $c;
			}
		}

		return array(
			'agent_ids' => $agent_ids,
			'not_id' => $not_id,
			'unassigned' => $unassigned
		);
	}

	protected function _normalizeAgentTeamChoice($choice)
	{
		$choice = (array)$choice;

		$team_ids = array();
		$not_ids = null;
		$no_team = false;

		if ($this->getPersonContext()) {
			$agent = $this->getPersonContext();
			$agent->loadHelper('AgentTeam');
		} else {
			$agent = null;
		}

		foreach ($choice as $c) {
			$c = (int)$c;
			if ($c === 0) {
				$no_team = true;
				break;
			} elseif ($c == -1) {
				if ($agent) {
					$team_ids = Arrays::removeFalsey($agent->getAgentTeamIds());
				} else {
					$team_ids = array();
				}
				$team_ids[] = -1;
			} elseif ($c == -2) {
				if ($agent) {
					$not_ids = Arrays::removeFalsey($agent->getAgentTeamIds());
				} else {
					$not_ids = array();
				}
				$not_ids[] = -1;
			} else {
				$team_ids = $c;
			}
		}

		return array(
			'team_ids' => $team_ids,
			'not_ids' => $not_ids,
			'no_team' => $no_team
		);
	}
}
