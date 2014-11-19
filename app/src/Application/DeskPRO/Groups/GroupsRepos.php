<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace Application\DeskPRO\Groups;

abstract class GroupsRepos implements \Countable, \IteratorAggregate
{
    /**
     * @var \Application\DeskPRO\Entity\Usergroup[]
     */
    private $groups;

    /**
     * Groups mapped by sysname
     * @var \Application\DeskPRO\Entity\Usergroup[]
     */
    private $groups_named;

    /**
     * @var int
     */
    private $count;

    /**
     * @param array $groups */
    public function __construct(array $groups)
    {
        $this->groups = array();
        $this->groups_named = array();

        foreach ($groups as $g) {
            $this->groups[$g->id] = $g;

            if ($g->sys_name) {
                $this->groups_named[$g->sys_name] = $g;
            }
        }
    }

    /**
     * @param  int  $id
     * @return bool
     */
    public function groupExists($id)
    {
        return isset($this->groups[$id]);
    }

    /**
     * @param  string $name
     * @return bool
     */
    public function sysGroupExists($name)
    {
        return isset($this->groups_named[$name]);
    }

    /**
     * @param  int                                   $id
     * @return \Application\DeskPRO\Entity\Usergroup
     */
    public function getGroup($id)
    {
        if (!isset($this->groups[$id])) {
            throw new \InvalidArgumentException();
        }

        return $this->groups[$id];
    }

    /**
     * @param  string                                $name
     * @return \Application\DeskPRO\Entity\Usergroup
     */
    public function getSysGroup($name)
    {
        if (!isset($this->groups_named[$name])) {
            throw new \InvalidArgumentException();
        }

        return $this->groups_named[$name];
    }

    /**
     * @return \Application\DeskPRO\Entity\Usergroup[]
     */
    public function getAll()
    {
        return $this->groups;
    }

    /**
     * @return \Application\DeskPRO\Entity\Usergroup[]
     */
    public function getAllSys()
    {
        return $this->groups_named;
    }

    /**
     * @return int
     */
    public function count()
    {
        if ($this->count === null) {
            $this->count = count($this->groups);
        }

        return $this->count;
    }

    /**
     * @return \ArrayIterator|\Traversable
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->groups);
    }
}
