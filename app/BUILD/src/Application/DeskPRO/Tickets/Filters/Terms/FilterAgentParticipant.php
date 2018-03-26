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
 * Filters based on followers.
 *
 * @option int[] agent_ids
 */
class FilterAgentParticipant extends AbstractFilterTerm
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('agent_ids');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterQuery(ExecutorContextInterface $context = null)
    {
        $query = new FilterQuery();

        $query->addJoin('tickets.organization.labels', 'labels_organizations', 'org_labels', 'org_labels.organization_id = tickets.organization_id');
        $query->addJoin('tickets.participants', 'tickets_participants', 'unique:parts', 'parts.ticket_id = tickets.id');
        $query->andWhereIn('parts.person_id', $this->getTermOptions()->get('agent_ids'), $this->getTermOperator() == self::OP_NOT);
    }
}
