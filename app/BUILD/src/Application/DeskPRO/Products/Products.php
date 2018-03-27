<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Products;

use Application\DeskPRO\Hierarchy\LazyPreloadedHierarchy;
use Orb\Util\Arrays;

class Products extends LazyPreloadedHierarchy
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
        $recs = $this->em->getRepository('DeskPRO:Product')->getAll();
        $recs = Arrays::keyFromData($recs, 'id');

        return $recs;
    }

    public function setDefaultProductPreference($obj_or_id)
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
     * @return \Application\DeskPRO\Entity\Product
     */
    public function getDefaultProduct()
    {
        if ($this->default_id && (!$this->getById($this->default_id) || $this->hasChildren($this->default_id))) {
            foreach ($this->getAll() as $dep) {
                if (!$this->hasChildren($dep)) {
                    $this->default_id = $dep->getId();
                    break;
                }
            }
        }

        if (!$this->default_id) {
            return;
        }

        return $this->getById($this->default_id);
    }

    /**
     * Returns a settable object. That is an entity that is not a parent.
     * Returns null if the passed $id is invalid or is not a valid settable.
     *
     * @param int $id
     *
     * @return \Application\DeskPRO\Entity\Product|null
     */
    public function getSettableById($id)
    {
        $obj = $this->getById($id);
        if (!$obj || $this->getChildren($obj)) {
            return;
        }

        return $obj;
    }

    //###################################################################################################################
    // implementing these just for better auto-complete in the IDE (due to @return) :-)

    /**
     * @param int $id
     *
     * @return \Application\DeskPRO\Entity\Product
     */
    public function getById($id)
    {
        return parent::getById($id);
    }

    /**
     * @param array $ids
     *
     * @return \Application\DeskPRO\Entity\Product[]
     */
    public function getByIds(array $ids)
    {
        return parent::getByIds($ids);
    }

    /**
     * @param $obj_or_id
     *
     * @return \Application\DeskPRO\Entity\Product[]
     */
    public function getParent($obj_or_id)
    {
        return parent::getParent($obj_or_id);
    }

    /**
     * @return \Application\DeskPRO\Entity\Product[]
     */
    public function getParentPath($obj_or_id, $keyed = false)
    {
        return parent::getParentPath($obj_or_id, $keyed);
    }

    /**
     * @return \Application\DeskPRO\Entity\Product[]
     */
    public function getChildren($obj_or_id)
    {
        return parent::getChildren($obj_or_id);
    }

    /**
     * @return \Application\DeskPRO\Entity\Product[]
     */
    public function getRoots()
    {
        return parent::getRoots();
    }

    /**
     * @return \Application\DeskPRO\Entity\Product[]
     */
    public function getAll()
    {
        return parent::getAll();
    }
}
