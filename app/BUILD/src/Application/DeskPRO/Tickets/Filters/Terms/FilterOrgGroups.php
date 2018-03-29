<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Filters\Terms;

use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\CheckedOptionsArray;

/**
 * Filters based on ticket org usergroups.
 *
 * @option int[] group_ids
 */
class FilterOrgGroups extends AbstractFilterTerm
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('group_ids');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterQuery(ExecutorContextInterface $context = null)
    {
        $options = $this->getTermOptions();

        $query = $this->getStringMatchQuery('org_groups.usergroup_id', $options['group_ids']);
        $query->addJoin('tickets.organization.usergroups', 'organization2usergroups', 'org_groups', 'org_groups.organization_id = tickets.organization_id');

        return $query;
    }
}
