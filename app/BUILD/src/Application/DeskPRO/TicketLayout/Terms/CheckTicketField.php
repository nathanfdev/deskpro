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

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContext;
use Application\DeskPRO\Tickets\Triggers\Terms\AbstractTriggerTerm;
use DeskPRO\Bundle\AppBundle\Form\FormFields;
use DeskPRO\Bundle\AppBundle\Form\Type\CustomFields\CustomDataType;
use Orb\Util\CheckedOptionsArray;

class CheckTicketField extends \Application\DeskPRO\Tickets\Triggers\Terms\CheckTicketField implements TicketLayoutTermInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('field_id', 'value', 'type_name');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function compileJsCheck()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function isTicketMatch(Ticket $ticket)
    {
        $context = new ExecutorContext();

        return $this->isTriggerMatch($ticket, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function isSubmittedDataMatch(array $data)
    {
        $options   = $this->getTermOptions();
        $op        = $this->getTermOperator();
        $submitted = @$data[FormFields::TICKET_FIELD.'_'.$options->get('field_id')][CustomDataType::KEY];
        $value     = $options->get('value');
        $type      = $options->get('type_name');

        if ($op === AbstractTriggerTerm::OP_ISSET) {
            return (bool) $submitted;
        } elseif ($op === AbstractTriggerTerm::OP_NOTISSET) {
            return !$submitted;
        }

        switch ($type) {
            case 'choice':
                if (!is_array($value)) {
                    $value = [$value];
                }
                $check_ids = array_fill_keys($value, true);
                $has       = false;
                foreach ($value as $v) {
                    if (isset($check_ids[$v])) {
                        $has = true;
                        break;
                    }
                }

                if ($op === AbstractTriggerTerm::OP_IS && $has) {
                    return true;
                }

                if ($op === AbstractTriggerTerm::OP_NOT && !$has) {
                    return true;
                }

                return false;

            case 'toggle':
                break;
            case 'date':
            case 'datetime':
                break;
            case 'text':
                break;
            default:
        }

        $a = 1;

        return false;
    }
}
