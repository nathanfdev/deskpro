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

namespace Application\DeskPRO\Collection;

use Doctrine\ORM\EntityManager;

abstract class LazyCollection
{
    /**
     * @var \Application\DeskPRO\ORM\EntityManager
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
     * Loads records
     *
     * @return array
     */
    abstract protected function loadRecords();

    /**
     * Loads department data from the database
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

    ####################################################################################################################

    /**
     * Count number of records
     *
     * @return int
     */
    public function count()
    {
        $this->preload();

        return count($this->records);
    }

    /**
     * @param  int   $id
     * @return mixed Returns null when not found
     */
    public function getById($id)
    {
        $this->preload();

        return isset($this->records[$id]) ? $this->records[$id] : null;
    }

    /**
     * @param  array $ids
     * @return array
     */
    public function getByIds(array $ids, $keyed = false)
    {
        $this->preload();
        $ret = array();
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
