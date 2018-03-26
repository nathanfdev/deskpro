<?php

namespace DeskPRO\Bundle\ImportBundle\Model;

/**
 * Interface OidAwareModelInterface.
 */
interface OidAwareModelInterface
{
    /**
     * Get entity oid.
     *
     * @return int|string
     */
    public function getOid();
}
