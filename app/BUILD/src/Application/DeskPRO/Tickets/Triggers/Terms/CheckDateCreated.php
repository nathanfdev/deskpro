<?php

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
 * Checks when ticket was created.
 *
 * @option int date1
 * @option int date2
 * @option string date1_relative
 * @option string date2_relative
 */
class CheckDateCreated extends AbstractTriggerTerm
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addValidNames('date1', 'date2', 'date1_relative', 'date2_relative', 'date1_relative_type', 'date2_relative_type', 'value');
        // 'value' is a UI artefact; it is not actually used

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $context)
    {
        $opts = $this->getTermOptions();

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

        switch ($this->getTermOperator()) {
            case 'lt':
            case 'lte':
            case 'gt':
            case 'gte':
                if (!$date1 && !$date2) {
                    return false;
                }

                $d = Util::coalesce($date1, $date2);

                return $this->isDateMatch($ticket, $context, 'date_created', $d);

            case 'between':
                if (!$date1 || !$date2) {
                    return false;
                }

                return $this->isDateRangeMatch($ticket, $context, 'date_created', $date1, $date2);

            default:
                return false;
        }
    }
}
