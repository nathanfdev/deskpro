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

        foreach ($opt as $s) {
            if (!preg_match('#^[a-zA-Z0-9_\-\.]+$#', $s)) {
                continue;
            }
            if (strpos($s, '.') === false) {
                $statuses[] = $s;
            } else {
                list(, $hs)        = explode('.', $s, 2);
                $hidden_statuses[] = $hs;
            }
        }

        if ($statuses) {
            $query->orWhere("status IN ('".implode("','", $statuses).')');
        }
        if ($hidden_statuses) {
            $query->orWhere("(status = 'hidden' AND hidden_status IN ('".implode("','", $hidden_statuses).'))');
        }

        return $query;
    }
}
