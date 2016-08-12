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

namespace Application\DeskPRO\TicketLayout\Terms;

use Application\DeskPRO\Tickets\Triggers\Terms\AbstractTriggerTerm;
use DeskPRO\Bundle\AppBundle\Form\FormFields;
use DeskPRO\Bundle\AppBundle\Form\Type\CustomFields\CustomDataType;

class CheckTicketField extends CheckCustomField
{
    /**
     * {@inheritdoc}
     */
    public function compileJsCheck()
    {
        $options     = $this->getTermOptions();
        $op          = $this->getTermOperator();
        $id          = $options['field_id'];
        $check_value = $options->get('value');
        $type        = $options->get('type_name');

        if ($op === AbstractTriggerTerm::OP_ISSET) {
            return "function (ticket) { return !!ticket.getTicketFieldValue($id); }";
        } elseif ($op === AbstractTriggerTerm::OP_NOTISSET) {
            return "function (ticket) { return !ticket.getTicketFieldValue($id); }";
        }

        $op_is  = AbstractTriggerTerm::OP_IS;
        $op_not = AbstractTriggerTerm::OP_NOT;
        $op_lt  = AbstractTriggerTerm::OP_LT;
        $op_lte = AbstractTriggerTerm::OP_LTE;
        $op_gt  = AbstractTriggerTerm::OP_GT;
        $op_gte = AbstractTriggerTerm::OP_GTE;
        $op_btw = AbstractTriggerTerm::OP_BETWEEN;

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
  var value = parseInt(ticket.getTicketFieldValue($id)) || null;
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
                try {
                    if ($options['date1']) {
                        $date1 = new \DateTime('@'.$options['date1']);
                    } elseif ($options['date1_relative']) {
                        $date1 = new \DateTime('@'.@strtotime('-'.$options['date1_relative'].' '.$options->get('date1_relative_type', 'days')));
                    } else {
                        $date1 = null;
                    }
                } catch (\Exception $e) {
                    $date1 = null;
                }

                try {
                    if ($options['date2']) {
                        $date2 = new \DateTime('@'.$options['date2']);
                    } elseif ($options['date2_relative']) {
                        $date2 = new \DateTime('@'.@strtotime('-'.$options['date2_relative'].' '.$options->get('date2_relative_type', 'days')));
                    } else {
                        $date2 = null;
                    }
                } catch (\Exception $e) {
                    $date2 = null;
                }

                $date1 = $date1 ? 'new Date('.$date1->getTimestamp().'000)' : 'null';
                $date2 = $date2 ? 'new Date('.$date2->getTimestamp().'000)' : 'null';

                return <<<JS
function (ticket) {
  var date1 = $date1;
  var date2 = $date2;
  var op = '$op';
  var value = ticket.getTicketFieldValue($id);
  if (!value || !value.length) {
    return false;
  }
  value = new Date(value[0], value[1], value[2]);

  switch (op) {
    case '$op_lt':
    
  }
  
  return false;
}
JS;
        }
    }

    protected function getSubmittedData(array $data)
    {
        $options = $this->getTermOptions();

        return @$data[FormFields::TICKET_FIELD.'_'.$options['field_id']][CustomDataType::KEY];
    }
}
