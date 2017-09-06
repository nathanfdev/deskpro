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

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use DeskPRO\Bundle\AppBundle\Webhooks\TicketWebhookVars\ExecutorContextVars;
use Orb\Util\CheckedOptionsArray;

/**
 * Checks the value of a user var.
 *
 * @option string name  The name of the user var
 * @option string value Value to check for (not used for isset/notisset)
 */
class CheckWebhookVar extends AbstractTriggerTerm
{
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('name');
        $options->addValidNames('value');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $context)
    {
        $options = $this->getTermOptions();
        $name = $options->get('name');
        if (!$name) {
            return false;
        }

        $valueExists = ExecutorContextVars::exists($context, $name);
        if ($this->getTermOperator() == 'not_isset') {
            return !$valueExists;
        } else if ($this->getTermOperator() == 'isset') {
            return $valueExists;
        } else if (!$valueExists) {
            return false;
        }

        $value = ExecutorContextVars::get($context, $name);
        return $this->isStringMatch($ticket, $context, TermValue::createWithValue($value), $options['value']);
    }
}
