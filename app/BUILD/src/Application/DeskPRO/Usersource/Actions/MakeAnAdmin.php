<?php

namespace Application\DeskPRO\Usersource\Actions;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Person;

class MakeAnAdmin extends AbstractAction
{
    public function getData()
    {
        return null;
    }

    public function setData($value)
    {
    }

    protected function doHandle(DeskproContainer $container, Person $person, array $rawInput)
    {
        $person->setCanAdmin(true);
    }
}
