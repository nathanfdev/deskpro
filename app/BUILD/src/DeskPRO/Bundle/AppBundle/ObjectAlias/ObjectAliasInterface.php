<?php

namespace DeskPRO\Bundle\AppBundle\ObjectAlias;

interface ObjectAliasInterface
{
    /**
     * Returns the type of object referenced by this alias.
     *
     * @return string
     */
    public function getObjectType();

    /**
     * Returns the id of the object referenced by this alias.
     *
     * @return string
     */
    public function getObjectId();

    /**
     * Returns the un-qualified alias.
     *
     * @return string
     */
    public function getQualifiedName();

    /**
     * Returns a list of qualifiers.
     *
     * A qualifier is an order list of strings that can be attached to the alias as a prefix, forming a qualified alias
     *
     * @return array[]
     */
    public function getQualifiers();
}
