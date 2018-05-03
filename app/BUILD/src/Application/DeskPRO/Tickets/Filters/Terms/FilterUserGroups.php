<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Filters\Terms;

use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\CheckedOptionsArray;

class FilterUserGroups extends AbstractFilterTerm
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

        $query = $this->getIdMatchQuery('user_groups.id', $options['group_ids']);
        $query->addJoin('tickets.person.groups', 'person2usergroups', 'user_groups', 'user_groups.person_id = tickets.person_id');

        return $query;
    }
}
