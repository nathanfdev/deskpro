<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\CheckedOptionsArray;
use Orb\Util\Util;

/**
 * Checks the value of a ticket field.
 *
 * @option int field_id   The field to check
 * @option mixed value    The value. For choice, this will be multiple ints. For others, it will be a string.
 */
abstract class AbstractCheckCustomField extends AbstractTriggerTerm
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('field_id', 'value');

        return $options;
    }

    /**
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     *
     * @return array
     */
    abstract public function getCustomDataArray(Ticket $ticket, ExecutorContextInterface $context);

    /**
     * {@inheritdoc}
     */
    public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $context)
    {
        $options = $this->getTermOptions();
        $op      = $this->getTermOperator();

        //------------------------------
        // Get the field value
        //------------------------------

        $custom_data_array = $this->getCustomDataArray($ticket, $context);

        $field_id   = $options->get('field_id');
        $field      = null;
        $field_data = null;

        foreach ($custom_data_array as $custom_data) {
            if ($custom_data->field->id == $field_id) {
                $field_data = $custom_data->getData();
                $field      = $custom_data->field;
                break;
            } elseif ($custom_data->field->parent && $custom_data->field->parent->id == $field_id) {
                $field      = $custom_data->field->parent;
                $field_data = [];
                break;
            }
        }

        if ($field && $field->getTypeName() == 'choice') {
            foreach ($custom_data_array as $custom_data) {
                if ($custom_data->field->parent and $custom_data->field->parent->id == $field_id) {
                    $field_data[] = $custom_data->field->id;
                }
            }
        }

        //------------------------------
        // Check for existence
        //------------------------------

        if ($op == 'isset') {
            return (bool) $field_data;
        } elseif ($op == 'not_isset') {
            return !((bool) $field_data);
        } elseif ('touched' === $op || 'nottouched' === $op) {
            return $this->isStringMatch($ticket, $context, 'custom_data.'.$field_id, $options->get('value'));
        }

        if (!$field_data) {
            $test_value = $options->get('value');

            if (ctype_digit($test_value)) {
                $field_data = 0;

                return $this->isIntMatch($ticket, $context, TermValue::createWithValue($field_data), $options->get('value'));
            } else {
                $field_data = '';

                return $this->isStringMatch($ticket, $context, TermValue::createWithValue($field_data), $options->get('value'));
            }
        }

        //------------------------------
        // Handle choice check
        //------------------------------

        if ($field->getTypeName() == 'choice') {
            $check_value = $options->get('value');
            $check_ids   = array_fill_keys($field_data, true);
            if (!is_array($check_value)) {
                $check_value = [$check_value];
            }

            $has = false;
            foreach ($check_value as $v) {
                if (isset($check_ids[$v])) {
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

        //------------------------------
        // Handle toggle
        //------------------------------
        } elseif ($field->getTypeName() == 'toggle') {
            return $this->isIntMatch($ticket, $context, TermValue::createWithValue($field_data), (int) $options->get('value'));

        //------------------------------
        // Handle dates
        //------------------------------
        } elseif ($field->getTypeName() === 'date' || $field->getTypeName() === 'datetime') {

            /*
             * cp from CheckDateCreated
             */

            $opts  = $this->getTermOptions();
            $date1 = null;
            $date2 = null;

            try {
                if ($opts['date1']) {
                    $date1 = new \DateTime('@'.$opts['date1']);
                } elseif ($opts['date1_relative']) {
                    $date1 = new \DateTime('@'.@strtotime('-'.$opts['date1_relative'].' '.$opts->get('date1_relative_type', 'days')));
                } else {
                    $date1 = null;
                }
            } catch (\Exception $e) {
                $date1 = null;
            }

            try {
                if ($opts['date2']) {
                    $date2 = new \DateTime('@'.$opts['date2']);
                } elseif ($opts['date2_relative']) {
                    $date2 = new \DateTime('@'.@strtotime('-'.$opts['date2_relative'].' '.$opts->get('date2_relative_type', 'days')));
                } else {
                    $date2 = null;
                }
            } catch (\Exception $e) {
                $date2 = null;
            }

            try {
                $value = new \DateTime('@'.$field_data);
            } catch (\Exception $e) {
                $value = null;
            }

            switch ($this->getTermOperator()) {
                case 'lt':
                case 'lte':
                case 'gt':
                case 'gte':
                    if (!$date1 && !$date2) {
                        return false;
                    }

                    $d = TermValue::createWithValue(Util::coalesce($date1, $date2));

                    return $this->isDateMatch($ticket, $context, $d, $value);

                case 'between':
                    if (!$date1 || !$date2) {
                        return false;
                    }

                    return $this->isDateRangeMatch($ticket, $context, TermValue::createWithValue($value), $date1, $date2);

                default:
                    return false;
            }

        //------------------------------
        // Handle text check
        //------------------------------
        } else {
            return $this->isStringMatch($ticket, $context, TermValue::createWithValue($field_data), $options->get('value'));
        }
    }

    /**
     * @return string
     */
    public function getTermType()
    {
        return 'CheckTicketField'.$this->getTermOptions()->get('field_id');
    }

    public function compileJsCheck()
    {
        $options     = $this->getTermOptions();
        $op          = $this->getTermOperator();
        $id          = $options['field_id'];
        $check_value = $options->get('value');
        $type        = $options->get('type_name');
        $value       = $this->getTicketFieldValueJs($id);

        if ($op === AbstractTriggerTerm::OP_ISSET) {
            return "function (ticket) { return !!$value; }";
        } elseif ($op === AbstractTriggerTerm::OP_NOTISSET) {
            return "function (ticket) { return !$value; }";
        }

        $op_is          = AbstractTriggerTerm::OP_IS;
        $op_not         = AbstractTriggerTerm::OP_NOT;
        $op_lt          = AbstractTriggerTerm::OP_LT;
        $op_lte         = AbstractTriggerTerm::OP_LTE;
        $op_gt          = AbstractTriggerTerm::OP_GT;
        $op_gte         = AbstractTriggerTerm::OP_GTE;
        $op_btw         = AbstractTriggerTerm::OP_BETWEEN;
        $op_contains    = AbstractTriggerTerm::OP_CONTAINS;
        $op_notcontains = AbstractTriggerTerm::OP_NOTCONTAINS;
        $op_reg         = AbstractTriggerTerm::OP_IS_REGEX;
        $op_notreg      = AbstractTriggerTerm::OP_NOT_REGEX;

        switch ($type) {
            case 'choice':
                if (!is_array($check_value)) {
                    $check_value = [$check_value];
                }
                foreach ($check_value as &$v) {
                    $v = (int) $v;
                }
                $check_value = json_encode($check_value);

                return <<<JS
function (ticket) { 
  var check_value = $check_value;
  var value = parseInt($value) || null;
  var op = '$op';
  if (!value || !value.length) value = [value];
  
  var has = false; 
  for (var i = 0; i < check_value.length; i++) {
    if (value.indexOf(check_value[i]) !== -1) has = true;
  }
  
  if (op === '$op_is' && has) return true;
  if (op === '$op_not' && !has) return true;
  return false;
}
JS;
            case 'date':
            case 'datetime':
                $date1 = null;
                $date2 = null;
                if ($options['date1']) {
                    $date1 = $options['date1'] * 1000;
                    $date1 = "new Date($date1)";
                } elseif ($options['date1_relative']) {
                    // todo
                }
                if ($options['date2']) {
                    $date2 = $options['date2'] * 1000;
                    $date2 = "new Date($date2)";
                } elseif ($options['date2_relative']) {
                    // todo
                }

                $date1 = $date1 ?: 'null';
                $date2 = $date2 ?: 'null';

                return <<<JS
function (ticket) {
  var date1 = $date1;
  var date2 = $date2;
  date1 = date1 ? date1.getTime() : null;
  date2 = date2 ? date2.getTime() : null;
  var op = '$op';
  var value = $value;
  if (!value || !value.length) {
    return false;
  }
  value = new Date(parseInt(value[0]), parseInt(value[1]) - 1, parseInt(value[2]));
  value = value.getTime();
  if (!date1 && !date2) return false;

  switch (op) {
    case '$op_lt':
    case '$op_lte':
      return value < (date2 || date1);
    case '$op_gt':
    case '$op_gte':
      return value > (date2 || date1);
    case '$op_btw':
      if (!date1 || !date2) return false;
      return Math.min(date1, date2) >= value && value <= Math.max(date1, date2); 
  }
  
  return false;
}
JS;
            default:
                return <<<JS
function (ticket) { 
  var check_value = '$check_value'.toLowerCase();
  var value = $value || '';
  value = value.toLowerCase();
  var op = '$op';
  
  switch (op) {
    case '$op_is':
      return !value.localeCompare(check_value);
    case '$op_not':
      return !!value.localeCompare(check_value);
    case '$op_contains':
      return value.indexOf(check_value) !== -1;
    case '$op_notcontains':
      return value.indexOf(check_value) === -1;
    case '$op_reg':
    case '$op_notreg':
      if (check_value.charAt(0) === '/') {
        check_value = check_value.substr(1);
      }
      if (check_value.charAt(check_value.length - 1) === '/') {
        check_value = check_value.substr(0, check_value.length - 1);
      }
      var patt = new RegExp(check_value);
      if (op === '$op_reg') {
        return patt.test(value);
      }
      if (op === '$op_notreg') {
        return !patt.test(value);
      }
      return false;
  }
 
  return false;
}
JS;
        }
    }
}
