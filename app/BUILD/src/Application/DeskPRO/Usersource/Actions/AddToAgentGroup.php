<?php

namespace Application\DeskPRO\Usersource\Actions;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Person;

class AddToAgentGroup extends AbstractAction
{
    protected $groupId;

    public function getData()
    {
        return $this->groupId;
    }

    public function setData($value)
    {
        if (!is_numeric($value)) {
            throw new \Exception('Invalid data type for AddToAgentGroup usersource action');
        }

        $this->groupId = (int) $value;
    }

    protected function doHandle(DeskproContainer $container, Person $person, array $rawInput)
    {
        /** @var $agentGroupsHelper */
        $agentGroupsHelper = $container->getAgentGroups();
        $group             = $agentGroupsHelper->getGroup($this->getData());
        $person->addUsergroup($group);
    }
}
