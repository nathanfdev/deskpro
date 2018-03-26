<?php

namespace DeskPRO\Component\FilterQueryLanguage\Query\Node;

use DeskPRO\Component\FilterQueryLanguage\Query\QueryPart;

abstract class Node extends QueryPart
{
    const TYPE = 'ABSTRACT_NODE';

    /**
     * @return array
     */
    abstract public function toArray();

    /**
     * @return string
     */
    public function getType()
    {
        return static::TYPE;
    }
}
