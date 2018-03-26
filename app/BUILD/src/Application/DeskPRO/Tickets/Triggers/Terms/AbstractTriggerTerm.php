<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Criteria\CriteriaTermInterface;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use DeskPRO\Component\Util\RegexUtils;
use Doctrine\Common\Collections\Collection;
use Orb\Util\CheckedOptionsArray;
use Orb\Util\Numbers;
use Orb\Util\Strings;
use Orb\Util\Util;

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
    const OP_NOTBETWEEN  = 'notbetween';
    const OP_CONTAINS    = 'contains';
    const OP_NOTCONTAINS = 'notcontains';
    const OP_IS_REGEX    = 'is_regex';
    const OP_NOT_REGEX   = 'not_regex';
    const OP_ISSET       = 'isset';
    const OP_NOTISSET    = 'not_isset';

    const OP_CHANGED          = 'changed';
    const OP_CHANGED_TO       = 'changed_to';
    const OP_CHANGED_FROM     = 'changed_from';
    const OP_NOT_CHANGED_TO   = 'not_changed_to';
    const OP_NOT_CHANGED_FROM = 'not_changed_from';

    const OP_TOUCHED     = 'touched';
    const OP_NOT_TOUCHED = 'nottouched';

    /**
     * @var
     */
    private $op;

    /**
     * @var \Orb\Util\CheckedOptionsArray
     */
    private $options;

    /**
     * @param string $op
     * @param array  $options
     */
    public function __construct($op, array $options = [])
    {
        $this->op = $op;
        $this->_initOptions($options);
    }

    /**
     * @param array $options
     */
    private function _initOptions(array $options)
    {
        $this->options = $this->getOptionsDef();
        $this->options->setAll($options);
        $this->options->setArrayDefault($this->getDefaultOptions());

        // touched/changed ops dont take args, but we want to keep
        // the required options list for better type-checking,
        // so a quick workaround is to null out required names
        switch ($this->op) {
            case 'touched':
            case 'nottouched':
            case 'changed':
                foreach ($this->options->getRequiredNames() as $name) {
                    if ('field_id' === $name) {
                        continue;
                    }
                    $this->options->set($name, null);
                }
                break;
        }

        $this->options->ensureRequired();
    }

    /**
     * @return array
     */
    protected function getDefaultOptions()
    {
        return [];
    }

    /**
     * @return CheckedOptionsArray
     */
    protected function getOptionsDef()
    {
        return new CheckedOptionsArray();
    }

    /**
     * Gets the type name of the criteria.
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
     * Get's an array of options.
     *
     * @return \Orb\Util\OptionsArray
     */
    public function getTermOptions()
    {
        return $this->options;
    }

    /**
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     * @param string                   $prop_name
     *
     * @return array
     */
    protected function getValueOpArray(Ticket $ticket, ExecutorContextInterface $context, $prop_name)
    {
        if ($prop_name instanceof TermValue) {
            $value = $prop_name->getValue();
        } else {
            // Dotted notation lets us go 'deep' within the
            // object. E.g., person.name = $ticket->person->name
            if (strpos($prop_name, '.') !== false) {
                $value = $ticket;
                $parts = explode('.', $prop_name);

                while (($p = array_shift($parts)) !== null) {
                    // if we are using special array syntax
                    // we are collecting from an array (below)
                    $is_coll = false;
                    if (substr($p, -2) == '[]') {
                        $is_coll = true;
                        $p       = substr($p, 0, -2);
                    }

                    if (isset($value->$p)) {
                        $value = $value->$p;
                        if ($is_coll) {
                            break;
                        }
                    } else {
                        $value = null;
                        break;
                    }
                }

                if ($value === null) {
                    $value = [];
                }

                // Means we found an array collection syntax like emails[]
                // so we still have values after to get.
                // E.g., emails[].email means $value is now emails[], but we need
                // to reduce that down to email
                if ($is_coll && $parts) {
                    $parts_default = $parts;
                    $all_values    = $value;
                    $use_value     = [];
                    foreach ($all_values as $value) {
                        $parts = $parts_default;
                        while (($p = array_shift($parts)) !== null) {
                            if (isset($value->$p)) {
                                $value = $value->$p;
                                if ($is_coll) {
                                    break;
                                }
                            } else {
                                $value = null;
                                break;
                            }
                        }

                        $use_value[] = $value;
                    }

                    $value = $use_value;
                }
            } else {
                $value = $ticket->$prop_name;
            }
        }

        $state = $ticket->getStateChangeRecorder();

        $op            = $this->op;
        $is_change_op  = false;
        $is_touched_op = false;

        if (is_string($prop_name)) {
            $was_changed = $state->hasChangedField($prop_name);
            $was_touched = $state->hasTouchedField($prop_name);
        } else {
            $was_changed = false;
            $was_touched = false;
        }

        if (strpos($this->op, 'changed') !== false) {
            $is_change_op = true;

            if ($was_changed) {
                if ($this->op == 'changed_to') {
                    $op = 'is';
                } elseif ($this->op == 'not_changed_to') {
                    $op = 'not';
                } elseif ($this->op == 'changed_from') {
                    $op    = 'is';
                    $value = $state->getFirstChangeForField($prop_name)->getOld();
                } elseif ($this->op == 'not_changed_from') {
                    $op    = 'not';
                    $value = $state->getFirstChangeForField($prop_name)->getOld();
                } elseif ($op == 'changed') {
                    $op = 'changed';
                }
            }
        } elseif ($this->op == 'touched' || $this->op == 'nottouched') {
            $is_touched_op = true;
        }

        if ($value instanceof Collection) {
            $value = $value->toArray();
        }

        return [
            'op'            => $op,
            'value'         => $value,
            'is_changed_op' => $is_change_op,
            'is_touched_op' => $is_touched_op,
            'was_changed'   => $was_changed,
            'was_touched'   => $was_touched,
        ];
    }

    /**
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     * @param string                   $prop_name
     * @param string                   $id_prop
     * @param array                    $check_ids
     *
     * @return bool
     */
    protected function isCollectionMatch(
        Ticket $ticket,
        ExecutorContextInterface $context,
        $prop_name,
        $id_prop,
        array $check_ids = null
    ) {
        $opts  = $this->getValueOpArray($ticket, $context, $prop_name);
        $op    = $opts['op'];
        $value = $opts['value'];

        if ($opts['is_touched_op']) {
            if ($opts['was_touched']) {
                return $op == 'touched';
            } elseif (!$opts['was_touched']) {
                return $op == 'nottouched';
            }
        }

        if ($opts['is_changed_op'] && !$opts['was_changed']) {
            return false;
        }
        if ($opts['is_changed_op'] && $opts['was_changed'] && $op == 'changed') {
            return true;
        }

        if ($check_ids === null || empty($check_ids)) {
            return false;
        }

        $check_ids = array_fill_keys($check_ids, true);
        $has       = false;
        foreach ($value as $val) {
            if (isset($check_ids[$val->$id_prop])) {
                $has = true;
                break;
            }
        }

        switch ($op) {
            case 'is':
            case 'contains':
                if ($has) {
                    return true;
                }
                break;

            case 'not':
            case 'notcontains':
                if (!$has) {
                    return true;
                }
        }

        return false;
    }

    /**
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     * @param string                   $prop_name
     * @param string                   $id_prop
     * @param array                    $check_ids
     * @param string                   $multi_mode
     *
     * @return bool
     */
    protected function isEntityMatch(
        Ticket $ticket,
        ExecutorContextInterface $context,
        $prop_name,
        $id_prop,
        $check_ids = null,
        $multi_mode = null
    ) {
        $opts       = $this->getValueOpArray($ticket, $context, $prop_name);
        $op         = $opts['op'];
        $all_values = $opts['value'];

        if ($opts['is_touched_op']) {
            if ($opts['was_touched']) {
                return $op == 'touched';
            } elseif (!$opts['was_touched']) {
                return $op == 'nottouched';
            }
        }

        if ($opts['is_changed_op'] && !$opts['was_changed']) {
            return false;
        }
        if ($opts['is_changed_op'] && $opts['was_changed'] && $op == 'changed') {
            return true;
        }

        if (!is_array($all_values)) {
            $all_values = [$all_values];
        }

        if ($check_ids === null || empty($check_ids)) {
            $check_ids = [];
        }

        if (!is_array($check_ids)) {
            $check_ids = [$check_ids];
        }

        if ($check_ids) {
            // Strings to lowercase (1)
            $check_ids = array_map(
                function ($v) {
                    return is_string($v) ? strtolower($v) : $v;
                },
                $check_ids
            );
            $check_ids = array_fill_keys($check_ids, true);
        }

        $check_fn = function ($value) use ($op, $id_prop, $check_ids) {
            $has = false;
            if ($value === null || $value === '0' || $value === 0 || $value === false) {
                $value_id = 0;
            } else {
                $value_id = $value->$id_prop;
            }

            // Strings to lowercase (2)
            if (is_string($value_id)) {
                $value_id = strtolower($value_id);
            }

            if (isset($check_ids[$value_id])) {
                $has = true;
            }

            switch ($op) {
                case 'is':
                case 'contains':
                    if ($has) {
                        return true;
                    }
                    break;

                case 'not':
                case 'notcontains':
                    if (!$has) {
                        return true;
                    }
            }

            return false;
        };

        return $this->getMultiMatchResult($all_values, $check_fn, $op, $multi_mode);
    }

    /**
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     * @param string                   $prop_name
     * @param \DateTime                $check_value
     *
     * @return bool
     */
    protected function isDateMatch(
        Ticket $ticket,
        ExecutorContextInterface $context,
        $prop_name,
        \DateTime $check_value = null
    ) {
        $opts  = $this->getValueOpArray($ticket, $context, $prop_name);
        $op    = $opts['op'];
        $value = $opts['value'];

        if ($opts['is_touched_op']) {
            if ($opts['was_touched']) {
                return $op == 'touched';
            } elseif (!$opts['was_touched']) {
                return $op == 'nottouched';
            }
        }

        return $this->isDateValuesMatch($op, $value, $check_value);
    }

    /**
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     * @param string                   $prop_name
     * @param \DateTime                $lower
     * @param \DateTime                $upper
     *
     * @return bool
     */
    protected function isDateRangeMatch(
        Ticket $ticket,
        ExecutorContextInterface $context,
        $prop_name,
        \DateTime $lower,
        \DateTime $upper
    ) {
        $opts  = $this->getValueOpArray($ticket, $context, $prop_name);
        $value = $opts['value'];

        return $this->isDateRangeValuesMatch($value, $lower, $upper);
    }

    /**
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     * @param string                   $prop_name
     * @param string                   $check_value
     *
     * @return bool
     */
    protected function isIntMatch(Ticket $ticket, ExecutorContextInterface $context, $prop_name, $check_value = null)
    {
        $opts  = $this->getValueOpArray($ticket, $context, $prop_name);
        $op    = $opts['op'];
        $value = $opts['value'];

        if ($opts['is_touched_op']) {
            if ($opts['was_touched']) {
                return $op == 'touched';
            } elseif (!$opts['was_touched']) {
                return $op == 'nottouched';
            }
        }

        if ($check_value === null) {
            return false;
        }

        return $this->isIntValuesMatch($op, $value, $check_value);
    }

    /**
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     * @param string                   $prop_name
     * @param int                      $lower
     * @param int                      $upper
     *
     * @return bool
     */
    protected function isIntRangeMatch(Ticket $ticket, ExecutorContextInterface $context, $prop_name, $lower, $upper)
    {
        $opts  = $this->getValueOpArray($ticket, $context, $prop_name);
        $op    = $opts['op'];
        $value = $opts['value'];

        $value = (int) $value;
        $lower = (int) $lower;
        $upper = (int) $upper;

        if (Numbers::inRange($value, $lower, $upper)) {
            if ($op == 'between') {
                return true;
            } else {
                return false;
            }
        } else {
            if ($op == 'notbetween') {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     * @param string                   $prop_name
     * @param string                   $check_value
     * @param string                   $multi_mode
     *
     * @return bool
     */
    protected function isStringMatch(
        Ticket $ticket,
        ExecutorContextInterface $context,
        $prop_name,
        $check_value = null,
        $multi_mode = null
    ) {
        $opts       = $this->getValueOpArray($ticket, $context, $prop_name);
        $op         = $opts['op'];
        $all_values = $opts['value'];

        if ($opts['is_touched_op']) {
            if ($opts['was_touched']) {
                return $op == 'touched';
            } elseif (!$opts['was_touched']) {
                return $op == 'nottouched';
            }
        }

        if ($opts['is_changed_op'] && !$opts['was_changed']) {
            return false;
        }
        if ($opts['is_changed_op'] && $opts['was_changed'] && $op == 'changed') {
            return true;
        }

        return $this->isStringValuesMatch($op, $all_values, $check_value, $multi_mode);
    }

    /**
     * @param array    $all_values
     * @param callback $check_fn
     * @param string   $op
     * @param string   $multi_mode
     *
     * @return bool
     */
    public function getMultiMatchResult(array $all_values, $check_fn, $op, $multi_mode)
    {
        // No values to check
        if (!$all_values) {
            if ($op == 'not' || $op == 'notcontains' || $op == 'not_regex' || $op == 'not_isset') {
                return true;
            } else {
                return false;
            }
        }

        $match_count = 0;
        $check_count = 0;
        foreach ($all_values as $v) {
            ++$check_count;
            if (call_user_func($check_fn, $v)) {
                ++$match_count;
            }
        }

        // When no mode is provided, we set it based on logic
        // If i want a trigger where PropertyA IS 'abc', then
        // if 'any' of those match, then the trigger should match.
        // Conversely, if I write a trigger where PropertyA IS NOT 'abc',
        // the trigger should match only if 'all' of those dont match
        if ($multi_mode === null) {
            if ($op == 'not' || $op == 'notcontains' || $op == 'not_regex') {
                $multi_mode = 'all';
            } else {
                $multi_mode = 'any';
            }
        }

        if ($match_count) {
            if ($multi_mode == 'all') {
                return $check_count == $match_count;
            } else {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     *
     * @return bool
     */
    abstract public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $context);

    protected function isIntValuesMatch($op, $value, $check_value)
    {
        $value       = (int) $value;
        $check_value = (int) $check_value;

        switch ($op) {
            case self::OP_IS:
                if ($value == $check_value) {
                    return true;
                }
                break;
            case self::OP_NOT:
                if ($value != $check_value) {
                    return true;
                }
                break;
            case self::OP_GT:
                if ($value > $check_value) {
                    return true;
                }
                break;
            case self::OP_GTE:
                if ($value >= $check_value) {
                    return true;
                }
                break;
            case self::OP_LT:
                if ($value < $check_value) {
                    return true;
                }
                break;
            case self::OP_LTE:
                if ($value <= $check_value) {
                    return true;
                }
                break;
        }

        return false;
    }

    protected function isDateValuesMatch($op, $value, $check_value)
    {
        if (!$value) {
            return false;
        }

        if ($check_value === null) {
            return false;
        }

        $value       = $value->getTimestamp();
        $check_value = $check_value->getTimestamp();

        switch ($op) {
            case 'is':
                if ($check_value == $value) {
                    return true;
                }
                break;
            case 'not':
                if ($check_value != $value) {
                    return true;
                }
                break;
            case 'gt':
                if ($check_value > $value) {
                    return true;
                }
                break;
            case 'gte':
                if ($check_value >= $value) {
                    return true;
                }
                break;
            case 'lt':
                if ($check_value < $value) {
                    return true;
                }
                break;
            case 'lte':
                if ($check_value <= $value) {
                    return true;
                }
                break;
        }

        return false;
    }

    protected function isDateRangeValuesMatch($value, $lower, $upper)
    {
        $value = $value->getTimestamp();
        $lower = $lower->getTimestamp();
        $upper = $upper->getTimestamp();

        if (Numbers::inRange($value, $lower, $upper)) {
            return true;
        }

        return false;
    }

    protected function isStringValuesMatch($op, $value, $check_value, $multi_mode = null)
    {
        if ($check_value === null) {
            return false;
        }

        $all_values = $value;

        if (!is_array($all_values)) {
            $all_values = [$all_values];
        }

        $check_value = is_array($check_value) ? $check_value : [$check_value];

        $check_value_i = array_map(
            function ($v) {
                return Strings::utf8_strtolower($v);
            },
            $check_value
        );

        $check_fn = function ($value) use ($op, $check_value_i, $check_value) {
            if (!is_string($value)) {
                if ($value === null || $value === false) {
                    $value = '';
                } else {
                    $value .= '';
                }
            }

            $value_i = Strings::utf8_strtolower($value);
            switch ($op) {
                case AbstractTriggerTerm::OP_ISSET:
                    if ($value_i !== '') {
                        return true;
                    } else {
                        return false;
                    }
                    break;

                case AbstractTriggerTerm::OP_NOTISSET:
                    if ($value_i === '' || $value_i === null || $value_i === false) {
                        return true;
                    } else {
                        return false;
                    }
                    break;

                case AbstractTriggerTerm::OP_IS:
                case AbstractTriggerTerm::OP_NOT:
                    foreach ($check_value_i as $vi) {
                        if ($value_i == $vi) {
                            if ($op == AbstractTriggerTerm::OP_IS) {
                                return true;
                            }
                            if ($op == AbstractTriggerTerm::OP_NOT) {
                                return false;
                            }
                        }
                    }
                    if ($op == AbstractTriggerTerm::OP_NOT) {
                        return true;
                    }
                    break;

                case AbstractTriggerTerm::OP_CONTAINS:
                case AbstractTriggerTerm::OP_NOTCONTAINS:
                    foreach ($check_value_i as $vi) {
                        // Special case: empty search string
                        // We consider 'ticket subject has ""' to be true
                        if ($vi === '') {
                            if ($op == AbstractTriggerTerm::OP_CONTAINS) {
                                return true;
                            } else {
                                return false;
                            }
                        }

                        if (strpos($value_i, $vi) !== false) {
                            if ($op == AbstractTriggerTerm::OP_CONTAINS) {
                                return true;
                            }
                            if ($op == AbstractTriggerTerm::OP_NOTCONTAINS) {
                                return false;
                            }
                        }
                    }
                    if ($op == AbstractTriggerTerm::OP_NOTCONTAINS) {
                        return true;
                    }
                    break;

                case AbstractTriggerTerm::OP_IS_REGEX:
                case AbstractTriggerTerm::OP_NOT_REGEX:
                    foreach ($check_value as $v) {
                        $regex = Strings::getInputRegexPattern($v);

                        if (!$regex) {
                            return false;
                        }

                        if (RegexUtils::safePregMatch($regex, $value)) {
                            if ($op == AbstractTriggerTerm::OP_IS_REGEX) {
                                return true;
                            }
                            if ($op == AbstractTriggerTerm::OP_NOT_REGEX) {
                                return false;
                            }
                        }
                    }
                    if ($op == AbstractTriggerTerm::OP_NOT_REGEX) {
                        return true;
                    }
            }

            return false;
        };

        return $this->getMultiMatchResult($all_values, $check_fn, $op, $multi_mode);
    }
}
