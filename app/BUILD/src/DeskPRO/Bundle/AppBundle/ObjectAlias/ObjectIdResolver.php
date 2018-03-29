<?php

namespace DeskPRO\Bundle\AppBundle\ObjectAlias;

interface ObjectIdResolver
{
    /**
     * Returns the fully qualified class name of the type of objects handled by this resolver.
     *
     * @return string
     */
    public function getObjectType();

    /**
     * Returns the id of the object referenced by the unqualified $alias.
     *
     * @param QualifiedName $name
     *
     * @return int|null
     */
    public function resolveAlias(QualifiedName $name);
}
