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

class CheckTicketField extends \Application\DeskPRO\Tickets\Triggers\Terms\CheckTicketField implements TicketLayoutTermInterface
{
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

        switch ($op) {
            case AbstractTriggerTerm::OP_IS:
                return is_array($submitted) ? in_array($value, $submitted, 1) : $value === $submitted;
            case AbstractTriggerTerm::OP_NOT:
                return is_array($submitted) ? !in_array($value, $submitted, 1) : $value !== $submitted;
            case AbstractTriggerTerm::OP_ISSET:
                return (bool) $submitted;
            case AbstractTriggerTerm::OP_NOTISSET:
                return !$submitted;
            case AbstractTriggerTerm::OP_CONTAINS:
                if (is_array($submitted)) {
                    foreach ($submitted as $sub) {
                    }
                }

                return is_array($submitted) ? in_array($value, $submitted, 1) : $value === $submitted;
            case AbstractTriggerTerm::OP_NOTCONTAINS:
                return is_array($submitted) ? !in_array($value, $submitted, 1) : $value !== $submitted;
            case AbstractTriggerTerm::OP_IS_REGEX:
                break;
            case AbstractTriggerTerm::OP_NOT_REGEX:
                break;
        }

        $a = 1;

        return false;
    }
}
