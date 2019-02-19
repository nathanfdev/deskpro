<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Filters\Terms;

use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\Arrays;
use Orb\Util\CheckedOptionsArray;

/**
 * Filters based on status.
 *
 * @option string|string[] status
 */
class FilterStatus extends AbstractFilterTerm
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('status');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterQuery(ExecutorContextInterface $context = null)
    {
        $opt = $this->getTermOptions()->get('status');
        if (!is_array($opt)) {
            $opt = [$opt];
        }

        $opt = array_unique($opt);
        $opt = Arrays::removeFalsey($opt);

        $query = new FilterQuery();

        if (!$opt) {
            $query->andWhere('0');
        }

        $statuses        = [];
        $hidden_statuses = [];
        $join_statuses   = [];

        foreach ($opt as $s) {
            if (!preg_match('#^[a-zA-Z0-9_\-\.]+$#', $s)) {
                continue;
            }
            if (strpos($s, '.') === false) {
                $statuses[] = $s;
            } else {
                list($statusType, $hs) = explode('.', $s, 2);
                if (is_numeric($hs)) {
                    $hidden_statuses[] = [$statusType, $hs];
                } else {
                    // fallback to hidden.deleted | hidden.spam
                    $join_statuses[] = [$statusType, $hs];
                }
            }
        }

        if ($statuses) {
            $query->orWhere("tickets.status IN ('".implode("','", $statuses)."')");
        }
        foreach ($hidden_statuses as $item) {
            $query->orWhere(sprintf("tickets.status = '%s' AND tickets.ticket_status_id = %s", $item[0], $item[1]));
        }
        if ($join_statuses) {
            $query->addJoin('tickets.ticket_status', 'ticket_statuses', 'ticket_statuses', 'ticket_statuses.id = tickets.ticket_status_id');
            foreach ($join_statuses as $item) {
                $query->orWhere(sprintf("tickets.status = '%s' AND ticket_statuses.sys_id = '%s'", $item[0], $item[1]));
            }
        }

        return $query;
    }
}
