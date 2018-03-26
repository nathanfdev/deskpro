<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Filters\Terms;

use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Application\DeskPRO\Util as DeskPROUtil;
use Orb\Util\CheckedOptionsArray;

/**
 * Filters based on ticket labels.
 *
 * @option string[] labels
 */
class FilterLabels extends AbstractFilterTerm
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('labels');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterQuery(ExecutorContextInterface $context = null)
    {
        $options = $this->getTermOptions();

        $labels = DeskPROUtil::labelsArrayFromString($options['labels']);

        $query = $this->getStringMatchQuery('tickets', $labels);
        $query->addJoin('tickets.labels', 'labels_tickets', 'labels', 'labels.ticket_id = tickets.id');

        return $query;
    }
}
