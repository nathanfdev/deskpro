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
 * Filters based on ticket org labels.
 *
 * @option string[] labels
 */
class FilterOrgLabels extends AbstractFilterTerm
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

        $query = $this->getStringMatchQuery('org_labels.label', $labels);
        $query->addJoin('tickets.organization.labels', 'labels_organizations', 'org_labels', 'org_labels.organization_id = tickets.organization_id');

        return $query;
    }
}
