<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\ORM\StateChange;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\PersistentCollection;
use Orb\Util\Arrays;

class ChangeCollection implements ChangeInterface
{
    /**
     * @var string
     */
    private $field_id;

    /**
     * @var array
     */
    private $old;

    /**
     * @var array
     */
    private $new;

    /**
     * @var bool
     */
    private $is_same = false;

    /**
     * @var array
     */
    private $add_elements = [];

    /**
     * @var array
     */
    private $del_elements = [];

    /**
     * @param string     $field_id
     * @param Collection $coll
     *
     * @return ChangeCollection
     */
    public static function newFromPersistedCollection($field_id, Collection $coll, $old = [])
    {
        if ($coll instanceof PersistentCollection) {
            $new = $coll->toArray();
        } else {
            $new = $coll->toArray();
        }

        return new self($field_id, $old, $new);
    }

    /**
     * @param string $field_id
     * @param mixed  $old
     * @param mixed  $new
     */
    public function __construct($field_id, array $old = null, array $new = null)
    {
        $this->field_id = $field_id;
        $this->old      = $old;
        $this->new      = $new;

        // Check for null
        if ($old === $new) {
            $this->is_same = true;
        } else {
            if ($this->old && $this->new) {
                $this->del_elements = Arrays::arrayDiffAssocIdentity($this->old, $this->new);
                $this->add_elements = Arrays::arrayDiffAssocIdentity($this->new, $this->old);
            } elseif ($this->old && !$this->new) {
                $this->del_elements = $this->old;
            } elseif ($this->new && !$this->old) {
                $this->add_elements = $this->new;
            }

            if (!$this->del_elements && !$this->add_elements) {
                $this->is_same = true;
            }
        }
    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->field_id;
    }

    /**
     * @return array
     */
    public function getOld()
    {
        return $this->old;
    }

    /**
     * @return array
     */
    public function getNew()
    {
        return $this->new;
    }

    /**
     * @return bool
     */
    public function isSame()
    {
        return $this->is_same;
    }

    /**
     * @return bool
     */
    public function isCollection()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isEntity()
    {
        return false;
    }

    /**
     * @return array
     */
    public function getAddedElements()
    {
        return $this->add_elements;
    }

    /**
     * @return array
     */
    public function getRemovedElements()
    {
        return $this->del_elements;
    }

    public function setAddedElements(array $added)
    {
        $this->add_elements = $added;
    }

    public function setRemovedElements(array $removed)
    {
        $this->del_elements = $removed;
    }
}
