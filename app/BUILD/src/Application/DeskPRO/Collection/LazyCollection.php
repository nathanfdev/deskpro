<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Collection;

use Doctrine\ORM\EntityManager;

abstract class LazyCollection
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var array
     */
    private $records;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Loads records.
     *
     * @return array
     */
    abstract protected function loadRecords();

    /**
     * Loads department data from the database.
     */
    private function preload()
    {
        if ($this->records !== null) {
            return;
        }

        $this->records = $this->loadRecords();
    }

    /**
     * @return \Application\DeskPRO\Hierarchy\PreloadedHierarchy
     */
    public function getRecords()
    {
        $this->preload();

        return $this->records;
    }

    /**
     * Resets this repository so the next time data is requested form it, it will
     * be queried again.
     */
    public function reset()
    {
        $this->records = null;
    }

    //###################################################################################################################

    /**
     * Count number of records.
     *
     * @return int
     */
    public function count()
    {
        $this->preload();

        return count($this->records);
    }

    /**
     * @param int $id
     *
     * @return mixed Returns null when not found
     */
    public function getById($id)
    {
        $this->preload();

        return isset($this->records[$id]) ? $this->records[$id] : null;
    }

    /**
     * @param array $ids
     *
     * @return array
     */
    public function getByIds(array $ids, $keyed = false)
    {
        $this->preload();
        $ret = [];
        foreach ($ids as $id) {
            if (isset($this->records[$id])) {
                if ($keyed) {
                    $ret[$id] = $this->records[$id];
                } else {
                    $ret[] = $this->records[$id];
                }
            }
        }

        return $ret;
    }

    /**
     * @return array
     */
    public function getAllIds()
    {
        return array_keys($this->records);
    }

    /**
     * @return array
     */
    public function getAll()
    {
        $this->preload();

        return array_values($this->records);
    }
}
