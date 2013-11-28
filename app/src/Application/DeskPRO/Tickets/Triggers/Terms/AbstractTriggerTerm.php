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
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Triggers\Terms;

use Doctrine\Common\Collections\Collection;
use Orb\Util\Numbers;
use Orb\Util\Strings;
use Orb\Util\Util;
use Application\DeskPRO\Criteria\CriteriaTermInterface;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContext;


abstract class AbstractTriggerTerm implements CriteriaTermInterface, TriggerTermInterface
{
	const OP_NOOP        = null;
	const OP_IS          = 'is';
	const OP_NOT         = 'not';
	const OP_LT          = 'lt';
	const OP_GT          = 'gt';
	const OP_LTE         = 'lte';
	const OP_GTE         = 'gte';
	const OP_BETWEEN     = 'between';
	const OP_CONTAINS    = 'contains';
	const OP_NOTCONTAINS = 'notcontains';
	const OP_IS_REGEX    = 'is_regex';
	const OP_NOT_REGEX   = 'not_regex';

	const OP_CHANGED            = 'changed';
	const OP_CHANGED_TO         = 'changed_to';
	const OP_CHANGED_FROM       = 'changed_from';
	const OP_NOT_CHANGED_TO     = 'not_changed_to';
	const OP_NOT_CHANGED_FROM   = 'not_changed_from';

	/**
	 * @var
	 */
	private $op;

	/**
	 * @var array
	 */
	private $options;


	/**
	 * @param string $op
	 * @param array  $options
	 */
	public function __construct($op, array $options)
	{
		$this->op      = $op;
		$this->options = $options;
	}


	/**
	 * Gets the type name of the criteria
	 *
	 * @return string
	 */
	public function getTermType()
	{
		return Util::getBaseClassname($this);
	}


	/**
	 * Gets criteria operator (is, is not, etc).
	 *
	 * @return string
	 */
	public function getTermOperator()
	{
		return $this->op;
	}


	/**
	 * Get's an array of options
	 *
	 * @return array
	 */
	public function getTermOptions()
	{
		return $this->options;
	}


	/**
	 * @param Ticket $ticket
	 * @param ExecutorContext $context
	 * @param string $prop_name
	 * @return array
	 */
	protected function getValueOpArray(Ticket $ticket, ExecutorContext $context, $prop_name)
	{
		$value        = $ticket->$prop_name;
		$op           = $this->op;
		$is_change_op = false;
		$was_changed  = false;

		if (strpos($this->op, 'changed') !== false) {
			$is_change_op = true;
			$state = $ticket->getStateChangeRecorder();

			if ($state->hasChangedField($prop_name)) {
				$was_changed = true;

				if ($this->op == 'changed_to') {
					$op = 'is';
				} else if ($this->op == 'not_changed_to') {
					$op = 'not';
				} else {
					$value = $state->getLastChangeForField($prop_name);
					if ($this->op == 'changed_from') {
						$op = 'is';
					} else {
						$op = 'not';
					}
				}
			}
		}

		if ($value instanceof Collection) {
			$value = $value->toArray();
		}

		$context->getLogger()->info(sprintf("[%s] Real op: %s, processed op: %s", Util::getBaseClassname($this), $this->op, $op));
		if ($is_change_op && !$was_changed) {
			$context->getLogger()->info(sprintf("[%s]\t(Field was not changed)", Util::getBaseClassname($this), $this->op, $op));
		}

		return array(
			'op'            => $op,
			'value'         => $value,
			'is_changed_op' => $is_change_op,
			'was_changed'   => $was_changed
		);
	}


	/**
	 * @param Ticket $ticket
	 * @param ExecutorContext $context
	 * @param string $prop_name
	 * @param string $id_prop
	 * @param array $check_ids
	 * @return bool
	 */
	protected function isCollectionMatch(Ticket $ticket, ExecutorContext $context, $prop_name, $id_prop, array $check_ids)
	{
		$opts  = $this->getValueOpArray($ticket, $context, $prop_name);
		$op    = $opts['op'];
		$value = $opts['value'];

		if ($opts['is_changed_op'] && !$opts['was_changed']) {
			return false;
		}

		$check_ids = array_fill_keys($check_ids, true);
		$has = false;
		foreach ($value as $val) {
			if (isset($check_ids[$val->$id_prop])) {
				$has = true;
				break;
			}
		}

		switch ($op) {
			case self::OP_IS:
			case self::OP_CONTAINS:
				if ($has) {
					return true;
				}
				break;

			case self::OP_NOT:
			case self::OP_NOTCONTAINS:
				if (!$has) {
					return true;
				}
		}

		$context->getLogger()->info(sprintf("[%s] isCollectionMatch no pass on: %s %s", Util::getBaseClassname($this), $op, implode(', ', array_keys($check_ids))));

		return false;
	}


	/**
	 * @param Ticket $ticket
	 * @param ExecutorContext $context
	 * @param string $prop_name
	 * @param string $id_prop
	 * @param array $check_ids
	 * @return bool
	 */
	protected function isEntityMatch(Ticket $ticket, ExecutorContext $context, $prop_name, $id_prop, array $check_ids)
	{
		$opts  = $this->getValueOpArray($ticket, $context, $prop_name);
		$op    = $opts['op'];
		$value = $opts['value'];

		if ($opts['is_changed_op'] && !$opts['was_changed']) {
			return false;
		}

		$check_ids = array_fill_keys($check_ids, true);
		$has = false;
		if ($value === null) {
			$value_id = 0;
		} else {
			$value_id = $value->$id_prop;
		}

		if (isset($check_ids[$value_id])) {
			$has = true;
		}

		switch ($op) {
			case self::OP_IS:
			case self::OP_CONTAINS:
				if ($has) {
					return true;
				}
				break;

			case self::OP_NOT:
			case self::OP_NOTCONTAINS:
				if (!$has) {
					return true;
				}
		}

		$context->getLogger()->info(sprintf("[%s] isEntityMatch no pass on: %s(%s) != %s", Util::getBaseClassname($this), $op, $value_id, implode(', ', array_keys($check_ids))));

		return false;
	}


	/**
	 * @param Ticket $ticket
	 * @param ExecutorContext $context
	 * @param string $prop_name
	 * @param \DateTime $check_value
	 * @return bool
	 */
	protected function isDateMatch(Ticket $ticket, ExecutorContext $context, $prop_name, \DateTime $check_value)
	{
		$opts  = $this->getValueOpArray($ticket, $context, $prop_name);
		$op    = $opts['op'];
		$value = $opts['value'];

		if (!$value) {
			return false;
		}

		$value       = $value->getTimestamp();
		$check_value = $check_value->getTimestamp();

		switch ($op) {
			case self::OP_IS:     if ($check_value == $value)  return true; break;
			case self::OP_NOT:    if ($check_value != $value)  return true; break;
			case self::OP_GT:     if ($check_value > $value)   return true; break;
			case self::OP_GTE:    if ($check_value >= $value)  return true; break;
			case self::OP_LT:     if ($check_value < $value)   return true; break;
			case self::OP_LTE:    if ($check_value <= $value)  return true; break;
		}

		$context->getLogger()->info(sprintf("[%s] isDateMatch no pass on: %s %s", Util::getBaseClassname($this), $op, $check_value));

		return false;
	}


	/**
	 * @param Ticket $ticket
	 * @param ExecutorContext $context
	 * @param string $prop_name
	 * @param \DateTime $lower
	 * @param \DateTime $upper
	 * @return bool
	 */
	protected function isDateRangeMatch(Ticket $ticket, ExecutorContext $context, $prop_name, \DateTime $lower, \DateTime $upper)
	{
		$opts  = $this->getValueOpArray($ticket, $context, $prop_name);
		$value = $opts['value'];

		$value = $value->getTimestamp();
		$lower = $lower->getTimestamp();
		$upper = $upper->getTimestamp();

		if (Numbers::inRange($value, $lower, $upper)) {
			return true;
		}

		$context->getLogger()->info(sprintf("[%s] isDateRangeMatch no pass on: %s to %s", Util::getBaseClassname($this), $lower, $upper));

		return false;
	}


	/**
	 * @param Ticket $ticket
	 * @param ExecutorContext $context
	 * @param string $prop_name
	 * @param string $check_value
	 * @return bool
	 */
	protected function isIntMatch(Ticket $ticket, ExecutorContext $context, $prop_name, $check_value)
	{
		$opts  = $this->getValueOpArray($ticket, $context, $prop_name);
		$op    = $opts['op'];
		$value = $opts['value'];

		$value       = (int)$value;
		$check_value = (int)$check_value;

		switch ($op) {
			case self::OP_IS:     if ($check_value == $value)  return true; break;
			case self::OP_NOT:    if ($check_value != $value)  return true; break;
			case self::OP_GT:     if ($check_value > $value)   return true; break;
			case self::OP_GTE:    if ($check_value >= $value)  return true; break;
			case self::OP_LT:     if ($check_value < $value)   return true; break;
			case self::OP_LTE:    if ($check_value <= $value)  return true; break;
		}

		$context->getLogger()->info(sprintf("[%s] isIntMatch no pass on: %s %s", Util::getBaseClassname($this), $op, $check_value));

		return false;
	}


	/**
	 * @param Ticket $ticket
	 * @param ExecutorContext $context
	 * @param string $prop_name
	 * @param int $lower
	 * @param int $upper
	 * @return bool
	 */
	protected function isIntRangeMatch(Ticket $ticket, ExecutorContext $context, $prop_name, $lower, $upper)
	{
		$opts  = $this->getValueOpArray($ticket, $context, $prop_name);
		$value = $opts['value'];

		$value = (int)$value;
		$lower = (int)$lower;
		$upper = (int)$upper;

		if (Numbers::inRange($value, $lower, $upper)) {
			return true;
		}

		$context->getLogger()->info(sprintf("[%s] isIntRangeMatch no pass on: %s to %s", Util::getBaseClassname($this), $lower, $upper));

		return false;
	}


	/**
	 * @param Ticket $ticket
	 * @param ExecutorContext $context
	 * @param string $prop_name
	 * @param string $check_value
	 * @return bool
	 */
	protected function isStringMatch(Ticket $ticket, ExecutorContext $context, $prop_name, $check_value)
	{
		$opts  = $this->getValueOpArray($ticket, $context, $prop_name);
		$op    = $opts['op'];
		$value = $opts['value'];

		$value_i       = Strings::utf8_strtolower($value);
		$check_value_i = Strings::utf8_strtolower($check_value);

		switch ($op) {
			case self::OP_IS:
			case self::OP_NOT:
				if ($value_i == $check_value_i) {
					if ($op == self::OP_IS) return true;
				} else {
					if ($op == self::OP_NOT) return true;
				}
				break;

			case self::OP_CONTAINS:
			case self::OP_NOTCONTAINS:
				if (strpos($value_i, $check_value_i) !== false) {
					if ($op == self::OP_IS) return true;
				} else {
					if ($op == self::OP_NOT) return true;
				}
				break;

			case self::OP_IS_REGEX:
			case self::OP_NOT_REGEX:
				$regex = Strings::getInputRegexPattern($check_value);
				if (!$regex) {
					return false;
				}

				if (preg_match($regex, $value)) {
					if (self::OP_IS_REGEX) return true;
				} else {
					if (self::OP_NOT_REGEX) return true;
				}
		}

		return false;
	}


	/**
	 * @param  Ticket $ticket
	 * @param  ExecutorContext $context
	 * @return bool
	 */
	abstract public function isTriggerMatch(Ticket $ticket, ExecutorContext $context);
}