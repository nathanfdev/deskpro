<?php

namespace DeskPRO\Bundle\AppBundle\Limits\Model;

class GlobalLimit extends AbstractLimit
{
    public function getType()
    {
        return AbstractLimit::TYPE_GLOBAL;
    }
}
