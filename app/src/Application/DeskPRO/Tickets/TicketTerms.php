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

namespace Application\DeskPRO\Tickets;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;
use Application\DeskPRO\Searcher\TicketSearch;
use Application\DeskPRO\Searcher\PersonSearch;

use Application\DeskPRO\Tickets\TicketChangeTracker;

use Orb\Util\Numbers;
use Orb\Util\Arrays;
use Orb\Util\Strings;

class TicketTerms
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

	/**
	 * @var array
	 */
	protected $terms = array();

	/**
	 * @var array
	 */
	protected $term_ids_map = array();

	/**
	 * @var \Application\DeskPRO\Tickets\TicketChangeTracker
	 */
	protected $tracker = null;


	/**
	 * @param array $terms
	 */
	public function __construct(array $terms)
	{
		$this->terms = $terms;

		foreach ($terms as $info) {
			if (!isset($info['options'])) {
				continue;
			}
			if (isset($info['type'])) {
				if (!isset($this->term_ids[$info['type']])) {
					$this->term_ids_map[$info['type']] = array();
				}
				$this->term_ids_map[$info['type']][] = $info;
			}
		}
	}


	/**
	 * Check if there is a certain term in this collection
	 *
	 * @param string $type
	 * @return bool
	 */
	public function hasTicketTerm($type)
	{
		return isset($this->term_ids_map[$type]);
	}


	/**
	 * @param string $type
	 * @param bool $first
	 * @return array
	 */
	public function getTicketTerm($type, $first = true)
	{
		if (!isset($this->term_ids_map[$type])) {
			return array();
		}

		if (!$first) {
			return $this->term_ids_map[$type];
		}

		return Arrays::getFirstItem($this->term_ids_map[$type]);
	}


	/**
	 * @param $tracker
	 */
	public function setChangeTracker($tracker)
	{
		$this->tracker = $tracker;
	}


	/**
	 * Check a specific ticket against these terms to see if it matches.
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 * @return bool
	 */
	public function doesTicketMatch(Entity\Ticket $ticket)
	{
		foreach ($this->terms as $info) {

			if (empty($info['type']) || empty($info['op']) || empty($info['options'])) {
				continue;
			}

			$term = $info['type'];
			if (!$term) continue;

			$op = $info['op'];
			$choice = $info['options'];

			if (strpos($op, 'changed') !== false) {
				if ($tracker) {
					if (!$this->testChangedTerm($ticket, $term, $op, $choice)) {
						return false;
					}
				} else {
					return false;
				}
			} else {
				if (!$this->testTerm($ticket, $term, $op, $choice)) {
					return false;
				}
			}
		}

		return true;
	}


	/**
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 * @param TicketChangeTracker|null $tracker
	 * @return bool
	 */
	public function doesTicketMatchAny(Entity\Ticket $ticket, TicketChangeTracker $tracker = null)
	{
		foreach ($this->terms as $term => $info) {

			$term = $info['type'];
			if (!$term) continue;

			$op = $info['op'];
			$choice = $info['options'];

			if ($this->testTerm($ticket, $term, $op, $choice)) {
				return true;
			}
		}

		return false;
	}


	/**
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 * @param string $term
	 * @param string $op
	 * @param mixed $choice
	 * @return bool
	 */
	public function testChangedTerm(Entity\Ticket $ticket, $term, $op, $choice)
	{
		$tracker = $this->tracker;
		if (!$tracker) return false;

		if (!$tracker->isPropertyChanged($term)) {
			return false;
		}

		// No specific value check
		if ($op == 'changed') {
			return true;
		}

		$info = $tracker->getChangedProperty($term);

		if (strpos($op, '_to') !== false) {
			$val = $info['new'];
		} else {
			$val = $info['old'];
		}

		$ticket2 = clone $ticket;
		$ticket2[$term] = $val;

		$rangeop = Strings::extractRegexMatch('#_(gte|lte|gt|lt)$#', $op, 1);

		if (strpos($op, 'not_') !== false) {
			if ($rangeop) {
				$pass = !$this->testTerm($ticket2, $term, $rangeop, $choice);
			} else {
				$pass = $this->testTerm($ticket2, $term, 'not', $choice);
			}
		} else {
			if ($rangeop) {
				$pass = $this->testTerm($ticket2, $term, $rangeop, $choice);
			} else {
				$pass = $this->testTerm($ticket2, $term, 'is', $choice);
			}
		}

		return $pass;
	}


	/**
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 * @param string $term
	 * @param string $op
	 * @param mixed $choice
	 * @return bool
	 */
	public function testTerm(Entity\Ticket $ticket, $term, $op, $choice)
	{
		$tracker = $this->tracker;

		// $term of people_field[12] becomes $term=people_field, $term_id=12 etc
		$m = null;
		$term_id = null;
		if (preg_match('#^(.*?)\[(.*?)\]$#', $term, $m)) {
			$term = $m[1];
			$term_id = $m[2];
		}

		switch ($term) {

			case 'is_new_user':
				return $ticket->person->isNewPerson();
				break;

			case 'is_not_new_user':
				return !$ticket->person->isNewPerson();
				break;

			case 'creation_system':
				$choice = (array)$choice;
				$choice = array_pop($choice);

				$reply = $tracker->getNewReply();
				if ($reply) {
					$creation_system = $reply->creation_system;
				} else {
					$creation_system = $ticket->creation_system;
				}

				if ($op == 'is') {
					if ($creation_system != $choice) return false;
				}
				if ($op == 'not') {
					if ($creation_system == $choice) return false;
				}
				break;

			case 'action_performer':
				$is_agent = App::getCurrentPerson()->isAgent();

				$choice = (array)$choice;
				$choice = array_pop($choice);

				if ($choice == 'agent') {
					if ($is_agent) {
						if ($op != 'is') return false;
					} else {
						if ($op != 'not') return false;
					}
				} else {
					if ($is_agent) {
						if ($op	!= 'not') return false;
					} else {
						if ($op != 'is') return false;
					}
				}
				break;

			case 'robot_email':
				if (!$ticket->email_reader) {
					return false;
				}

				$auto = $ticket->email_reader->getHeader('Auto-Submitted')->getAllParts();
				foreach ($auto as $v) {
					$v = strtolower($v);
					if ($v == 'auto-replied' || $v == 'auto-notified' || $v == 'auto-generated') {
						return true;
					}
				}
				break;

			case 'to_address':

				if (!$ticket->email_reader) {
					return false;
				}

				$check = strtolower($choice['to_address']);

				$tos = $ticket->email_reader->getToAddresses();
				foreach ($tos as $to) {
					$to = $to->getEmail();
					$to = strtolower($to);

					if ($check == $to) {
						return true;
					}
				}

				return false;

				break;

			case 'cc_address':

				if (!$ticket->email_reader) {
					return false;
				}

				$check = strtolower($choice['cc_address']);

				$ccs = $ticket->email_reader->getCcAddresses();
				foreach ($ccs as $cc) {
					$cc = $to->getEmail();
					$cc = strtolower($cc);

					if ($check == $cc) {
						return true;
					}
				}

				return false;

				break;

			case TicketSearch::TERM_DEPARTMENT:
				if (count($choice) == 1) $choice = array_pop($choice);
				$choice = App::getDataService('Department')->getIdsInTree($choice, true);

				if (!$this->_testChoiceMatch($ticket['department_id'], $op, $choice)) return false;
				break;
			case TicketSearch::TERM_CATEGORY:
				if (!$this->_testChoiceMatch($ticket['category_id'], $op, $choice)) return false;
				break;
			case TicketSearch::TERM_PRODUCT:
				if (!$this->_testChoiceMatch($ticket['product_id'], $op, $choice)) return false;
				break;
			case TicketSearch::TERM_PRIORITY:
				if (!$this->_testChoiceMatch($ticket['priority_id'], $op, $choice)) return false;
				break;
			case TicketSearch::TERM_ORGANIZATION:
				if (!$this->_testChoiceMatch($ticket['organization_id'], $op, $choice)) return false;
				break;
			case TicketSearch::TERM_LANGUAGE:
				if (!$this->_testChoiceMatch($ticket['language_id'], $op, $choice)) return false;
				break;
			case TicketSearch::TERM_AGENT:
				if (!$this->_testChoiceMatch($ticket['agent_id'], $op, $choice)) return false;
				break;
			case TicketSearch::TERM_URGENCY:
				$choice = (array)$choice;
				$choice = array_pop($choice);
				switch ($op) {
					case self::OP_BETWEEN:
						if (!\Orb\Util\Numbers::inRange($ticket['urgency'], $choice['min'], $choice['max'])) return false;
						break;

					case self::OP_IS:
						if ($ticket['urgency'] != $choice['num']) return false;
						break;

					case self::OP_NOT:
						if ($ticket['urgency'] == $choice['num']) return false;
						break;

					case self::OP_LT:
						if (!($ticket['urgency'] < $choice['num'])) return false;
						break;

					case self::OP_LTE:
						if (!($ticket['urgency'] <= $choice['num'])) return false;
						break;

					case self::OP_GT:
						if (!($ticket['urgency'] < $choice['num'])) return false;
						break;

					case self::OP_GTE:
						if (!($ticket['urgency'] <= $choice['num'])) return false;
						break;
				}
				break;
			case TicketSearch::TERM_PARTICIPANT:
				if (is_array($choice)) {
					$participant_ids = $ticket->getParticipantIds();
					$any = false;
					foreach ($choice as $person_id) {
						$is_in = in_array($person_id, $participant_ids);

						if ($is_in) {
							$any = true;
							if ($op == self::OP_CONTAINS) {
								break;
							} else {
								return false;
							}
						}
					}

					if ($op == self::OP_CONTAINS AND !$any) return false;
				} else {
					if ($ticket->hasParticipant($choice)) {
						if ($op == self::OP_NOT) return false;
					} else {
						if ($op == self::OP_IS) return false;
					}
				}
				break;
			case TicketSearch::TERM_SUBJECT:
				$choice = (array)$choice;
				$choice = array_pop($choice);
				switch ($op) {
					case self::OP_IS:
						if ($ticket['subject'] != $choice) return false;
						break;
					case self::OP_NOT:
						if ($ticket['subject'] == $choice) return false;
						break;
					case self::OP_CONTAINS:
						if (strpos(strtolower($ticket['subject']), strtolower($choice)) === false) return false;
						break;
					case self::OP_NOTCONTAINS:
						if (strpos(strtolower($ticket['subject']), strtolower($choice)) !== false) return false;
						break;
				}
				break;

			case TicketSearch::TERM_TICKET_FIELD:
				$test = $this->testCustomField('CustomDefTicket', $term_id, $op, $choice, $ticket);
				if (!$test && $test !== null) {
					return false;
				}
				break;

			case PersonSearch::TERM_PERSON_FIELD:
				$test = $this->testCustomField('CustomDefPerson', $term_id, $op, $choice, $ticket->person);
				if (!$test && $test !== null) {
					return false;
				}
				break;
		}

		return true;
	}


	/**
	 * @param string  $type      The custom field type: CustomDefTicket or CustomDefPerson
	 * @param int     $term_id   The field ID
	 * @param string  $op        The test operation
	 * @param mixed   $choice    The value to test against
	 * @param mixed   $obj       The ticket or person object
	 * @return bool|null
	 */
	protected function testCustomField($type, $term_id, $op, $choice, $obj)
	{
		$field = App::getEntityRepository('DeskPRO:'.$type)->find($term_id);
		if (!$field) return null;

		$search_type = $field->getHandler()->getSearchType();

		if (!isset($choice['custom_fields']['field_' . $term_id])) {
			return null;
		}

		$choice = $choice['custom_fields']['field_' . $term_id];

		switch ($search_type) {
			case 'input':
			case 'value':

				$set_value = $obj->getCustomDataForField($term_id);
				if ($set_value) {
					$set_value = $set_value->getData();
				}
				if (is_string($set_value)) {
					$set_value = Strings::utf8_strtolower($set_value);
				}

				$choice = Strings::utf8_strtolower($choice);

				switch ($op) {
					case self::OP_IS:
						if ($set_value != $choice) return false;
						break;
					case self::OP_NOT:
						if ($set_value == $choice) return false;
						break;
					case self::OP_CONTAINS:
						if (strpos($set_value, $choice) === false) return false;
						break;
					case self::OP_NOTCONTAINS:
						if (strpos($set_value, $choice) !== false) return false;
						break;
				}
				break;

			case 'id':
				$choices_in = array();
				foreach ((array)$choice as $c) {
					$choices_in[] = (int)$c;
				}

				$has_choices = array();
				foreach ($choices_in as $id) {
					$c = $obj->getCustomDataForField($id);
					if ($c) {
						$has_choices[$id] = $id;
					}
				}

				switch ($op) {
					case self::OP_CONTAINS:
					case self::OP_IS:
						if (!$has_choices) return false;
						break;

					case self::OP_NOTCONTAINS:
					case self::OP_NOT:
						if ($has_choices) return false;
						break;
				}
				break;
		}

		return true;
	}


	/**
	 * @param mixed $value
	 * @param string $op
	 * @param mixed $choice
	 * @return bool
	 */
	protected function _testChoiceMatch($value, $op, $choice)
	{
		if (is_array($choice)) {
			if ($op == self::OP_IS) {
				return in_array($value, $choice);
			} elseif ($op == self::OP_NOT) {
				return !in_array($value, $choice);
			}
		} else {
			if ($op == self::OP_IS) {
				return $value == $choice;
			} elseif ($op == self::OP_NOT) {
				return $value != $choice;
			}
		}

		return false;
	}


	/**
	 * Compiles these sets of terms into a number of JS tests on a 'ticket' variable.
	 * Note that this just generates the tests, so any implementation still has to wrap it in a function
	 * body etc.
	 *
	 * @param string $mode 'all' or 'any'
	 * @return string
	 */
	public function compileTermsToJavascript($mode = 'all')
	{
		$js = array();

		if ($mode == 'all') {
			$test_pass = '';
			$test_fail = 'return false;';
			$test_bottom = 'return true;';
		} else {
			$test_pass = 'return true;';
			$test_fail = '';
			$test_bottom = 'return false;';
		}

		$rb = \Application\DeskPRO\UI\RuleBuilder::newTermsBuilder();
		$terms = $rb->readForm($this->terms);

		foreach ($terms as $info) {

			$term = $info['type'];

			if (!$term) continue;

			$op = $info['op'];
			$choice = $info['options'];

			if (count($choice) == 1) {
				$choice = array_pop($choice);
			}

			switch ($term) {
				case TicketSearch::TERM_DEPARTMENT:
					$ids = array();
					foreach ((array)$choice as $cond) {
						$ids = array_merge($ids, App::getDataService('Department')->getIdsInTree($cond, true));
					}
					$ids = Arrays::castToType($ids, 'int');
					if (count($ids) == 1) $ids = $ids[0];

					$js[] = $this->_compileJsChoiceTermCondition("ticket.getDepartmentId()", $op, $ids) . " { $test_pass } else { $test_fail } ";
					break;
				case TicketSearch::TERM_CATEGORY:
					$ids = array();
					foreach ((array)$choice as $cond) {
						$ids = array_merge($ids, App::getEntityRepository('DeskPRO:TicketCategory')->getIdsInTree($cond, true));
					}
					$ids = Arrays::castToType($ids, 'int');
					if (count($ids) == 1) $ids = $ids[0];

					$js[] = $this->_compileJsChoiceTermCondition("ticket.getCategoryId()", $op, $ids) . " { $test_pass } else { $test_fail } ";
					break;
				case TicketSearch::TERM_PRODUCT:
					$js[] = $this->_compileJsChoiceTermCondition("ticket.getProductId()", $op, $choice) . " { $test_pass } else { $test_fail } ";
					break;
				case TicketSearch::TERM_PRIORITY:
					$js[] = $this->_compileJsChoiceTermCondition("ticket.getPriorityId()", $op, $choice) . " { $test_pass } else { $test_fail } ";
					break;
				case TicketSearch::TERM_ORGANIZATION:
					$js[] = $this->_compileJsChoiceTermCondition("ticket.getOrganizationId()", $op, $choice) . " { $test_pass } else { $test_fail } ";
					break;
				case TicketSearch::TERM_LANGUAGE:
					$js[] = $this->_compileJsChoiceTermCondition("ticket.getLanguageId()", $op, $choice) . " { $test_pass } else { $test_fail } ";
					break;
				case TicketSearch::TERM_AGENT:
					$js[] = $this->_compileJsChoiceTermCondition("ticket.getAgentId()", $op, $choice) . " { $test_pass } else { $test_fail } ";
					break;
			}
		}

		$js[] = $test_bottom;

		$js = implode(' ', $js);

		return $js;
	}


	/**
	 * @param mixed $value
	 * @param string $op
	 * @param string $choice
	 * @return string
	 */
	protected function _compileJsChoiceTermCondition($value, $op, $choice)
	{
		if (is_array($choice) AND count($choice) == 1) {
			$choice = array_pop($choice);
		}

		if (is_array($choice)) {
			if (Arrays::checkAll($choice, function($v) { return Numbers::isInteger($v); })) {
				$choice = Arrays::castToType($choice, 'integer');
			}

			$choice = json_encode(array_values($choice));
			if ($op == self::OP_IS) {
				return "if ($choice.indexOf($value) !== -1) ";
			} elseif ($op == self::OP_NOT) {
				return "if ($choice.indexOf($value) === -1) ";
			}
		} else {
			if (Numbers::isInteger($choice)) {
				$choice = (int)$choice;
			}
			$choice = json_encode($choice);
			if ($op == self::OP_IS) {
				return "if ($value == $choice) ";
			} elseif ($op == self::OP_NOT) {
				return "if ($value != $choice) ";
			}
		}

		return '';
	}

	/**
	 * @return array
	 */
	public function getDescriptions()
	{
		$descs = array();
        $tr = App::getTranslator();

		foreach ($this->terms as $info) {

			if (empty($info['type']) || empty($info['op']) || empty($info['options'])) {
				continue;
			}

			$term = $info['type'];
			if (!$term) continue;

			$op = $info['op'];
			$choice = $info['options'];

			if (strpos($op, 'changed') !== false) {
				$term = $this->getTermDescription($term, $op, $choice);
				if ($term) {
					$descs[$info['type']] = $tr->phrase('admin.tickets.changed_to_effect', array('description' => $term));
				}
			} else {
				$term = $this->getTermDescription($term, $op, $choice);
				if ($term) {
					$descs[$info['type']] = $term;
				} else {
					error_log("Unknown term description for {$info['type']}");
				}
			}
		}

		return $descs;
	}


	/**
	 * Compiles a term into an english phrase to describe the test
	 *
	 * @param string $term
	 * @param string $op
	 * @param mixed $choice
	 * @return string
	 */
	public function getTermDescription($term, $op, $choice)
	{
		$term_summary = new \Application\DeskPRO\Translate\TermSummary();
		if (strpos($term, 'person_') === 0) {
			$summary = $term_summary->getSummary($term, $op, $choice);
			if (!$summary) {
				$summary = $term_summary->getSummary(preg_replace('#^person_#', '', $term), $op, $choice);
			}
		} else {
			$summary = $term_summary->getSummary("ticket_$term", $op, $choice);
		}

		if (!$summary) {
			$summary = $term_summary->getSummary($term, $op, $choice);
		}

		return $summary;
	}
}
