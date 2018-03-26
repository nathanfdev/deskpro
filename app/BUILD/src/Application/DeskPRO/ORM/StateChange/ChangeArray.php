<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\ORM\StateChange;

use Orb\Util\Arrays;

class ChangeArray implements ChangeInterface
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
        } elseif ($old && $new && count($this->old) == count($this->new)) {
            if (Arrays::arrayDiffAssocRecursive($this->old, $this->new) || Arrays::arrayDiffAssocRecursive($this->new, $this->old)) {
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
        return false;
    }

    /**
     * @return bool
     */
    public function isEntity()
    {
        return false;
    }
}
