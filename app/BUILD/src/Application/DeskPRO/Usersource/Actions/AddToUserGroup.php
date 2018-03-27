<?php

namespace Application\DeskPRO\Usersource\Actions;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Person;

class AddToUserGroup extends AbstractAction
{
    protected $groupId;

    public function getData()
    {
        return $this->groupId;
    }

    public function setData($value)
    {
        if (!is_numeric($value)) {
            throw new \Exception('Invalid data type for AddToUserGroup usersource action');
        }

        $this->groupId = (int) $value;
    }

    protected function doHandle(DeskproContainer $container, Person $person, array $rawInput)
    {
        $userGroupsHelper = $container->getUserGroups();
        $group            = $userGroupsHelper->getGroup($this->getData());
        $person->addUsergroup($group);
    }
}
