<?php

namespace DeskPRO\Bundle\AppBundle\Limits\Model;

class KeyLimit extends AbstractLimit
{
    public function getType()
    {
        return AbstractLimit::TYPE_KEY;
    }
}
