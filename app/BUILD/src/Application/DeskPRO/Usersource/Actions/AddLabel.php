<?php

namespace Application\DeskPRO\Usersource\Actions;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\LabelPerson;
use Application\DeskPRO\Entity\Person;

class AddLabel extends AbstractAction
{
    protected $label;

    public function getData()
    {
        return $this->label;
    }

    public function setData($value)
    {
        $this->label = (string) $value;
    }

    protected function getValue(array $data)
    {
        return $this->getData();
    }

    protected function doHandle(DeskproContainer $container, Person $person, array $rawInput)
    {
        $value = $this->getValue($rawInput);
        if (trim($value)) {
            $label = new LabelPerson($value);
            $person->addLabel($label);
        }
    }
}
