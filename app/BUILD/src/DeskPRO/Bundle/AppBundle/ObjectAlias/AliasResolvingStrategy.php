<?php

namespace DeskPRO\Bundle\AppBundle\ObjectAlias;

interface AliasResolvingStrategy
{
    /**
     * @param string $alias
     *
     * @return string|null
     */
    public function resolve($alias);
}
