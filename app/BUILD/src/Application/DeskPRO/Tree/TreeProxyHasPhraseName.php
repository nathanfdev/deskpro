<?php

namespace Application\DeskPRO\Tree;

use Application\DeskPRO\Translate\HasPhraseName;
use Application\DeskPRO\Translate\Translate;

class TreeProxyHasPhraseName extends TreeProxy implements HasPhraseName
{
    /**
     * {@inheritdoc}
     */
    public function getPhraseName($property)
    {
        return $this->__obj->getPhraseName($property);
    }

    /**
     * {@inheritdoc}
     */
    public function getPhraseDefault($property, Translate $translate)
    {
        return $this->__obj->getPhraseDefault($property, $translate);
    }
}
