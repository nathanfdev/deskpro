<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Tickets;

use Application\DeskPRO\Collection\LazyCollection;
use Orb\Util\Arrays;

class TicketWorkflows extends LazyCollection
{
    /**
     * @var int
     */
    private $default_id;

    /**
     * @return array
     */
    protected function loadRecords()
    {
        $recs = $this->em->getRepository('DeskPRO:TicketWorkflow')->getAll();
        $recs = Arrays::keyFromData($recs, 'id');

        return $recs;
    }

    /**
     * This sets the 'default' preference.
     * Note that setting an invalid or bogus ID here will not result in an exception.
     *
     * With an invalid pref, getDefaultWorkflow() will return the first selectable option.
     * Use getById() to check if a exists before calling this.
     *
     * @param int $obj_or_id
     */
    public function setDefaultWorkflowPreference($obj_or_id)
    {
        if ($obj_or_id === null) {
            $this->default_id = null;
        } elseif (is_object($obj_or_id)) {
            $this->default_id = $obj_or_id->id;
        } else {
            $this->default_id = intval($obj_or_id);
        }
    }

    /**
     * @return \Application\DeskPRO\Entity\TicketPriority
     */
    public function getDefaultWorkflow()
    {
        if ($this->default_id && !$this->getById($this->default_id)) {
            foreach ($this->getAll() as $dep) {
                $this->default_id = $dep->getId();
                break;
            }
        }

        if (!$this->default_id) {
            return;
        }

        return $this->getById($this->default_id);
    }

    //###################################################################################################################
    // implementing these just for better auto-complete in the IDE (due to @return) :-)

    /**
     * @param int $id
     *
     * @return \Application\DeskPRO\Entity\TicketPriority[]
     */
    public function getById($id)
    {
        return parent::getById($id);
    }

    /**
     * @param array $ids
     * @param bool  $keyed
     *
     * @return \Application\DeskPRO\Entity\TicketPriority[]
     */
    public function getByIds(array $ids, $keyed = false)
    {
        return parent::getByIds($ids, $keyed);
    }

    /**
     * @return \Application\DeskPRO\Entity\TicketPriority[]
     */
    public function getAll()
    {
        return parent::getAll();
    }

    public function getFlatArray()
    {
        $flat = [];

        foreach ($this->getAll() as $obj) {
            $flat[] = ['object' => $obj, 'depth' => 0];
        }

        return $flat;
    }
}
