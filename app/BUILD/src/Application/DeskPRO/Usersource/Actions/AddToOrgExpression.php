<?php

namespace Application\DeskPRO\Usersource\Actions;

class AddToOrgExpression extends AddToOrg
{
    /**
     * @param array $data
     *
     * @return string
     */
    protected function getValue(array $data)
    {
        return $this->evaluate($this->getData(), ['user' => $data]);
    }
}
