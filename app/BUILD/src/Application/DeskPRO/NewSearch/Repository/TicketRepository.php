<?php

namespace Application\DeskPRO\NewSearch\Repository;

use Elastica\Query;

/**
 * Ticket Repository.
 */
class TicketRepository extends AbstractRepository implements WithLabelsInterface
{
    /**
     * The currently logged in person.
     *
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    protected $highlightFields = [
        'subject'  => ['fragment_size' => 100],
        'messages' => ['fragment_size' => 100, 'number_of_fragments' => 1],
    ];

    /**
     * {@inheritdoc}
     */
    protected function getSource()
    {
        return ['excludes' => ['attachment']];
    }

    /**
     * {@inheritdoc}
     */
    protected function getQueryFields()
    {
        return ['_all', 'attachments'];
    }

    /**
     * Sets the person context.
     *
     * @param $person
     */
    public function setPersonContext($person)
    {
        $this->person = $person;
    }

    /**
     * {@inheritdoc}
     */
    protected function getFilters(array $options = [])
    {
        // See app/src/Application/DeskPRO/Searcher/TicketSearch.php
        // Re-creating permission logic via filters

        $mainFilter = new Query\BoolQuery();

        $assignedFilter = new Query\BoolQuery();
        $assignedFilter->addShould(new Query\Term(['agent' => $this->person->getId()]));

        $this->person->loadHelper('Agent');
        $teamIds = $this->person->getHelper('Agent')->getTeamIds();
        if ($teamIds) {
            $assignedFilter->addShould(new Query\Terms('agent_team', $teamIds));
        }

        $mainFilter->addShould($assignedFilter);
        $this->person->loadHelper('AgentPermissions');

        if (!$this->person->getAllowedDepartments()
            || (!$this->person->hasPerm('agent_tickets.view_unassigned') && !$this->person->hasPerm('agent_tickets.view_others'))
        ) {
            // cant see anything else
        } else {
            $subFilter = new Query\BoolQuery();
            $any       = false;

            $disDepIds = $this->person->getDisallowedDepartments();
            if ($disDepIds) {
                $subFilter->addMustNot(new Query\Terms('department', $disDepIds));
                $any = true;
            }

            if (!$this->person->hasPerm('agent_tickets.view_unassigned')) {
                $setAgentQuery = new Query\BoolQuery();
                $setAgentQuery->addMustNot(new Query\Term(['agent' => 0]));

                $setAgentTeamQuery = new Query\BoolQuery();
                $setAgentTeamQuery->addMustNot(new Query\Term(['agent_team' => 0]));

                $not_assigned = new Query\BoolQuery();
                $not_assigned->addShould($setAgentQuery);
                $not_assigned->addShould($setAgentTeamQuery);
                $subFilter->addMust($not_assigned);
                $any = true;
            }

            if (!$this->person->hasPerm('agent_tickets.view_others')) {
                $subFilter->addMustNot(new Query\Range('agent', ['gt' => 0]));
                $subFilter->addMustNot(new Query\Range('agent_team', ['gt' => 0]));
                $any = true;
            }

            // If user has all perms, then no filters are applied
            // and the BoolAnd filter will be empty
            if (!$any) {
                return [];
            }

            $mainFilter->addShould($subFilter);
        }

        return $mainFilter->toArray();
    }
}
