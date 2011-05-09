<?php

namespace Application\DeskPRO\Tickets;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;
use \Application\DeskPRO\Searcher\TicketSearch;

use \Application\DeskPRO\Tickets\TicketChangeTracker;

use \Orb\Util\Numbers;
use \Orb\Util\Arrays;

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

	protected $terms = array();

	protected $tracker = null;

	public function __construct(array $terms)
	{
		$this->terms = $terms;
	}

	public function setChangeTracker($tracker)
	{
		$this->tracker = $tracker;
	}


	/**
	 * Check a specific ticket against these terms to see if it matches.
	 *
	 * @param Ticket $ticket
	 * @return bool
	 */
	public function doesTicketMatch(Entity\Ticket $ticket)
	{
		foreach ($this->terms as $info) {

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

	public function testChangedTerm(Entity\Ticket $ticket, $term, $op, $choice)
	{
		if (!$tracker) return false;

		$tracker = $this->tracker;
		
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

		if (strpos($op, 'not_') !== false) {
			$pass = $this->testTerm($ticket2, $term, 'not', $choice);
		} else {
			$pass = $this->testTerm($ticket2, $term, 'is', $choice);
		}

		return $pass;
	}

	public function testTerm(Entity\Ticket $ticket, $term, $op, $choice)
	{
		switch ($term) {
			case TicketSearch::TERM_DEPARTMENT:
				$choice = App::getEntityRepository('DeskPRO:Department')->getIdsInTree($choice, true);
				if (count($choice) == 1) $choice = $choice[0];

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
		}

		return true;
	}

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

		foreach ($this->terms as $info) {

			$term = $info['type'];

			if (!$term) continue;

			$op = $info['op'];
			$choice = $info['options'];

			switch ($term) {
				case TicketSearch::TERM_DEPARTMENT:
					$ids = array();
					foreach ($cond_ids as $cond) {
						$ids = array_merge($ids, App::getEntityRepository('DeskPRO:Department')->getIdsInTree($cond, true));
					}
					$ids = Arrays::castToType($ids, 'int');
					if (count($ids) == 1) $ids = $ids[0];

					$js[] = "if (typeof ticket.department_id === 'function') ticket.department_id = ticket.department_id();";
					$js[] = $this->_compileJsChoiceTermCondition("parseInt(ticket.department_id)", $op, $ids) . " { $test_pass } else { $test_fail } ";
					break;
				case TicketSearch::TERM_CATEGORY:
					$ids = array();
					foreach ($cond_ids as $cond) {
						$ids = array_merge($ids, App::getEntityRepository('DeskPRO:TicketCategory')->getIdsInTree($cond, true));
					}
					$ids = Arrays::castToType($ids, 'int');
					if (count($ids) == 1) $ids = $ids[0];

					$js[] = "if (typeof ticket.category_id === 'function') ticket.category_id = ticket.category_id();";
					$js[] = $this->_compileJsChoiceTermCondition("parseInt(ticket.category_id)", $op, $ids) . " { $test_pass } else { $test_fail } ";
					break;
				case TicketSearch::TERM_PRODUCT:
					$js[] = "if (typeof ticket.product_id === 'function') ticket.product_id = ticket.product_id();";
					$js[] = $this->_compileJsChoiceTermCondition("parseInt(ticket.product_id)", $op, $choice) . " { $test_pass } else { $test_fail } ";
					break;
				case TicketSearch::TERM_PRIORITY:
					$js[] = "if (typeof ticket.priority_id === 'function') ticket.priority_id = ticket.priority_id();";
					$js[] = $this->_compileJsChoiceTermCondition("parseInt(ticket.priority_id)", $op, $choice) . " { $test_pass } else { $test_fail } ";
					break;
				case TicketSearch::TERM_ORGANIZATION:
					$js[] = "if (typeof ticket.organization_id === 'function') ticket.organization_id = ticket.organization_id();";
					$js[] = $this->_compileJsChoiceTermCondition("parseInt(ticket.organization_id)", $op, $choice) . " { $test_pass } else { $test_fail } ";
					break;
				case TicketSearch::TERM_LANGUAGE:
					$js[] = "if (typeof ticket.language_id === 'function') ticket.language_id = ticket.language_id();";
					$js[] = $this->_compileJsChoiceTermCondition("parseInt(ticket.language_id)", $op, $choice) . " { $test_pass } else { $test_fail } ";
					break;
				case TicketSearch::TERM_AGENT:
					$js[] = "if (typeof ticket.agent_id === 'function') ticket.agent_id = ticket.agent_id();";
					$js[] = $this->_compileJsChoiceTermCondition("parseInt(ticket.agent_id)", $op, $choice) . " { $test_pass } else { $test_fail } ";
					break;
			}
		}

		$js[] = $test_bottom;

		$js = implode(' ', $js);

		return $js;
	}

	protected function _compileJsChoiceTermCondition($value, $op, $choice)
	{
		if (count($choice) == 1) {
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
	}
}