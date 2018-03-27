<?php

namespace Application\DeskPRO\Groups;

use Application\DeskPRO\Entity\Usergroup;

abstract class GroupsRepos implements \Countable, \IteratorAggregate
{
    /**
     * @var Usergroup[]
     */
    private $groups;

    /**
     * Groups mapped by sysname.
     *
     * @var Usergroup[]
     */
    private $groups_named;

    /**
     * @var int
     */
    private $count;

    /**
     * @param array $groups
     */
    public function __construct(array $groups)
    {
        $this->groups       = [];
        $this->groups_named = [];

        foreach ($groups as $g) {
            $this->groups[$g->id] = $g;

            if ($g->sys_name) {
                $this->groups_named[$g->sys_name] = $g;
            }
        }
    }

    /**
     * @param int $id
     *
     * @return bool
     */
    public function groupExists($id)
    {
        return isset($this->groups[$id]);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function sysGroupExists($name)
    {
        return isset($this->groups_named[$name]);
    }

    /**
     * @param int $id
     *
     * @return Usergroup
     */
    public function getGroup($id)
    {
        if (!isset($this->groups[$id])) {
            throw new \InvalidArgumentException();
        }

        return $this->groups[$id];
    }

    /**
     * @param string $name
     * @param bool   $throwException
     *
     * @return Usergroup
     */
    public function getSysGroup($name, $throwException = true)
    {
        if (!isset($this->groups_named[$name])) {
            if ($throwException) {
                throw new \InvalidArgumentException("Group '$name' doesn't exist");
            }

            return;
        }

        return $this->groups_named[$name];
    }

    /**
     * @return Usergroup[]
     */
    public function getAll()
    {
        return $this->groups;
    }

    /**
     * @return Usergroup[]
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
