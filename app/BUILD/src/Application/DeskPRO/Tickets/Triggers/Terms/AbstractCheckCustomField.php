<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

use Application\DeskPRO\Entity\CustomDataAbstract;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Application\DeskPRO\Tickets\ExecutorContextVars;
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
        $options->setAliases('field_id', ['field']);

        return $options;
    }


    /**
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     *
     * @return CustomDataAbstract[]
     */
    abstract public function getCustomDataArray(Ticket $ticket, ExecutorContextInterface $context);

    /**
     * Parse the action options and return the field id
     *
     * @param \Orb\Util\OptionsArray $options
     * @param ExecutorContextInterface $context
     * @return string
     */
    protected function resolveFieldId(\Orb\Util\OptionsArray $options, ExecutorContextInterface $context)
    {
        // if the field id is set in the options, used that
        $fieldId = isset($options['field_id']) ? $options['field_id'] : null;
        if (! empty($fieldId)) {
            return $fieldId;
        }

        // let's see if we have a field alias
        $fieldId = isset($options['field']) ? $options['field'] : null;
        if (!empty($fieldId)) {
            $fieldDef = null;
            $fieldManager = ExecutorContextVars::getTicketFieldManagerFromContext($context);
            if ($fieldManager) {
                $fieldDef = $fieldManager->getFieldFromId($fieldId);
            }

            if (empty($fieldDef)) {
                return null;
            }
            return $fieldDef->getId();
        }

        return null;
    }


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

        $customDataArray = $this->getCustomDataArray($ticket, $context);

        $fieldId   = $this->resolveFieldId($options, $context);
        $field     = null;
        $fieldData = null;

        foreach ($customDataArray as $customData) {
            $field = $customData->getField();


            if ($customData->getField()->getId() == $fieldId) {
                $fieldData = $customData->getData();
                $field     = $customData->getField();
                break;
            } elseif ($customData->getField()->getParent() && $customData->getField()->getParent()->getId() == $fieldId) {
                $field     = $customData->getField()->getParent();
                $fieldData = [];
                break;
            }
        }

        if ($field && $field->isChoiceType()) {
            foreach ($customDataArray as $customData) {
                if ($customData->getField()->getParent() && $customData->getField()->getParent()->getId() == $fieldId) {
                    $fieldData[] = $customData->getField()->getId();
                }
            }
        }

        //------------------------------
        // Check for existence
        //------------------------------

        if ($op == 'isset') {
            return (bool) $fieldData;
        } elseif ($op == 'not_isset') {
            return !((bool) $fieldData);
        } elseif ('touched' === $op || 'nottouched' === $op) {
            return $this->isStringMatch($ticket, $context, 'custom_data.'.$fieldId, $options->get('value'));
        }

        if (!$fieldData) {
            $testValue = $options->get('value');

            if (ctype_digit($testValue)) {
                $fieldData = 0;

                return $this->isIntMatch($ticket, $context, TermValue::createWithValue($fieldData), $options->get('value'));
            } else {
                $fieldData = '';

                return $this->isStringMatch($ticket, $context, TermValue::createWithValue($fieldData), $options->get('value'));
            }
        }

        //------------------------------
        // Handle choice check
        //------------------------------

        if ($field->isChoiceType()) {
            // prevent db collisions
            if (is_scalar($fieldData)) {
                return false;
            }

            $checkValue = $options->get('value');
            $checkIds   = array_fill_keys($fieldData, true);
            if (!is_array($checkValue)) {
                $checkValue = [$checkValue];
            }

            $has = false;
            foreach ($checkValue as $v) {
                if (isset($checkIds[$v])) {
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
            return $this->isIntMatch($ticket, $context, TermValue::createWithValue($fieldData), (int) $options->get('value'));

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
                $value = new \DateTime('@'.$fieldData);
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
            return $this->isStringMatch($ticket, $context, TermValue::createWithValue($fieldData), $options->get('value'));
        }
    }

    /**
     * @return string
     */
    public function getTermType()
    {
        return 'CheckTicketField'.$this->getTermOptions()->get('field_id');
    }

    /**
     * note: this method is only used in TicketLayout terms
     * eg app/BUILD/src/Application/DeskPRO/TicketLayout/Terms/CheckTicketField.php.
     *
     * @return string
     */
    public function compileJsCheck()
    {
        $options     = $this->getTermOptions();
        $op          = $this->getTermOperator();
        $id          = $options['field_id'];
        $check_value = $options->get('value');
        $type        = $options->get('type_name');
        $value       = $this->getTicketFieldValueJs($id);

        if ($op === AbstractTriggerTerm::OP_ISSET) {
            if ($type === 'toggle') {
                return "function (ticket) { return !!$value; }";
            } else {
                return "function (ticket) { return !!($value ? ($value).length : $value); }";
            }
        } elseif ($op === AbstractTriggerTerm::OP_NOTISSET) {
            if ($type === 'toggle') {
                return "function (ticket) { return !$value; }";
            } else {
                return "function (ticket) { return !$value || 0 === ($value).length; }";
            }
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
  var value = $value || null;
  var op = '$op';
  if (!value) {
    value = [];
  } else if (typeof value === 'string') {
    value = (value+'').split(',');
  }
  
  var has = false; 
  for (var i = 0; i < check_value.length; i++) {
    if ((Array.isArray(value) && value.indexOf(check_value[i] + '') !== -1) || value + '' === check_value[i] + '') has = true;
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
                $i1    = 0;
                $i2    = 0;
                if ($options['date1']) {
                    $date1 = $options['date1'] * 1000;
                    $date1 = "new Date($date1)";
                } elseif ($options['date1_relative']) {
                    $i1   = \DateInterval::createFromDateString($options['date1_relative'].' '.$options['date1_relative_type']);
                    $date = new \DateTime('@0');
                    $i1   = $date->add($i1)->getTimestamp() * 1000;
                }
                if ($options['date2']) {
                    $date2 = $options['date2'] * 1000;
                    $date2 = "new Date($date2)";
                } elseif ($options['date2_relative']) {
                    $i2   = \DateInterval::createFromDateString($options['date2_relative'].' '.$options['date2_relative_type']);
                    $date = new \DateTime('@0');
                    $i2   = $date->add($i2)->getTimestamp() * 1000;
                }

                $date1 = $date1 ?: 'null';
                $date2 = $date2 ?: 'null';

                return <<<JS
function (ticket) {
  var date1 = $date1;
  var date2 = $date2;
  var i1 = $i1;
  var i2 = $i2;
  date1 = date1 ? date1.getTime() : null;
  date2 = date2 ? date2.getTime() : null;
  if (!date1 && i1) {
    date1 = Date.now() - i1;
  }
  if (!date2 && i2) {
    date2 = Date.now() - i2;
  }
   
  var op = '$op';
  var value = $value;
  if (!value) {
    return false;
  }
  var parts = (value + '').trim().split(' ');
  if (!parts[0]) return false;
  value = parts[0].split('-');
  if (!value.length || value.length !== 3) return false;
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
      return Math.min(date1, date2) <= value && Math.max(date1, date2) >= value; 
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
