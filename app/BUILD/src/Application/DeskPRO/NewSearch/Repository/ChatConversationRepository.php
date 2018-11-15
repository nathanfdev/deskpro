<?php

namespace Application\DeskPRO\NewSearch\Repository;

use Elastica\Query;

/**
 * Person Repository.
 */
class ChatConversationRepository extends AbstractRepository implements WithLabelsInterface
{
    /**
     * The currently logged in person.
     *
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

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
        $mainFilter = new Query\BoolQuery();
        $mainFilter->addMust(new Query\Term(['is_agent' => false]));

        $assignFilter = new Query\BoolQuery();
        $assignFilter->addShould(new Query\Term(['agent' => $this->person->getId()]));
        $assignFilter->addShould(new Query\Term(['participants' => $this->person->getId()]));

        $mainFilter->addMust($assignFilter);

        $this->person->loadHelper('AgentPermissions');

        $allowedDepIds = $this->person->getAllowedDepartments('chat');
        $allowedDepIds = array_map(function ($id) {
            return (int) $id;
        }, $allowedDepIds);

        if (!$allowedDepIds
            || (!$this->person->hasPerm('agent_chat.view_unassigned') && !$this->person->hasPerm('agent_chat.view_others'))
        ) {
            // cant see anything else
        } else {
            $subFilter = new Query\BoolQuery();
            if ($allowedDepIds) {
                $subFilter->addMust(new Query\Terms('department', $allowedDepIds));
            }
            if (!$this->person->hasPerm('agent_chat.view_unassigned')) {
                $subFilter->addMustNot(new Query\Term(['agent' => 0]));
            }
            if (!$this->person->hasPerm('agent_chat.view_others')) {
                $subFilter->addMustNot(new Query\Range('agent', ['gt' => 0]));
            }

            $assignFilter->addShould($subFilter);
        }

        return $mainFilter->toArray();
    }
}
