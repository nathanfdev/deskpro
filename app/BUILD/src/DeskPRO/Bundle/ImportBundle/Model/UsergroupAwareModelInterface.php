<?php

namespace DeskPRO\Bundle\ImportBundle\Model;

/**
 * Interface UserGroupAwareModelInterface.
 */
interface UsergroupAwareModelInterface
{
    /**
     * Returns a collection of article category user groups.
     *
     * @return array
     */
    public function getUserGroups();
}
