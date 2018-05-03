<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\People;

use Application\DeskPRO\Groups\GroupsRepos;

class UserGroups extends GroupsRepos
{
    /**
     * @param bool $throwException
     *
     * @return \Application\DeskPRO\Entity\Usergroup
     */
    public function getEveryoneGroup($throwException = true)
    {
        return $this->getSysGroup('everyone', $throwException);
    }

    /**
     * @param bool $throwException
     *
     * @return \Application\DeskPRO\Entity\Usergroup
     */
    public function getRegisteredGroup($throwException = true)
    {
        return $this->getSysGroup('registered', $throwException);
    }
}
