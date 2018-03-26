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
 * Filters based on ticket user labels.
 *
 * @option string[] labels
 */
class FilterUserLabels extends AbstractFilterTerm
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

        $query = $this->getStringMatchQuery('user_labels.label', $labels);
        $query->addJoin('tickets.person.labels', 'labels_people', 'user_labels', 'user_labels.person_id = tickets.person_id');

        return $query;
    }
}
