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

/**
 * Checks if SLAs on the ticket match a status.
 * Note that only SLAs that actually exist on the ticket contrinute to the result
 * of this check.
 *
 * @option int[]  sla_ids
 * @option string sla_status
 * @option bool   is_complete
 */
class CheckSlaStatus extends AbstractTriggerTerm
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('sla_ids');
        $options->addValidNames('sla_status', 'is_complete');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $context)
    {
        $options = $this->getTermOptions();

        $map = [];
        foreach ($ticket->ticket_slas as $ticket_sla) {
            $map[$ticket_sla->sla->id] = $ticket_sla;
        }

        $check_status   = $options->get('sla_status');
        $check_complete = $options->get('is_complete');

        $has_any          = false;
        $any_complete     = false;
        $any_match_status = false;
        foreach ($options->get('sla_ids') as $sla_id) {
            if (!isset($map[$sla_id])) {
                continue;
            }

            $ticket_sla = $map[$sla_id];
            $has_any    = true;

            if ($check_status && $ticket_sla->sla_status == $check_status) {
                $any_match_status = true;
            }
            if ($ticket_sla->is_completed) {
                $any_complete = true;
            }
        }

        switch ($this->getTermOperator()) {
            case 'is':
            case 'contains':
                if ($has_any) {
                    if ($check_complete && !$any_complete) {
                        return false;
                    }
                    if ($check_status && !$any_match_status) {
                        return false;
                    }

                    return true;
                } else {
                    return false;
                }
                break;

            case 'not':
            case 'notcontains':
                return !$has_any;
                break;
        }

        return false;
    }
}
